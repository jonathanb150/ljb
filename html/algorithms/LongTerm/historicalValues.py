#!/usr/bin/env python
#FILE NAME: historicalPe

import sys
sys.path.append("/home/ubuntu/www/algorithms/lib")
from financialObject import financialObject
import scipy
import mysql.connector
import json
import plotly.plotly as py
import plotly
import plotly.graph_objs as go

def main():

    stock_f,truevalue = getInputs()
    makingPlots(stock_f)

    ## returns PE today, One year AVG, 3 Year avg
    pes = stock_f.currentPeRatio()
    
    if pes[0]>0 and pes[1]>0 and pes[2]>0:
        truevalues = [round(float(truevalue),2),round(float(pes[1]/pes[0])*float(truevalue),2), round(float(pes[2]/pes[0])*float(truevalue),2)]
    else:
        truevalues = ['Negative Pe Ratio','Negative Pe Ratio','Negative Pe Ratio']
    header = ['Time','Pe Ratio','True Value']
    years = ['Today','1 Year', '3 Years']

    data = makeTable(pes,truevalues,years)
    dic = {}
    dic['table'] = [header,data]

    dic = json.dumps(dic)
    print(dic)

def makeTable(pes,truevalues,years):
    data = []
    c = 0
    for i in pes:
        data.append(years[c])
        data.append(round(float(i),2))
        data.append(truevalues[c])
        c += 1

    return data

def getInputs():
	stock_f = financialObject()
	stock_f.name = sys.argv[1] + '_f'
	stock_f.finaldate = sys.argv[2] 
	truevalue = float(sys.argv[3])
	stock_f.initdate = str(float(stock_f.finaldate[:4]) - 3) + stock_f.finaldate[4:]
	stock_f.getFinancials('Revenue','Profit','Eps','Cash','Equity','Debt')

	return stock_f,truevalue

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
    fig = go.Figure(data=data, layout=layout)
   # fig = dict(layout=layout, data=data)
    fig['layout']['title'] = 'Financials Growth'
    fig['layout']['updatemenus'] = updatemenus
    layout['annotations'] = annotations
    fig['layout']['showlegend'] = True
    fig['layout'].update(height=800)
    plotly.offline.plot(fig, filename='./graphs/HistoricalFundamentals.html')

if __name__ =='__main__':
    main()
