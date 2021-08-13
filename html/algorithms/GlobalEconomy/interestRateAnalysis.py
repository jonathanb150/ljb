#!/usr/bin/env python
#FILE NAME: interestRatesAnalysis.py
# Get interest rate change , graphs, and yield curve
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from financialObject import financialObject
from stockObject import stockObject
from core import *
import pandas as pd
import numpy as np
import scipy
import json
import sys
import mysql.connector
import datetime
import plotly
import plotly.plotly as py
import plotly.graph_objs as go

#sort company by change in price in many different periods
def priceChangeSort(stock,changeInPeriods):
    change = []
    for i in changeInPeriods:
        change.append(((stock.all_data['closing_price'][-1]-stock.all_data['closing_price'][-i])/stock.all_data['closing_price'][-i])*100)
    return change


def sortingData(financialChanges,tables):   
    sorted_data = sorted(financialChanges, reverse = True)
    #rearrenging tables
    sorted_tables = []
    counter = 0
    for i in sorted_data:
        for x in range(len(tables)):
            if i == financialChanges[counter]:
                sorted_tables.append(tables[counter])
                counter = 0
                break
            else:
                counter+=1
    return sorted_data,sorted_tables

def jsonOutputforPHP(dic,names,info):
    counter = 0
    for i in names:
        dic[i] = info[counter]
        counter+=1
    return dic

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

def makingIRPlot(ir,description):  
    # all data we are going to graph
    ir_dates = ir.all_data.index.values
    ir_values = ir.all_data
    #Plotting
  
    trace1 = go.Scatter(x = ir_dates , y = ir_values['closing_price'], name = 'Interest Rates', fill = 'tozeroy', opacity = 0.8)
    data = [trace1]
    
    layout = dict(
        title=description,
        xaxis=dict(
            rangeselector=dict(
                buttons=list([
                    dict(count=1,
                         label='1m',
                         step='month',
                         stepmode='backward'),
                    dict(count=6,
                         label='6m',
                         step='month',
                         stepmode='backward'),
                    dict(count=12,
                         label='1y',
                         step='month',
                         stepmode='backward'),
                    dict(count=36,
                         label='3y',
                         step='month',
                         stepmode='backward'),
                    dict(label='max',
                         step='all'),

                    ]),
                         ),
            rangeslider=dict(
                    yaxis=dict(
                            rangemode='auto'),
                visible = False),
            type='date'),
            )

    fig = dict(data=data, layout=layout)
    fig['layout'].update(height=450)
    fname = '/var/www/ljb.solutions/html/graphs/' + '%s' %(ir.stock_name) + '.html'
    plotly.offline.plot(fig, filename=fname)
    with open(fname, 'r') as content_file:
        content = content_file.read()
    return content

def makingYieldPlot(bondsValues):# make plots for 3Y,1Y,6M,3M,ACTUAL
    data = []
    name = ['3Y','1Y','6M','3M','ACTUAL']
    for i in range(len(bondsValues[0])):
        y_values = []
        for x in range(len(bondsValues)):
            y_values.append(bondsValues[x][i])
        maturity = [0.25,0.5,1,5,10,30] ## in years
        trace = go.Scatter( x = maturity ,y = y_values, name = '%s' %name[i])
        data.append(trace) # 5 plots = 5 traces
    
    updatemenu =list([
        dict(
            buttons=list([   
                dict(
                    args=[{'visible': [True,False,False,False,False]}],
                    label='Up to Date',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,True,False,False,False]}],
                    label='3Months',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,False,True,False,False]}],
                    label='6Months',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,False,False,True,False]}],
                    label='1Year',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,False,False,False,True]}],
                    label='3Years',
                    method='update',
                ),
                dict(
                    args=[{'visible': [True,True,True,True,True]}],
                    label='ALL',
                    method='update',
                    ),

            ]),
            direction = 'left',
            pad = {'r': 10, 't': 10},
            showactive = True,
            type = 'buttons',
            x = 0.0,
            xanchor = 'left',
            y = 1.2,
            yanchor = 'top' 
        ),
    ])
    layout=go.Layout(title="Bond Yield Curve",xaxis=dict(title='Maturity (Years)'),yaxis=dict(title='Interest Rate (%)'))
    
    fig = dict(data=data, layout=layout)
    fig['layout'].update(height=450)
    fig['layout']['updatemenus'] = updatemenu
    fname = '/var/www/ljb.solutions/html/graphs/YieldCurve.html'
    plotly.offline.plot(fig, filename=fname)
    with open(fname, 'r') as content_file:
        content = content_file.read()
    return content

def showAlltableNameAssets(asset,subtype = False,append = False): ##ALL tables for selected ASSETS. CHECKED WHEN ADDED TO DATABASE
# asset = tables of that type, append = append _1d or _f if desired
    tables = []
    conn = dbConnection()
    cursor = conn.cursor()
    if subtype == False:
        query = "SELECT tableName from `items` WHERE type='%s'" %asset
    else:
        query = "SELECT tableName from `items` WHERE type='%s' AND sub_type ='%s'" %(asset,subtype)
    cursor.execute(query)
    row = cursor.fetchone()
    while isinstance(row,tuple):
        if append == False:
            tables.append(row[0])
        else:
            tables.append(row[0]+ append)
        row = cursor.fetchone()
           
    return tables

def showAllTableNamesAssets(asset,subtype = False,append = False): ##ALL tables for selected ASSETS. CHECKED WHEN ADDED TO DATABASE
# asset = tables of that type, append = append _1d or _f if desired
    tables = []
    conn = dbConnection()
    cursor = conn.cursor()
    if subtype == False:
        query = "SELECT tableName from `items` WHERE type='%s'" %asset
    else:
        query = "SELECT tableName from `items` WHERE type='%s' AND sub_type ='%s'" %(asset,subtype)
    cursor.execute(query)
    row = cursor.fetchone()
    while isinstance(row,tuple):
        if append == False:
            tables.append(row[0])
        else:
            tables.append(row[0]+ append)
        row = cursor.fetchone()
           
    return tables

def yieldCurveData(tables):
    bondsChange = []
    bondsValues = []
    bondsNames =[]
    oneyear = 252 # trading day
    for i in tables:
        temp = stockObject()
        temp.stock_name = i
        temp.final_date = todaysDate()
        ## final date =  3 years ago
        temp.init_date = str(int(temp.final_date.split('-',1)[0])-3) + '-' + (temp.final_date.split('-',1)[1])
        temp.getData_df()
        bondsChange.append(priceChangeSort(temp,[36,12,6,3,1])) ## numbers must correspond at time in bonds
        bondsValues.append([temp.all_data['closing_price'][0],temp.all_data['closing_price'][-oneyear],
            temp.all_data['closing_price'][int(scipy.floor(-oneyear/2))],temp.all_data['closing_price'][int(scipy.floor(-oneyear/4))],
            temp.all_data['closing_price'][-1]])
        bondsNames.append(i)
    
    return (bondsChange,bondsValues,bondsNames)


def getStockDescription(symbol):
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT name from `items` WHERE symbol='%s'" %symbol  
    cursor.execute(query)
    row = cursor.fetchone()
    description = row[0]
    
    return description

def main():

## Initializing IR info-------------------------------------------------------
## IR ANALYSIS 
    ir = stockObject()
    ir.stock_name= 'FEDFUNDS'
    ir.final_date = todaysDate()
    ir.init_date = '1972-05-05'
    #final date, in one year
    #ir.final_date = str(int(ir.init_date.split('-',1)[0])-1) + '-' + (ir.init_date.split('-',1)[1])

    ir.getData_df()
    # change in 3Y,1Y,6M,3M
    change = priceChangeSort(ir,[36,12,3,6])
    #description of stock
    symbol = 'FEDFUNDS'
    description = getStockDescription(symbol)

## graph Data
    irPlot = makingIRPlot(ir,description)
    
## yield curve---------------------------------------------------------------
    tables = showAlltableNameAssets('us_bond') # I am going to hard code the values because there is no way I can
    # differentiate between which bond is which and I need them in order
    # makingYieldPlot also hard coded!!
    tables = ['DGS1MO','DGS3MO','DGS1','DGS5','DGS10','DGS30']

    bondsChange,bondsValues,bondsNames = yieldCurveData(tables)
## graph data
    graph = makingYieldPlot(bondsValues)
    
    
## output
    dic = {}
    #names = ['currentIR','IRChange3y','IRChange1y','IRChange3m','IRChange6m']
    info = [['Last 3 Months','Last 6 Months','Last Year','Last 3 Years'], [change[2],change[3],change[1],change[0]]]
    
    dic['US Interest Rates'] = info
    dic['Ir Plot'] = irPlot
    dic['Yield Curve'] = graph
    info_json = json.dumps(dic,sort_keys=True)

    print(info_json) 

if __name__ == '__main__':
    main()
