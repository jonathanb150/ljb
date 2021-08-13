#!/usr/bin/env python
#FILE NAME: fundamentalAnalysisStocks.py
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from financialObject import financialObject
from stockObject import stockObject
from core import *
import matplotlib
matplotlib.use('Agg')
import scipy
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
    MatureOrGrowth = sys.argv[4] # str Mature or Growth
    assetHeavy = bool(sys.argv[5]) # empty str --> No str-True --> Yes
    stock_f.finaldate = sys.argv[6]

    stock_f.name = name + '_f'   
    
## GETTING ALL FINANCIAL INFO AND STORING AS ATTRIBUTES
    stock_f.getFinancials('Revenue')
    stock_f.getFinancials('Profit')
    stock_f.getFinancials('Cash')
    stock_f.getFinancials('Equity')
    stock_f.getFinancials('Eps')
    stock_f.getFinancials('Debt')

## updating financials
    stock_f.revenue = financialsLast3Years(stock_f,stock_f.revenue)
    stock_f.profit = financialsLast3Years(stock_f,stock_f.profit)
    stock_f.cash = financialsLast3Years(stock_f,stock_f.cash)
    stock_f.equity = financialsLast3Years(stock_f,stock_f.equity)
    stock_f.eps = financialsLast3Years(stock_f,stock_f.eps)
    stock_f.debt = financialsLast3Years(stock_f,stock_f.debt)


## Finding financial changes over time

    (RevenueChangeY,ProfitChangeY,EquityChangeY,EpsChangeY,CashChangeY,RoiChangeY,DebtChangeY) = stock_f.financialChanges(4)
    (RevenueChangeQ,ProfitChangeQ,EquityChangeQ,EpsChangeQ,CashChangeQ,RoiChangeQ,DebtChangeQ) = stock_f.financialChanges(1)
    
## AVG Change in the last 2 quarters and last 3 years
## output order [RevenueChange,ProfitChange,EquityChange,EpsChange,CashChange,RoiChange,DebtChange]
    changesQ = stock_f.avgFinancialsChange(2,RevenueChangeQ,ProfitChangeQ,EquityChangeQ,EpsChangeQ,CashChangeQ,RoiChangeQ,DebtChangeQ)
    changesY = stock_f.avgFinancialsChange(3,RevenueChangeY,ProfitChangeY,EquityChangeY,EpsChangeY,CashChangeY,RoiChangeY,DebtChangeY)
    if changesY == 'Not Enough Info':
        sys.exit(['Not Enough Financial Info'])
## Analyzing financials and getting true and expected price 
## period of 3 Years
    dic= {}
    if MatureOrGrowth == 'Mature':
        stock_f.analyzeMatureCompany(3,pe1,changesY[0],changesY[1],changesY[2],changesY[4],assetHeavy)
    else:
        stock_f.analyzeGrowthCompany(3,pe1,changesY[0],changesY[1],changesY[2],changesY[4])        

    dic['truePrice'] =stock_f.trueValue

    dic['expectedPrice'] = stock_f.trueValue*1.2

# analyzing again using PE 2  
    if pe2 != 0 or pe2 != 0.0:
        if MatureOrGrowth == 'Mature':
            stock_f.analyzeMatureCompany(3,pe2,changesY[0],changesY[1],changesY[2],changesY[4],assetHeavy)

        else:
            stock_f.analyzeGrowthCompany(3,pe2,changesY[0],changesY[1],changesY[2],changesY[4])        
    
    dic['truePrice2'] =stock_f.trueValue

    dic['expectedPrice2'] = stock_f.trueValue*1.2

#riskReward Ratio using last 3 years info
    period = 3
    points = stock_f.riskRewardRatio(period,RevenueChangeY,ProfitChangeY,EquityChangeY,CashChangeY,RoiChangeY)

##Plotting
    makingPlots(stock_f)
    
## Just in case changes are not real   
    changesQ2 = stock_f.avgRealFinancialsChange(2,RevenueChangeQ,ProfitChangeQ,EquityChangeQ,EpsChangeQ,CashChangeQ,RoiChangeQ,DebtChangeQ)
    changesY2 = stock_f.avgRealFinancialsChange(3,RevenueChangeY,ProfitChangeY,EquityChangeY,EpsChangeY,CashChangeY,RoiChangeY,DebtChangeY)
    if changesY2[0] != changesY[0]:
        dic['truePrice'] = 0
        dic['truePrice2'] = 0
        changesY = changesY2
        changesQ = changesQ2
    if changesY2[1] != changesY[1]:
        dic['truePrice'] = 0
        dic['truePrice2'] = 0
        changesY = changesY2
        changesQ = changesQ2
       
## Making Json
    names = ['rev','prof','equi','eps','cash','roi','rev3','prof3','equi3',
             'eps3','cash3','roi3','revq','profq','equiq','epsq','cashq','roiq',
             'revq2','profq2','equiq2','epsq2','cashq2','roiq2','currentRoi','riskRewardRatio']

    info = [RevenueChangeY[0],ProfitChangeY[0],EquityChangeY[0],EpsChangeY[0],CashChangeY[0],
            RoiChangeY[0],changesY[0],changesY[1],changesY[2],changesY[3],changesY[4],changesY[5],
            RevenueChangeQ[0],ProfitChangeQ[0],EquityChangeQ[0],EpsChangeQ[0],CashChangeQ[0],
            RoiChangeQ[0],changesQ[0],changesQ[1],changesQ[2],changesQ[3],changesQ[4],changesQ[5],
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
                    dict(count=20,
                         label='1m',
                         step='day',
                         stepmode='backward'),
                    dict(count=120,
                         label='6m',
                         step='day',
                         stepmode='backward'),
                    dict(count=240,
                         label='1y',
                         step='day',
                         stepmode='backward'),
                    dict(count=720,
                         label='3y',
                         step='day',
                         stepmode='backward'),
                    dict(label='max',
                         step='all'),

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
    fig['layout']['title'] = 'Financials Change'
    fig['layout']['updatemenus'] = updatemenus
    layout['annotations'] = annotations
    fig['layout']['showlegend'] = True
    fig['layout'].update(height=800)
    
    plotly.offline.plot(fig, filename='/var/www/ljb.solutions/html/graphs/plot1Fundamentals.html')

def financialsLast3Years(stock_f,financial_df):
    finalyear = int(stock_f.finaldate[:4])
    counter = 0
    if int(((financial_df.index.values[-1]).strftime('%Y-%m-%d'))[:4])>finalyear:
        sys.exit('Not Enough Financial Info')
    while counter!=50000: # 50k so It doesnt run forever in case of error
        dates = (financial_df.index.values[counter]).strftime('%Y-%m-%d')
        year = int(dates[:4])
        if year == finalyear:
            break
        counter+= 1

    temp_month = int(dates[5:7])
    desired_month = int(stock_f.finaldate[5:7])
    if (desired_month-temp_month)<=3 and (desired_month-temp_month)>=0:
        financial_temp_df = financial_df[counter:counter+12+4]# 12 = 3 years in quarters, +4 = an extra year to find change in last 3 years
    else:
        financial_temp_df = financial_df[counter:counter+12+4]
        counter+=1
        dates = (financial_df.index.values[counter]).strftime('%Y-%m-%d')
        temp_month2 = int(dates[5:7])
        year = int(dates[:4])
        if (desired_month-temp_month2)<=3 and (desired_month-temp_month2)>=0 and finalyear==year: # if new diff less than previous diff
            financial_temp_df = financial_df[counter:counter+12+4]
        else:
            counter+=1
            dates = (financial_df.index.values[counter]).strftime('%Y-%m-%d')
            temp_month3 = int(dates[5:7])
            if (desired_month-temp_month3)<=3 and (desired_month-temp_month3)>=0 and finalyear==year:
                financial_temp_df = financial_df[counter:counter+12+4]
            else:
                counter+=1
                financial_temp_df = financial_df[counter:counter+12+4]   
    return financial_temp_df

if __name__ =='__main__':
    main()
    