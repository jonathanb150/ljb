#!/usr/bin/env python
#FILE NAME: fundamentalAnalysisStocks.py
import sys
sys.path.append("/home/ubuntu/www/algorithms/lib")
from financialObject import financialObject
from stockObject import stockObject
import scipy
import numpy as np
import plotly.plotly as py
import plotly
import plotly.graph_objs as go
import json
import datetime
import math



def main():
    stock_f = financialObject()
    
    #inputs
    name = sys.argv[1] # str company symbol
    pe1 = float(sys.argv[2]) # number
    pe2 = float(sys.argv[3]) # number
    assetHeavy = bool(sys.argv[4]) # empty str --> No str-True --> Yes
    stock_f.initdate = sys.argv[5] # str yr-mm-dd
    stock_f.finaldate = sys.argv[6]# str yr-mm-dd


    stock_f.name = name + '_f'   
    
## GETTING ALL FINANCIAL INFO AND STORING AS ATTRIBUTES
    stock_f.getFinancials('Revenue','Profit','Eps','Cash','Equity','Debt','MarketCap')
#header = ['revenue','profit','eps','cash','equity','debt'] --> financials positions
    financials_yearly = financialsYearlyClean(stock_f) ## financials --> TTM
    financials_quarterly = [stock_f.revenue.copy(), stock_f.profit.copy(), stock_f.eps.copy(), stock_f.cash.copy(), stock_f.equity.copy(), stock_f.debt.copy()]
# Yearly and Quarterly Changes    
    financials_qoq,dates_qoq = getFinancialsChange(financials_quarterly,period=1)
    financials_qonq,dates_qonq = getFinancialsChange(financials_quarterly,period=4)
    financials_yoy,dates_yoy = getFinancialsChange(financials_yearly,period=4) 

    avg_change3Y = currentChanges(financials_yoy,0,4,1)
    avg_change2Q = currentChanges(financials_qoq,0,1,2)
    avg_change1Y = [financials_yoy[0][0], financials_yoy[0][1], financials_yoy[0][2], financials_yoy[0][3], financials_yoy[0][4]]
    dates,expected_values,true_values,price_stats,current_prices = historicalValues(stock_f,dates_yoy,financials_yoy)

    
#Current true and Expected value with PEs selected 
## period of 3 Years
    dic= {}
    pe = [pe1]
    if pe2 !=0 or pe2 != 0.0:
        pe = [pe1,pe2]
    for pe_value in pe:
        ## greater than 30%  == growth company
        if avg_change3Y[0]>30 or avg_change3Y[1]>30 or avg_change3Y[2]>30 or avg_change3Y[4]>30:
            mature = False
        else:
            mature = True
        # price with 3Y financials
        stock_f.analyzeCompany(3,pe_value,avg_change3Y,mature)
        temp_trueval = stock_f.true_value
        temp_expectedval = stock_f.expected_value
        #price with 1Y financials
        stock_f.analyzeCompany(3,pe_value,avg_change1Y,mature)
        if pe_value==pe1:
            trueprice1 =stock_f.true_value*0.3 + temp_trueval*0.7
            expectedprice1 = stock_f.expected_value*0.3 + temp_expectedval*0.7

            trueprice2 = stock_f.true_value*0.3 + temp_trueval*0.7
            expectedprice2 = stock_f.expected_value*0.3 + temp_expectedval*0.7
        else:
            trueprice2 = stock_f.true_value*0.3 + temp_trueval*0.7
            expectedprice2 = stock_f.expected_value*0.3 + temp_expectedval*0.7

#Calculates how risky the company is based on current financials. Output is a number out of 10
    points = stock_f.riskRewardRatio()

## Getting more changes to plot and display info
    profit_margin = str(round(float(np.sum(stock_f.profit[0:4])/np.sum(stock_f.revenue[0:4]))*100,2))+' %'
    cash_flow = int(stock_f.cash[0])-int(stock_f.cash[4])
    current_cash = int(stock_f.cash[0])
    marketcap = int(stock_f.marketcap[0])

##Plotting
    makingPlots(stock_f)
    barPlots(financials_qonq,dates_qonq,['Revenue','Profit','EPS','Cash','Equity','Debt'])


## Making Json
    header = ['Change (YoY)','Revenue','Profit','EPS','Cash','Equity','Debt']
    info = [header,
            ['1 Quarter',financials_qoq[0][0],financials_qoq[1][0],financials_qoq[2][0],financials_qoq[3][0],financials_qoq[4][0],financials_qoq[5][0]],
            ['2 Quarters',avg_change2Q[0],avg_change2Q[1],avg_change2Q[2],avg_change2Q[3],avg_change2Q[4],avg_change2Q[5]],
            ['1 Year',financials_yoy[0][0],financials_yoy[1][0],financials_yoy[2][0],financials_yoy[3][0],financials_yoy[4][0],financials_yoy[5][0]],
            ['3 Years',avg_change3Y[0],avg_change3Y[1],avg_change3Y[2],avg_change3Y[3],avg_change3Y[4],avg_change3Y[5]]]

    dic=dict()
    dic['table1'] = info

    info_historical = makeTable(dates,true_values,expected_values,price_stats,current_prices)
    header2 = ['Dates','Current Price','True Value','Expected Value','Possible Buying Range','Max 1 Year','Max 2 years']
    info_historical[0] = header2
    dic['historical'] = info_historical

    header3 = ['True Price','Expected Price','Profit Margin','Safety','Cash Flow (1Y)','Current Cash','Market Cap']
    dic['tablepe1'] = [header3,[float(trueprice1),float(expectedprice1),profit_margin,float(points),cash_flow, current_cash, marketcap]]
    dic['portfolio'] = [expectedprice1]
    if pe2!=0 or pe2!=0.0:
        dic['tablepe2'] = [header3,[float(trueprice2),float(expectedprice2),profit_margin,float(points),cash_flow, current_cash, marketcap]]
    
    info_json = json.dumps(dic)
    print(info_json)
#---------------------------------------OTHER FUNCTIONS-----------------------------------------
    
def jsonOutputforPHP(dic,names,info):
    counter =0
    for i in names:
        dic[i] = info[counter]
        counter+=1
    
    return dic

def barPlots(info,dates,names):
    period = 8 # 8 quarters = 2 years
    data = []
    counter = 0
    for x in names:
        trace = go.Bar(
        x=dates[counter][:period],
        y=info[counter][:period],
        name = x )
        counter+=1
        data.append(trace)
    updatemenus=list([
        dict(
            buttons=list([
                dict(
                    args=[{'visible': [True,True,True,True,True,True]}],
                    label='All',
                    method='update',
                ),   
                dict(
                    args=[{'visible': [True,False,False,False,False,False]}],
                    label='Revenue',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,True,False,False,False,False]}],
                    label='Profit',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,False,True,False,False,False]}],
                    label='EPS',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,False,False,True,False,False]}],
                    label='Cash',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,False,False,False,True,False]}],
                    label='Equity',
                    method='update',),
                dict(
                    args=[{'visible': [False,False,False,False,False,True]}],
                    label='Debt',
                    method='update',)     

            ]),
            direction = 'down',
            pad = {'r': 10, 't': 10},
            showactive = True,
            x = 0.145,
            xanchor = 'left',
            y = 1.14,
            yanchor = 'top' 
        ),
    ])    
    
    fig = go.Figure(data=data)
    fig['layout']['title'] = 'Financials Change QoQ'
    fig['layout']['updatemenus'] = updatemenus
    fig['layout']['showlegend'] = True
    
    plotly.offline.plot(fig, filename='./graphs/plot1FundamentalsChange.html')

def makingPlots(stock_f):
## dates Data 
    rev = stock_f.revenue.index.values
    prof = stock_f.profit.index.values
    equi = stock_f.equity.index.values
    cash = stock_f.cash.index.values
    debt = stock_f.debt.index.values
    eps = stock_f.eps.index.values
## Plotting changes-------------------------------------------------------------
   
    trace1 = go.Scatter(x = rev , y = stock_f.revenue.values[:], name = 'Revenue')
    trace2 = go.Scatter(x = equi , y = stock_f.equity.values[:], name = 'Equity', visible = False)
    trace3 = go.Scatter(x=cash,y=stock_f.cash.values[:], name ='Cash', visible = False)
    trace4 = go.Scatter(x = eps , y = stock_f.eps.values[:], name = 'Eps', visible = False)
    trace5 = go.Scatter(x=prof,y=stock_f.profit.values[:], name ='Profit', visible = False)
    trace6 = go.Scatter(x=debt,y=stock_f.debt.values[:], name ='Debt', visible = False)

    data = [trace1,trace2,trace3,trace4,trace5,trace6]
    
    layout = dict(
        xaxis=dict(
            rangeselector=dict(
                buttons=list([
                    dict(count=30,
                         label='1m',
                         step='day',
                         stepmode='backward'),
                    dict(count=180,
                         label='6m',
                         step='day',
                         stepmode='backward'),
                    dict(count=365,
                         label='1y',
                         step='day',
                         stepmode='backward'),
                    dict(count=1095,
                         label='3y',
                         step='day',
                         stepmode='backward'),
                    dict(label='max',
                         step='all',
                         stepmode='backward'),

                    ]),
                         ),
            domain=[0, 1]
        ),
        yaxis=dict(
            domain=[0, 1],
            anchor = 'y'
        ))
                  
    updatemenus=list([
        dict(
            buttons=list([   
                dict(
                    args=[{'visible': [True,False,False,False,False,False]}],
                    label='Revenue',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,True,False,False,False,False]}],
                    label='Equity',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,False,True,False,False,False]}],
                    label='Cash',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,False,False,True,False,False]}],
                    label='Eps',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,False,False,False,True,False]}],
                    label='Profit',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,False,False,False,False,True]}],
                    label='Debt',
                    method='update',
                ),
                dict(
                    args=[{'visible': [True,True,True,True,True,True]}],
                    label='All',
                    method='update',
                )                

            ]),
            direction = 'down',
            pad = {'r': 10, 't': 10},
            showactive = True,
            x = 0.145,
            xanchor = 'left',
            y = 1.14,
            yanchor = 'top' 
        ),
    ])    

    annotations = list([dict(text='Select Financial:', x=0, y=1.1, yref='paper', align='left', showarrow=False)])
    fig = go.Figure(data=data, layout=layout)
   # fig = dict(layout=layout, data=data)
    fig['layout']['title'] = 'Financials Growth'
    fig['layout']['updatemenus'] = updatemenus
    layout['annotations'] = annotations
    fig['layout']['showlegend'] = True
    fig['layout'].update(height=800)
    plotly.offline.plot(fig, filename='./graphs/plot1Fundamentals.html')


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

def historicalValues(stock_f,dates,changes): 
    true_values = []
    expected_values = [] 
    finaldate = stock_f.finaldate
    new_dates = []
    price_stats = []
    current_prices = []
    for i in dates[0][:len(changes[0])-8]:
        new_dates.append(i.isoformat())
    for counter in range(len(changes[0])-8):
        pe_value = -25 # Negative value so current price is used
        stock_f.finaldate = new_dates[counter]
        #YEARLY CHANGES
        avg_change_3Y = currentChanges(changes,counter,4,1)
        #QUARTERLY CHANGES--> nothing right now
        if avg_change_3Y[0]>30 or avg_change_3Y[1]>30 or avg_change_3Y[2]>30 or avg_change_3Y[4]>30:
            mature = False
        else:
            mature = True
        stock_f.analyzeCompany(3,pe_value,avg_change_3Y,mature)

        stock_f.currentPrice() # assigns price to self.price
        current_prices.append(float(stock_f.price))
        if counter<=(len(new_dates)-1):
            stats = stock_f.priceStatistics(new_dates[counter])
        else:
            temp = datetime.datetime.strptime(new_dates[counter], '%Y-%m-%d')
            temp = (temp - datetime.timedelta(days=90)).strftime("%Y-%m-%d")#previous quarter
            # HEADER ['min','max','max_1y','max_2y']   
            stats = stock_f.priceStatistics(new_dates[counter]) # DONE--> ADD TO TABLES

        price_stats.append(stats)
        true_values.append(float(stock_f.true_value))
        expected_values.append(float(stock_f.expected_value))
    stock_f.finaldate = finaldate

    return new_dates,expected_values,true_values,price_stats,current_prices

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
            change = ((i.iloc[w]-i.iloc[w+period])/i.iloc[w+period])*100
            temp_change.append(change)
            dates.append(i.index.values[w])
        financials_change.append(temp_change)
        final_dates.append(dates)

    return financials_change,final_dates

def makeTable(dates,true_values,expected_values,price_stats,current_prices):
    table = [[]]
    c = 0
    for i in dates:
        price1 = price_stats[c][0]
        price2 = price_stats[c][1]
        if isinstance(price1,float):
            price1 = str(round(price1,3))
        if isinstance(price2,float):
            price2 = str(round(price2,3))

        temp = [i,current_prices[c],true_values[c],expected_values[c],price1+' - '+ price2,price_stats[c][2],price_stats[c][3]]
        table.append(temp)
        c+=1
    return table[:]



if __name__ =='__main__':
    main()
    