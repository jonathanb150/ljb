#!/usr/bin/env python
#FILE NAME: TechnicalAnalysisStock

## TECHNICAL ANALYSIS ALGORITHM
## CALCULATES ENTRY POINTS BASED ON SMAs, STOCHASTIC, and CROSSOVERS
## USE ONLY FOR STOCKS
import sys, os
sys.path.append("/home/ubuntu/www/algorithms/lib")
from financialObject import financialObject
from stockObject import stockObject
from core import *
import matplotlib
import matplotlib.pyplot as plt,mpld3
import scipy
import datetime
from typing import Union, List, Dict
import plotly.plotly as py
import plotly
import plotly.graph_objs as go
matplotlib.use('Agg')
import json
import sys

def main():
     # creating stock object
    stock = stockObject()
    # Getting info from user
    stock.stock_name = sys.argv[1]
    stock.init_date = sys.argv[2]
    stock.final_date = sys.argv[3]
    
    # getting data and calculating SMAs, stochs, and finding crossovers and over bought/sold opportunities
    stock.getData_df()

    stock.period1=50
    stock.period2=20
    stock.period3=14
    stock.SMAsCrossover()

    resistanceShort = stock.sma1[-1]
    resistanceVeryShort = stock.sma2[-1]

    stoch,d = stock.stochastic()
    longTermEntryPoints_up = stock.SMAs_intersectionIndex_up
    longTermEntryPoints_down = stock.SMAs_intersectionIndex_down

    stock.period1 = 100
    stock.period2 = 50
    stock.period3 = 20
    stock.SMAsCrossover()

    resistanceLong = stock.sma1[-1]
    ## REAL POINTS ABOVE 100 sma -----------------------------------------------------------------
    stock.realIntersectionPoints(longTermEntryPoints_up,longTermEntryPoints_down)
 ## ------------------------ PLOTTING ------------------------------------------------------------   
    makingPlot(stock,stoch,d)
    info_json = phpOutput(stock,d)
    info_json['resistanceLong'] = resistanceLong
    info_json['resistanceShort'] = resistanceShort
    info_json['resistanceVeryShort'] = resistanceVeryShort
    info_json = json.dumps(info_json)
    print(info_json)
    
    
    
## DONE   
    
    
    
def makingPlot(stock,stoch,d):       

    # all data we are going to graph
    dates = stock.requested_data.index.values
    closing = stock.requested_data['closing_price']
    lt_stars = stock.requested_data.index.values[stock.SMAs_intersectionIndex_up]
    st_stars=stock.requested_data.index.values[stock.SMAs_intersectionIndex_down]
    volume = stock.requested_data['vol']
    
    ratio = max(closing)*0.3
    ratio2 = max(closing)*0.5
    #Plotting stock, entry points, and interest Rates
    fig = plotly.tools.make_subplots(rows=2, cols=1, shared_xaxes=True, print_grid=False)
    
    trace1 = go.Scatter(x = lt_stars , y = closing[stock.SMAs_intersectionIndex_up],
                        mode = 'markers',marker = dict(size=10) , name = 'Strong Buy')
    
    trace2 = go.Scatter(x = st_stars , y = closing[stock.SMAs_intersectionIndex_down],
                        mode = 'markers', marker = dict(size=10) , name = 'Buy')
    
    trace0 = go.Scatter(x=dates,y=closing,name ='Price', yaxis ='y')
    
    trace4 = go.Scatter(x = dates[-len(stoch):] , y = stoch/max(stoch)*ratio+ratio2, name = 'Stochastic',
                                  visible = False, yaxis ='y')
    trace5 = go.Scatter(x = dates[-len(stoch):] , y = d/max(d)*ratio+ratio2, name = 'Stoch Avg',
                        visible = False, yaxis ='y')
    
    trace6 = go.Scatter(x = dates , y = volume/max(volume)*ratio+ratio2, name = 'Volume', visible = False
                        , yaxis ='y')

    trace7 = go.Scatter(x=dates,y=stock.sma1,name ='SMA100', yaxis ='y1',visible = True)
    trace8 = go.Scatter(x=dates,y=stock.sma2,name ='SMA50', yaxis ='y1',visible = True)
    trace9 = go.Scatter(x=dates,y=stock.sma3,name ='SMA20', yaxis ='y1',visible = True)
    
    data = [trace1,trace2,trace0,trace4,trace5,trace6,trace7,trace8,trace9]
    
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
            type='date',domain=[0, 1]
        ),
        yaxis=dict(
            domain=[0, 1],
            anchor = 'y'
        ))
                  
    updatemenus=list([
        dict(
            buttons=list([   
                dict(
                    args=[{'visible': [True,True,True,False,False,True,True,True,True]}],
                    label='Volume',
                    method='update',
                ),
                dict(
                    args=[{'visible': [True,True,True,True,True,False,True,True,True]}],
                    label='Stochastic',
                    method='update',
                ),
                dict(
                    args=[{'visible': [True,True,True,False,False,False,True,True,True]}],
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
    
    plotly.offline.plot(fig, filename='./graphs/plot1Stocks.html')

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
    info = [stockPrice,vol,stoc,longTerm,shortTerm]
    for x in range(len(info)):
        dic[info_header[x]] = info[x]
    
    return dic

if __name__ == '__main__':
    main()
    

    

    
    
