#!/usr/bin/env python
#FILE NAME: TechnicalAnalysisStock

## TECHNICAL ANALYSIS ALGORITHM
## CALCULATES ENTRY POINTS BASED ON SMAs, STOCHASTIC, and CROSSOVERS
## USE ONLY FOR STOCKS
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from stockObject import stockObject
from financialObject import financialObject
from core import *
import scipy
import datetime
from typing import Union, List, Dict
import plotly
import plotly.graph_objs as go
import json
import sys

def main():
     # creating stock object
    stock = stockObject()
    # Getting info from user
    stock.stock_name = sys.argv[1]
    real_init_date = sys.argv[2]
    stock.init_date = str(int(real_init_date[:4])-1)+real_init_date[4:]
    stock.final_date = sys.argv[3]

    # SHORT TERM ANALYSIS
# getting data and calculating SMAs, stochs, and finding crossovers and over bought/sold opportunities
    stock.getData_df()

    stock.period1=50
    stock.period2=20
    stock.period3=14
# setting init date selected by user
    stock.requested_data = stock.all_data[255:] # 255 is one year
    stock.init_date = real_init_date
    stock.SMAsCrossover()

    longTermEntryPoints_up_short = stock.SMAs_intersectionIndex_up
    longTermEntryPoints_down_short = stock.SMAs_intersectionIndex_down

 ## LONG TERM ANALYSIS - GETTING POINTS
    stock.period1 = 100
    stock.period2 = 50
    stock.period3 = 20
    stock.SMAsCrossover()

    #calculating SMAs, stochs, and finding crossovers and over bought/sold opportunities
    ## LONG TERM ANALYSIS
    stoch,d = stock.stochastic()
    longTermEntryPoints_up = stock.SMAs_intersectionIndex_up
    longTermEntryPoints_down = stock.SMAs_intersectionIndex_down

    resistance100 = stock.sma1[-1]
    #Determining if we are in bull or bear market ( above 200 SMA BULL, below BEAR)
    stock.period1=200
    stock.period2=100
    stock.period3=50
    stock.SMAsCrossover()
    ## GETTING REAL SHORT AND LONG TERM ENTRY POINTS
    stock.realIntersectionPoints(longTermEntryPoints_up_short,longTermEntryPoints_down_short)
    st_stars_index = stock.SMAs_intersectionIndex_up
    stock.realIntersectionPoints(longTermEntryPoints_up,longTermEntryPoints_down)
 ## ------------------------ PLOTTING ------------------------------------------------------------   
    makingPlot(stock,stoch,d,st_stars_index)
    info_json = phpOutput(stock,d)
    #info_json['resistanceLong'] = stock.sma1[-1]
    #info_json['resistanceShort'] = resistance100
    info_json = json.dumps(info_json)
    print(info_json)
    
    
    
## DONE   
    
    
    
def makingPlot(stock,stoch,d,st_stars_index):   

    stock_f = financialObject()
    stock_f.name = sys.argv[1][:-3] + '_f'
    stock_f.initdate = stock.init_date
    stock_f.finaldate = stock.final_date
    stock_f.getFinancials('Revenue','Profit','Cash','Debt')

    # econ data
    econ_data = stockObject()
    econ_data.init_date = stock.init_date
    econ_data.final_date = stock.final_date
    data = []
    econ_tablenames = ['SPX','UNRATE','FEDFUNDS','GDPQOQ','CPALTT01USM659N']
    for x in econ_tablenames:
        econ_data.stock_name = x
        econ_data.getData_df()
        data.append(econ_data.all_data[:])


    # all data we are going to graph
    dates = stock.requested_data.index.values
    closing = stock.requested_data['closing_price'][:]
    lt_stars = stock.requested_data.index.values[stock.SMAs_intersectionIndex_up]
    st_stars = stock.requested_data.index.values[st_stars_index]
    volume = stock.requested_data['vol']
    
    #Plotting stock, entry points

    
    trace1 = go.Scattergl(x = lt_stars , y = closing[stock.SMAs_intersectionIndex_up],
                        mode = 'markers',marker = dict(size=10) , name = 'Strong Buy')
    
    trace2 = go.Scattergl(x = st_stars , y = closing[st_stars],
                        mode = 'markers', marker = dict(size=10) , name = 'Potential Buy')

    trace3 = go.Candlestick(x=dates,
                open=stock.requested_data['opening_price'][:].values,
                high=stock.requested_data['high_price'][:].values,
                low=stock.requested_data['low_price'][:].values,
                close=stock.requested_data['closing_price'][:].values,
                yaxis = 'y', name = 'Candlestick')
    
    trace4 = go.Scattergl(x=dates,y=closing,name ='Closing Price', yaxis ='y')
    

    trace5 = go.Scattergl(x = dates[-len(stoch):] , y = stoch, name = 'Stochastic',
                                  visible = False, yaxis ='y2', line = dict( width = 2 , color='rgba(205, 12, 24,0.6)'))
    trace6 = go.Scattergl(x = dates[-len(stoch):] , y = d, name = 'Stochastic',
                        visible = False, yaxis ='y2', line = dict( width = 3 , color='rgba(22, 96, 167,0.8)'))
    
    trace7 = go.Scattergl(x = dates , y = volume, name = 'Volume', visible = False
                        , yaxis ='y2', fill = 'tozeroy')

    trace8 = go.Scattergl(x=dates,y=stock.sma1,name ='SMA200', yaxis ='y1',visible = True)
    trace9 = go.Scattergl(x=dates,y=stock.sma2,name ='SMA100', yaxis ='y1',visible = True)
    trace10 = go.Scattergl(x=dates,y=stock.sma3,name ='SMA50', yaxis ='y1',visible = True)

    trace11 = go.Bar(x=stock_f.revenue.index.values, y= stock_f.revenue, name ='Revenue', yaxis ='y2',visible = False, opacity = 0.6)
    trace12 = go.Bar(x=stock_f.cash.index.values, y= stock_f.cash, name ='Cash', yaxis ='y2',visible = False, opacity = 0.6)
    trace13 = go.Bar(x=stock_f.debt.index.values, y= stock_f.debt, name ='Debt', yaxis ='y2',visible = False, opacity = 0.6)
    trace14 = go.Bar(x=stock_f.profit.index.values, y= stock_f.profit, name ='Profit', yaxis ='y2',visible = False, opacity = 0.6)

    trace15 = go.Scattergl(x = [dates[-len(stoch):][-1]] , y = [d.max()*3.5], name = '',
                        visible = False, yaxis ='y2', mode= 'markers', marker = dict(color = 'rgba(255, 255, 255, 08)'))

    trace16 = go.Scattergl(x=data[0].index.values,y=data[0]['closing_price'][:].values,name ='S&P500', yaxis ='y3',visible = False)
    trace17 = go.Scattergl(x=data[1].index.values,y=data[1]['closing_price'][:].values,name ='Unemployment Rate', yaxis ='y2',visible = False, fill = 'tozeroy', opacity = 0.4)
    trace18 = go.Scattergl(x=data[2].index.values,y=data[2]['closing_price'][:].values,name ='Fed Fund Rates', yaxis ='y2',visible = False, fill = 'tozeroy', opacity = 0.4)
    trace19 = go.Bar(x=data[3].index.values, y= data[3]['closing_price'][:].values, name ='GDP QoQ', yaxis ='y2',visible = False, opacity = 0.8)
    trace20 = go.Bar(x=data[4].index.values, y= data[4]['closing_price'][:].values, name ='CPI YoY', yaxis ='y2',visible = False, opacity = 0.8)
    
    data = [trace1,trace2,trace3,trace4,trace5,trace6,trace7,trace8,trace9,trace10,trace11,trace12,trace13,trace14,trace15,
    trace16,trace17,trace18,trace19,trace20]
    
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
            rangeslider = dict(visible = False),
            type='date',domain=[0, 1]
        ),
        yaxis=dict(
            domain=[0, 1],
            anchor = 'y'
        ),
        yaxis2=dict(side= 'right', overlaying= 'y', showgrid = False, zeroline = False),
        yaxis3=dict(side= 'right', overlaying= 'y', showgrid = False, zeroline = False, showline= False, visible = False))
                  
    updatemenus=list([
        dict(
            buttons=list([   
                dict(
                    args=[{'visible': [True,True,True,True,False,False,True,True,True,True,False,False,False,False,False,False,False,False,False,False]}],
                    label='Volume',
                    method='update',
                ),
                dict(
                    args=[{'visible': [True,True,True,True,True,True,False,True,True,True,False,False,False,False,True,False,False,False,False,False]}],
                    label='Stochastic',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,False,True,True,False,False,False,False,False,False,True,True,True,True,False,False,False,False,False,False]}],
                    label='Fundamentals',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,False,False,True,False,False,False,False,False,False,False,False,False,False,False,True,True,True,False,True]}],
                    label='Econ Fundamentals',
                    method='update',
                ),
                dict(
                    args=[{'visible': [True,True,True,True,False,False,False,True,True,True,False,False,False,False,False,False,False,False,False,False]}],
                    label='Reset',
                    method='update',
                )                 

            ]),
            direction = 'left',
            pad = {'r': 10, 't': 10},
            showactive = True,
            type = 'buttons',
            x = 0.05,
            xanchor = 'left',
            y = 1.2,
            yanchor = 'top' 
        ),
    ])

    fig = dict(layout=layout, data=data)
    fig['layout']['title'] = '%s Daily Chart' %stock.stock_name[:-3]
    fig['layout']['showlegend'] = True
    fig['layout']['updatemenus'] = updatemenus
    fig['layout'].update(height=800)
    
    plotly.offline.plot(fig, filename='/var/www/ljb.solutions/html/graphs/plot1Stocks.html')

def phpOutput(stock,d):    
    ## Preparing output for PHP
    margin = datetime.timedelta(days=60)
    for x in ['vol','stoch','LG Cross', 'ST Cross']:
        if x == 'vol':
            if scipy.average(stock.requested_data[x][-30:])> stock.requested_data[x][-1]:
                vol = 'High'
            else:
                vol = 'Low'
        elif x == 'stoch':
            if scipy.average(d[-7:])> 70:
                stoc = 'Overbought'
            elif scipy.average(d[-7:])< 30:
                stoc = 'Oversold'
            else:
                stoc = 'Medium'
        elif x =='LG Cross':
            if len(stock.SMAs_intersectionIndex_up) == 0:
                longTerm = 'No'
            elif stock.requested_data.index.values[stock.SMAs_intersectionIndex_up[-1]]+margin >=stock.requested_data.index.values[-1]:
                longTerm = 'Yes'
            else:
                longTerm = 'No'
        else:
            if len(stock.SMAs_intersectionIndex_down) == 0:
                shortTerm = 'No'
            elif stock.requested_data.index.values[stock.SMAs_intersectionIndex_down[-1]]+margin >=stock.requested_data.index.values[-1]:
                shortTerm = 'Yes'
            else:
                shortTerm = 'No'
    stockPrice = stock.requested_data['closing_price'][-1]
## Variables to Return to PHP
    dic = {}
    info_header = ['price','vol','stoch','Strong Buy', 'Buying Momentum']
    info = [round(float(stockPrice),2),vol,stoc,longTerm,shortTerm]

    dic['table'] = [info_header,info]
    return dic
    
if __name__ == '__main__':
    main()
