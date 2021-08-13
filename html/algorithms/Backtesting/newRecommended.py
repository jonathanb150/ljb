#!/usr/bin/python3.5
#FILE NAME: newRecommended.py
# Analyzes stocks in short term

## FUNDAMENTAL ANALYSIS ALGORITHM + TECHNICAL
## CALCULATES STOCK GROWTH, FINANCIAL STABILITY, REAL PRICE, AND FUTURE VALUE AND TECHNICALS
## USE ONLY FOR STOCKS
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from financialObject import financialObject
from stockObject import stockObject
import scipy
import json
import sys
import numpy as np
import math
import datetime


def main():
    company = sys.argv[1]
    stock_f = financialObject()
    stock = stockObject()
        #inputs
    stock_f.name = company + '_f' # str company symbol
    stock.stock_name = company + '_1d'# str company symbol
    stock.final_date = todaysDate()# final date Yr-mm-dd
    real_init_date = str(int(stock.final_date[:4])-1)+stock.final_date[4:]  # init date Yr-mm-dd
    stock.init_date = str(int(stock.final_date[:4])-2)+stock.final_date[4:] 
    
    stock_f.initdate = str(int(stock.final_date[:4])-5)+stock.final_date[4:]  # init date Yr-mm-dd
    stock_f.finaldate = todaysDate() # final date Yr-mm-dd
    
    stock.getData_df()
    currentPrice = stock.all_data['closing_price'][-1]
    
## GETTING ALL FINANCIAL INFO AND STORING AS ATTRIBUTES
    stock_f.getFinancials('Revenue','Profit','Eps','Cash','Equity','Debt','MarketCap')
#header = ['revenue','profit','eps','cash','equity','debt'] --> financials positions
    if len(stock_f.revenue)<16 or len(stock_f.profit)<16 or len(stock_f.cash)<16 or len(stock_f.equity)<16:
        sys.exit('No Financial Info')
    financials_yearly = financialsYearlyClean(stock_f) ## financials --> TTM
    financials_quarterly = [stock_f.revenue.copy(), stock_f.profit.copy(), stock_f.eps.copy(), stock_f.cash.copy(), stock_f.equity.copy(), stock_f.debt.copy()]
# Yearly and Quarterly Changes
    financials_qoq,dates_qoq = getFinancialsChange(financials_quarterly,period=1)
    financials_qonq,dates_qonq = getFinancialsChange(financials_quarterly,period=4)
    financials_yoy,dates_yoy = getFinancialsChange(financials_yearly,period=4)
 
    if len(financials_yoy[0])<9: # if less than 3 years of data available
        sys.exit('No Financial Info')
    avg_change3Y = currentChanges(financials_yoy,0,4,1)
    avg_change1Y = [financials_yoy[0][0], financials_yoy[0][1], financials_yoy[0][2], financials_yoy[0][3], financials_yoy[0][4]]
    avg_change2Q = currentChanges(financials_qoq,0,1,2)

# Analyzing financials and getting true and expected price    
    dic= {}
    if avg_change3Y[0]<30 and avg_change3Y[1]<30 and avg_change3Y[4]<30:
        mature = True
    else:
        mature = False
    pe_value = -18 # negative, so we use current PRice
    stock_f.analyzeCompany(3,pe_value,avg_change3Y,mature)
    temp_trueval = stock_f.true_value
    temp_expectedval = stock_f.expected_value
    stock_f.analyzeCompany(3,pe_value,avg_change1Y,mature)
        
    trueValue = stock_f.true_value*0.3 + temp_trueval*0.7
    dic['truePrice'] = trueValue
    dic['expectedPrice'] = stock_f.expected_value*0.3 + temp_expectedval*0.7

    dic['target_mediumTerm'] = (stock_f.true_value*0.3 + temp_trueval*0.7)*1.2
    dic['target_longTerm'] = (stock_f.true_value*0.3 + temp_trueval*0.7)*1.15*1.15*1.15
   
    trueShortTermPrice = stock_f.true_value

    points = stock_f.riskRewardRatio()
    dic['riskRewardRatio'] = points

## TECHNICAL INFO SHORT TERM
    dic['currentPrice'] = currentPrice 
    stock.period1 = 200
    stock.period2 = 100
    stock.period3 = 50

    #stock.init_date = real_init_date
    stock.init_date = real_init_date
    stock.requested_data = stock.all_data[255:] # 255 is one year
    stock.SMAsCrossover() ## just to get the SMAs
    currentSMA200 = stock.sma1[-1]
    currentSMA100 = stock.sma2[-1]

    maxLastMonth = (stock.requested_data['closing_price'][-23]).max()
    maxLast2Month = (stock.requested_data['closing_price'][-46]).max()
    avgLast2weeks = scipy.average(stock.requested_data['closing_price'][-10:])
    avgLastWeek = scipy.average(stock.requested_data['closing_price'][-5:])
    avgLast3days = scipy.average(stock.requested_data['closing_price'][-3:])
    avgOneWeekAgo = scipy.average(stock.requested_data['closing_price'][-10:-5])
    median2weeks = scipy.average(stock.requested_data['closing_price'][-10:])
    medianLastWeek = scipy.average(stock.requested_data['closing_price'][-5:])
    minPrice = (stock.requested_data['closing_price']).min()

    boughtPrice = 0
    dic['boughtPrice'] = 0

# if true value is greater than current and smas line up and price crosses sma and the stock didnt go up like crazy in the past year
    if (boughtPrice ==0 and currentPrice<=trueValue and currentSMA100<=1.02*currentSMA200 and currentSMA200>stock.requested_data['closing_price'][-2] and currentSMA200<currentPrice and (minPrice*2>currentPrice or currentPrice<trueShortTermPrice*3)):
    	boughtPrice = currentPrice
    	dic['boughtPrice'] = boughtPrice
    	dic['boughtPriceDescription'] = 1 ##'realBuyingPoint'
    elif (boughtPrice ==0 and currentPrice<=trueValue and currentSMA200>stock.requested_data['closing_price'][-2] and currentSMA200<currentPrice and (minPrice*2>currentPrice or currentPrice<trueShortTermPrice*3)):
    	boughtPrice = currentPrice
    	dic['boughtPrice'] = boughtPrice
    	dic['boughtPriceDescription'] = 2 ##'secondBuyingPoint only if we sold with selling 2'        

     
## Making Json
    header = ['Change (YoY)','Revenue','Profit','EPS','Cash','Equity','Debt']
    info = [header,
            ['1 Quarter',financials_qoq[0][0],financials_qoq[1][0],financials_qoq[2][0],financials_qoq[3][0],financials_qoq[4][0],financials_qoq[5][0]],
            ['2 Quarters',avg_change2Q[0],avg_change2Q[1],avg_change2Q[2],avg_change2Q[3],avg_change2Q[4],avg_change2Q[5]],
            ['1 Year',financials_yoy[0][0],financials_yoy[1][0],financials_yoy[2][0],financials_yoy[3][0],financials_yoy[4][0],financials_yoy[5][0]],
            ['3 Years',avg_change3Y[0],avg_change3Y[1],avg_change3Y[2],avg_change3Y[3],avg_change3Y[4],avg_change3Y[5]]]

    dic['financial_table'] = info
    info_json = json.dumps(dic)
    print(info_json)
#---------------------------------------OTHER FUNCTIONS-----------------------------------------
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
    for i in financials:
        temp_change = []
        dates = []
        length = len(i)-period
        for w in range(length):
            if i.iloc[w+period] == 0:
                if i.iloc[w]>0:
                    change = -15#I assigned this percentage, but might need to change it 
                else:
                    change = 15#I assigned this percentage, but might need to change it 
            else:
                change = ((i.iloc[w]-i.iloc[w+period])/i.iloc[w+period])*100
            temp_change.append(change)
            dates.append(i.index.values[w])
        financials_change.append(temp_change)
        final_dates.append(dates)

    return financials_change,final_dates

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
        for i in changes:
            temp_changes = [i[index],i[distance+index],i[2*distance+index]]
            change.append(temp_changes)
        avg_change = avgChangeOverTimeConstrained(change,period)

        return avg_change

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

if __name__ =='__main__':
    main()
    