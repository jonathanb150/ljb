#!/usr/bin/env python
#FILE NAME: fundamentalAnalysisStocks.py
import sys, os
sys.path.append("/home/ubuntu/www/algorithms/lib")
from financialObjectViejo import financialObject
from stockObject import stockObject
from core import *
import matplotlib
matplotlib.use('Agg')
import scipy
import numpy as np
import plotly.plotly as py
import plotly
import plotly.graph_objs as go
import matplotlib.pyplot as plt,mpld3
import json
import sys
import datetime
import time



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
    stock_f.getFinancials('Revenue')
    stock_f.getFinancials('Profit')
    stock_f.getFinancials('Cash')
    stock_f.getFinancials('Equity')
    stock_f.getFinancials('Eps')
    stock_f.getFinancials('Debt')
## updating financials
    stock_f.revenue = stock_f.revenue[0:16]
    stock_f.profit = stock_f.profit[0:16]
    stock_f.cash = stock_f.cash[0:16]
    stock_f.equity = stock_f.equity[0:16]
    stock_f.eps = stock_f.eps[0:16]
    stock_f.debt = stock_f.debt[0:16]

## Finding financial changes over time
    (RevenueChangeY,ProfitChangeY,EquityChangeY,EpsChangeY,CashChangeY,RoiChangeY,DebtChangeY) = stock_f.financialChanges(4)
    (RevenueChangeQ,ProfitChangeQ,EquityChangeQ,EpsChangeQ,CashChangeQ,RoiChangeQ,DebtChangeQ) = stock_f.financialChanges(1)
    
## AVG Change in the last 2 and 4 quarters and last 3 years
## output order [RevenueChange,ProfitChange,EquityChange,EpsChange,CashChange,RoiChange,DebtChange]
    changes2Q = stock_f.avgFinancialsChange(2,RevenueChangeQ,ProfitChangeQ,EquityChangeQ,EpsChangeQ,CashChangeQ,RoiChangeQ,DebtChangeQ)
    changesY = stock_f.avgFinancialsChange(3,RevenueChangeY,ProfitChangeY,EquityChangeY,EpsChangeY,CashChangeY,RoiChangeY,DebtChangeY)

    if changesY == 'Not Enough Info':
        sys.exit(['Not Enough Financial Info'])
## Analyzing financials and getting true and expected price 
## period of 3 Years
    dic= {}
    ## greater than 30%  == growth company
    if changesY[0]>30 or changesY[1]>30 or changesY[2]>30 or changesY[4]>30:
        # price with 3Y financials
        stock_f.analyzeGrowthCompany(3,pe1,changesY[0],changesY[1],changesY[2],changesY[4])
        temp_trueval = stock_f.trueValue
        temp_expectedval = stock_f.expectedValue
        #price with 2Q financials
        stock_f.analyzeGrowthCompany(1,pe1,changes2Q[0],changes2Q[1],changes2Q[2],changes2Q[4])
    else:
        # price with 3Y financials
        stock_f.analyzeMatureCompany(3,pe1,changesY[0],changesY[1],changesY[2],changesY[4],assetHeavy)        
        temp_trueval = stock_f.trueValue
        temp_expectedval = stock_f.expectedValue
        #price with 2Q financials
        stock_f.analyzeGrowthCompany(1,pe1,changes2Q[0],changes2Q[1],changes2Q[2],changes2Q[4])
        
    dic['truePrice'] =stock_f.trueValue*0.3 + temp_trueval*0.7
    dic['expectedPrice'] = stock_f.expectedValue*0.3 + temp_expectedval*0.7

# analyzing again using PE 2  
    if pe2 != 0 or pe2 != 0.0:
        if changesY[0]>30 or changesY[1]>30 or changesY[2]>30 or changesY[4]>30:
            # price with 3Y financials
            stock_f.analyzeGrowthCompany(3,pe1,changesY[0],changesY[1],changesY[2],changesY[4])
            temp_trueval = stock_f.trueValue
            temp_expectedval = stock_f.expectedValue
            #price with 2Q financials            
            stock_f.analyzeGrowthCompany(1,pe1,changes2Q[0],changes2Q[1],changes2Q[2],changes2Q[4])
        else:
            # price with 3Y financials
            stock_f.analyzeMatureCompany(3,pe1,changesY[0],changesY[1],changesY[2],changesY[4],assetHeavy)        
            temp_trueval = stock_f.trueValue
            temp_expectedval = stock_f.expectedValue
            #price with 2Q financials
            stock_f.analyzeGrowthCompany(1,pe1,changes2Q[0],changes2Q[1],changes2Q[2],changes2Q[4])
    
    dic['truePrice2'] =stock_f.trueValue*0.3 + temp_trueval*0.7

    dic['expectedPrice2'] = stock_f.expectedValue*0.3 + temp_expectedval*0.7

#riskReward Ratio using last 3 years info
    period = 3
    points = stock_f.riskRewardRatio(period,RevenueChangeY,ProfitChangeY,EquityChangeY,CashChangeY,RoiChangeY)

## Getting more changes to plot and display info
    changesQ1 = stock_f.avgRealFinancialsChange(1,RevenueChangeQ,ProfitChangeQ,EquityChangeQ,EpsChangeQ,CashChangeQ,RoiChangeQ,DebtChangeQ)
    changesQ2 = stock_f.avgRealFinancialsChange(2,RevenueChangeQ,ProfitChangeQ,EquityChangeQ,EpsChangeQ,CashChangeQ,RoiChangeQ,DebtChangeQ)
    changesQ4 = stock_f.avgRealFinancialsChange(4,RevenueChangeQ,ProfitChangeQ,EquityChangeQ,EpsChangeQ,CashChangeQ,RoiChangeQ,DebtChangeQ)
    changesY2 = stock_f.avgRealFinancialsChange(3,RevenueChangeY,ProfitChangeY,EquityChangeY,EpsChangeY,CashChangeY,RoiChangeY,DebtChangeY)

##Plotting
    plotChanges = [[RevenueChangeQ,ProfitChangeQ,EquityChangeQ,EpsChangeQ,CashChangeQ],[RevenueChangeY,ProfitChangeY,EquityChangeY,EpsChangeY,CashChangeY]]
    annualPeriod = []
    counter = 0
    iterate = range(int(np.ceil(len(stock_f.revenue.index[:-1])/4)))
    for i in iterate:
        annualPeriod.append(stock_f.revenue.index[counter])
        counter+=4

    period = [stock_f.revenue.index[:-1],annualPeriod]
    
    makingPlots(stock_f)
    barPlots(plotChanges,period)
    
## Just in case changes are not real  AKA--> changes are higher than the cap I use in the formula 
    
    if changesY2[0] != changesY[0]:
        changesY = changesY2
        changes2Q = changesQ2
    if changesY2[1] != changesY[1]:
        changesY = changesY2
        changes2Q = changesQ2

       
## Making Json
    names = ['rev','prof','equi','eps','cash','roi','rev3','prof3','equi3',
             'eps3','cash3','roi3','revq','profq','equiq','epsq','cashq','roiq',
             'revq2','profq2','equiq2','epsq2','cashq2','roiq2','currentRoi','riskRewardRatio']

    info = [RevenueChangeY[0],ProfitChangeY[0],EquityChangeY[0],EpsChangeY[0],CashChangeY[0],
            RoiChangeY[0],changesY[0],changesY[1],changesY[2],changesY[3],changesY[4],changesY[5],
            RevenueChangeQ[0],ProfitChangeQ[0],EquityChangeQ[0],EpsChangeQ[0],CashChangeQ[0],
            RoiChangeQ[0],changes2Q[0],changes2Q[1],changes2Q[2],changes2Q[3],changes2Q[4],changes2Q[5],
            stock_f.roi[0]*100,points]

    info_json = jsonOutputforPHP(dic,names,info)

## adding some new relationships to show after analysis is done
    info_json = relationships(dic, stock_f,changesY)
    
    info_json = json.dumps(info_json)
    print(info_json)
    
#---------------------------------------OTHER FUNCTIONS-----------------------------------------
def relationships(dic,stock_f,changesY):
    if stock_f.cash[0]>stock_f.debt[0] and changesY[4]>changesY[6]:
        dic['cash_flow'] = 'Company has a lot of cash, and it is increasing faster than debt!'
    else:
        dic['cash_flow'] = 'Company may run into cash flow troubles in the future.'
    if changesY[0]>6 and changesY[1]>6:
        dic['profitability'] = 'Revenue and Profit are increasing. Business is expanding!'
    else:
        dic['profitability'] = 'Either Revenue or Profit is not increasing as expected. This may be a problem in the future.'
    if stock_f.equity[0]>stock_f.debt[0]*5 and changesY[2]>changesY[6]:
        dic['stability'] = 'Company is growing and debt is under control.'
    elif stock_f.equity[0]>stock_f.debt[0]*5 and changesY[2]<changesY[6]:
        dic['stability'] = 'Debt is under control, but it is growing too fast.'
    else:
        dic['stability'] = 'Debt is out of control or company does not depend on Equity.'
    return dic
    
def jsonOutputforPHP(dic,names,info):
    counter =0
    for i in names:
        dic[i] = info[counter]
        counter+=1
    
    return dic

def barPlots(changes,period):
    temp_changesQ = []
    changesQ = []
    counter = 0
    count = 0
    for x in changes[0]:
        for i in x:
            if counter == 0:
                temp = (1+i/100)*1
                counter+=1
                temp_changesQ.append(temp)
            else:
                temp = (1+i/100)*temp_changesQ[counter-1]
                temp_changesQ.append(temp)
                counter+=1
        temp_changesQ = list(reversed(temp_changesQ))
        changesQ.append(temp_changesQ)
        temp_changesQ = []
        counter = 0
        count += 1
    Revenue = go.Bar(
    x=period[1],
    y=changes[1][0],
    marker=dict(
        color='rgba(55, 128, 191, 0.7)',
        line=dict(
            color='rgba(55, 128, 191, 1.0)',
        )
    ), name = 'Revenue'
)
        

    Profit = go.Bar(
    x=period[1],
    y=changes[1][1],
    marker=dict(
        color='rgba(0, 255, 0, 0.7)',
        line=dict(
            color='rgba(0, 255, 0, 1.0)',
        )
    ), name = 'Profit'
)

    Equity = go.Bar(
    x=period[1],
    y=changes[1][2],
    marker=dict(
        color='rgba(255,0,0, 0.7)',
        line=dict(
            color='rgba(255,0,0, 1.0)',
        )
    ), name = 'Equity'
)

    Eps = go.Bar(
    x=period[1],
    y=changes[1][3],
    marker=dict(
        color='rgba(250, 130, 10, 0.7)',
        line=dict(
            color='rgba(250, 130, 10, 1.0)',
        )
    ), name = 'Eps'
)

    Cash = go.Bar(
    x=period[1],
    y=changes[1][4],
    marker=dict(
        color='rgba(230, 121, 230, 0.7)',
        line=dict(
            color='rgba(230, 121, 230, 1.0)',
        )
    ), name = 'Cash'
)

    RevenueScatter = go.Scatter(
    x=period[0],
    y=changesQ[0],
    name = 'Quarterly Revenue'
)
    ProfitScatter = go.Scatter(
    x=period[0],
    y=changesQ[1],
    name = 'Quarterly Profit'
)
    EquityScatter = go.Scatter(
    x=period[0],
    y=changesQ[2],
    name = 'Quarterly Equity'
)
    EpsScatter = go.Scatter(
    x=period[0],
    y=changesQ[3],
    name = 'Quarterly Eps'
)
    CashScatter = go.Scatter(
    x=period[0],
    y=changesQ[4],
    name = 'Quarterly Cash'
)
    
    data = [Revenue,Profit,Equity,Eps,Cash,RevenueScatter,ProfitScatter,EquityScatter,EpsScatter,CashScatter]
    updatemenus=list([
        dict(
            buttons=list([   
                dict(
                    args=[{'visible': [True,False,False,False,False,True,False,False,False,False]}],
                    label='Revenue',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,True,False,False,False,False,True,False,False,False]}],
                    label='Profit',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,False,True,False,False,False,False,True,False,False]}],
                    label='Equity',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,False,False,True,False,False,False,False,True,False]}],
                    label='Eps',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,False,False,False,True,False,False,False,False,True]}],
                    label='Cash',
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
    fig['layout']['title'] = 'Financials Change'
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
    fig = plotly.tools.make_subplots(rows=3, cols=2, shared_xaxes=True,
                                     subplot_titles = ('Revenue','Equity', 'Cash', 'Eps', 'Profit', 'Debt'), print_grid=False)
    
    trace1 = go.Scatter(x = rev , y = stock_f.revenue, name = 'Revenue')
    trace2 = go.Scatter(x = equi , y = stock_f.equity, name = 'Equity')
    trace3 = go.Scatter(x=cash,y=stock_f.cash, name ='Cash')
    trace4 = go.Scatter(x = eps , y = stock_f.eps, name = 'Eps')
    trace5 = go.Scatter(x=prof,y=stock_f.profit, name ='Profit')
    trace6 = go.Scatter(x=debt,y=stock_f.debt, name ='Debt')

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

    fig = dict(layout=layout, data=data)
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
    
if __name__ =='__main__':
    main()
    