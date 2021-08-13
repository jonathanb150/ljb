#!/usr/bin/env python
#FILE NAME: historicalData.py
import sys
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from financialObject import financialObject
from stockObject import stockObject
import scipy
import numpy as np
import pandas as pd
import json
import datetime
import math



def main():
    stock_f = financialObject()
    
    #inputs
    name = sys.argv[1] # str company symbol
    stock_f.initdate = '1980-01-01' # str yr-mm-dd
    stock_f.finaldate = sys.argv[2]# str yr-mm-dd


    stock_f.name = name + '_f'   
    
## GETTING ALL FINANCIAL INFO AND STORING AS ATTRIBUTES
    stock_f.getFinancials('Revenue','Profit','Eps','Cash','Equity','Debt','MarketCapQuarterly','DividendYieldQuarterly')
#header = ['revenue','profit','eps','cash','equity','debt'] --> financials positions
    financials_yearly = financialsYearlyClean(stock_f) ## financials --> TTM

    financials_quarterly = [stock_f.revenue.copy(), stock_f.profit.copy(), stock_f.eps.copy(), stock_f.cash.copy(), stock_f.equity.copy(), stock_f.debt.copy(), stock_f.marketcapquarterly.copy(),stock_f.dividendyieldquarterly.copy()]
# Yearly and Quarterly Changes    
    financials_qoq,dates_qoq = getFinancialsChange(financials_quarterly,period=1)
    financials_qonq,dates_qonq = getFinancialsChange(financials_quarterly,period=4)
    financials_yoy,dates_yoy = getFinancialsChange(financials_yearly,period=4)


# True value will be calculated on platform --> expectedgrowth^3*(Selected PE* EPS)/1.15^3
# Historical financials
    table_header = ['Date','Revenue','Profit','EPS','Cash','Equity','Debt','MarketCap','Dividend Yield (%)']
    table1 = makeTableFromArrayOfDF(financials_quarterly,1)
    table1.insert(0,table_header)
    table2 = makeTableFromArrayOfDF(financials_yearly,4)
    table2.insert(0,table_header)

# Historical True Values
    new_dates,current_prices,expected_growth,true_values,price_stats = historicalValues(stock_f,dates_yoy,financials_yoy)
    table3 = makeTable(new_dates,current_prices,expected_growth,true_values,price_stats)
    table_header2 = ['Dates','Current Price ($)','Expected Growth (%)', 'EPS', 'PE Ratio', 'True Value ($)','Possible Buying Range ($)','Max 1 Year ($)','Max 2 years ($)']
    table3.insert(0,table_header2)

#JSON
    dic = {}
    dic['table1'] = table1
    dic['table2'] = table2
    dic['table3'] = table3
    info_json = json.dumps(dic)
#making excel FILE
    pd.concat(financials_yearly[:6],axis=1).to_excel('/var/www/ljb.solutions/html/graphs/DCF_data.xls',sheet_name='econData')
# Printing tables as json
    print(info_json)
#---------------------------------------OTHER FUNCTIONS-----------------------------------------
def makeTableFromArrayOfDF(info,step):
    length = 99999
    for i in info:
        temp = len(i)
        if temp<length:
            length = temp
    if step!=1:
        length = int(math.floor(length/step))
    #header = info.columns
    table = []
    count = 0
    for i in range(length):
        row = []
        if step != 0:
            row.append(str(info[0].index[i*step]))
        else:
            row.append(str(info[0].index[i+count]))
        for x in range(len(info)):
            add = info[x].values[i*step]
            if abs(add)>1000000000:
                add = str(round(add/1000000000,2))+'B'
            elif abs(add)>100000:
                add = str(round(add/1000000,2))+'M'
            else:
                add = str(round(add,2))

            row.append(add)
        count += step
        table.append(row)

    return table


def todaysDate():
    date = [datetime.datetime.today().year,datetime.datetime.today().month,
            datetime.datetime.today().day]
    counter =0
    init_date = ''
    for i in date:
        if len(str(i))<2:
            temp = '0'+ str(i)
        else:
            temp = str(date[counter])
        if counter !=0:
            init_date = init_date + '-' + temp
        else:
            init_date = init_date + temp
        counter +=1
    return init_date
    
def financialsYearlyClean(stock_f):
    rev = stock_f.revenue[:].copy()
    prof = stock_f.profit[:].copy()
    eps  = stock_f.eps[:].copy()
    values = []
    # finding min length
    if len(eps)==len(prof) and len(eps)==len(rev):
        length = len(eps)
    elif len(eps)<len(rev) and len(eps)<len(prof):
        length = len(eps)
    elif len(prof)<len(eps) and len(prof)<len(rev):
        length = len(prof)
    else:
        length = len(rev)

    for i in [rev,prof,eps]:
        period = 4
        for counter in range(length-period):
            i[counter]= np.sum(i[counter:counter+period])
        i = i[:counter+1]
        values.append(i)
    length = int(len(i))
    values.append(stock_f.cash.iloc[:length].copy())
    values.append(stock_f.equity.iloc[:length].copy())
    values.append(stock_f.debt.iloc[:length].copy())
    values.append(stock_f.marketcapquarterly.iloc[:length].copy())
    values.append(stock_f.dividendyieldquarterly.iloc[:length].copy())

    return values

def changeOverTime(values_list, period):
    final_changes = []
    for i in values_list:
        temp = []
        for counter in range(len(i)-period):
            change = (i[counter]-i[counter+period])/i[counter+period]*100
            temp.append(change)
        dates = (i.index.values)[:counter+1]
        counter = 0
        final_changes.append(temp)

    return final_changes,dates

def currentChanges(changes,index=0,distance=4,period=1):
        change = []
        counter = 99999
        for i in changes:
            try:
                temp_changes = [i[index],i[distance+index],i[2*distance+index]]
                change.append(temp_changes)
            except:
                sys.exit('error')

        avg_change = avgChangeOverTimeConstrained(change,period)

        return avg_change

def historicalValues(stock_f,dates,changes): 
    true_values = []
    expected_growth = [] 
    finaldate = stock_f.finaldate
    new_dates = []
    price_stats = []
    current_prices = []
    for i in dates[0][:len(changes[0])-8]:
        new_dates.append(i.isoformat())
    for counter in range(len(changes[0])-8):
        stock_f.finaldate = new_dates[counter]
        #YEARLY CHANGES
        avg_change_3Y = currentChanges(changes,counter,4,1)
        #QUARTERLY CHANGES--> nothing right now
        if avg_change_3Y[0]>30 or avg_change_3Y[1]>30 or avg_change_3Y[2]>30 or avg_change_3Y[4]>30:
            mature = False
        else:
            mature = True
        stock_f.predictedStockChange(3,avg_change_3Y,mature)

        stock_f.currentPrice() # assigns price to self.price
        current_prices.append(round(float(stock_f.price),2))
        if counter<=(len(new_dates)-1):
            stats = stock_f.priceStatistics(new_dates[counter])
        else:
            temp = datetime.datetime.strptime(new_dates[counter], '%Y-%m-%d')
            temp = (temp - datetime.timedelta(days=90)).strftime("%Y-%m-%d")#previous quarter
            # HEADER ['min','max','max_1y','max_2y']   
            stats = stock_f.priceStatistics(new_dates[counter]) # DONE--> ADD TO TABLES
        #Getting corresponding EPS
        price_stats.append(stats)
        stock_f.getFinancials('Eps')
        true_values.append(round(sum(stock_f.eps[0:4]),2))
        expected_growth.append(float(round(stock_f.expectedgrowth*100,2)))
    #print(true_values)
    stock_f.finaldate = finaldate

    return new_dates,current_prices,expected_growth,true_values,price_stats

#avg change over time
def avgChangeOverTimeConstrained(values_list, period): 
    final_changes = []
    for i in values_list:
        temp = (np.asarray(i[:])/100) +1
        new_list = []
        if period !=1:
            for w in range(math.floor(len(temp)/period)):  
                new_list.append(np.prod(temp[:period]))
                temp = temp[period:]
            temp = new_list
        change = 1
        for x in temp:
            if x>0 and x<2.5:
                change = change*x
            elif x<0: # 90% drop is cap
                change = change*0.1
            else:
                change = change*1.9 # 1.9 is cap of growth

        final_changes.append(((change**(1/len(i))-1)*100))

    return final_changes

def getFinancialsChange(financials,period):    
    financials_change = []
    final_dates = []
    counter = 999999
    # First loop to make changes all the same length
    for i in financials:
        if len(i)<counter:
            counter = len(i)
    length = counter-period
    # second loop to calculate changes
    for i in financials:
        temp_change = []
        dates = []
        for w in range(length):
            if i.iloc[w+period] <0 and i.iloc[w]>0 :
                change = 30
            elif i.iloc[w+period] <0 and i.iloc[w+period]>i.iloc[w]:
                change = ((abs(i.iloc[w]))-abs(i.iloc[w+period]))/abs(i.iloc[w+period])*100
            elif i.iloc[w+period] <0 and i.iloc[w+period]<i.iloc[w] and i.iloc[w]<0:
                change = ((abs(i.iloc[w+period]))-abs(i.iloc[w]))/abs(i.iloc[w])*100
            elif i.iloc[w] == 0 or i.iloc[w+period] ==0:
                change = 0
            else:
                change = ((i.iloc[w]-i.iloc[w+period])/i.iloc[w+period])*100
            temp_change.append(change)
            dates.append(i.index.values[w])
        financials_change.append(temp_change)
        final_dates.append(dates)

    return financials_change,final_dates

def makeTable(dates,current_prices,expected_growth,true_values,price_stats):
    table = [[]]
    c = 0
    for i in dates:
        price1 = price_stats[c][0]
        price2 = price_stats[c][1]
        if isinstance(price1,float):
            price1 = str(round(price1,3))
        if isinstance(price2,float):
            price2 = str(round(price2,3))

        temp = [i,current_prices[c],expected_growth[c],true_values[c],0, 0,price1+' - '+ price2,price_stats[c][2],price_stats[c][3]]
        table.append(temp)
        c+=1
    return table[:]



if __name__ =='__main__':
    main()