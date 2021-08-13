#!/usr/bin/env python
#FILE NAME: fundamentalAnalysis.py
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
        change.append(((stock.all_data[-1]-stock.all_data[-i])/stock.all_data[-i])*100)
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

def showAllSymbolsAssets(asset,subtype = False,append = False): ##ALL tables for selected ASSETS. CHECKED WHEN ADDED TO DATABASE
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


def getChangeOfTables(tables,initDate,finalDate):
    stockFundamentals = stockObject()
    change = []
    changes = []
    fundamentalsData = []
    changesintime = [1095,365,183,91]

    for i in range(len(tables)):
        stockFundamentals.stock_name = tables[i]
        stockFundamentals.final_date = finalDate
        stockFundamentals.init_date = initDate
        stockFundamentals.getData_df()
        fundamentalsData.append(stockFundamentals.all_data)

        for x in changesintime:
            delta = datetime.timedelta(days=x)
            d = datetime.datetime.strptime(finalDate, '%Y-%m-%d')
            stockFundamentals.init_date = d - delta
            
            try:
                stockFundamentals.getData_df()
                todays_price = stockFundamentals.all_data['closing_price'][-1]
                previous_price = stockFundamentals.all_data['closing_price'][0]
                change.append(((todays_price/previous_price) -1)*100)# change x ammount of time ago 
            except:
                change.append(0)# change x ammount of time ago 
        
                
        changes.insert(0,change)
        change = []
        
    return changes,fundamentalsData

def getStockDescription(tableName):
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT name from `items` WHERE tableName='%s'" %tableName
    cursor.execute(query)
    row = cursor.fetchone()
    description = row[0]
    
    return description

def makingFundamentalPlot(fundamentals,descriptions):
    count = 0
    data = []
    visibility = []
    dics = []
    visible = True
    for i in range(len(fundamentals)):
        visibility.append(False)
    for i in fundamentals:
        dic = {}
        dic['label'] = descriptions[count]
        dic['method'] = 'update'
        visibility_temp = visibility.copy()
        visibility_temp[count] = True
        dic['args'] = [{'visible': visibility_temp}]

        dates = i.index.values.tolist()
        y_values = i['closing_price']
        trace = go.Scatter( x = dates ,y = y_values, name = '%s' %descriptions[count],visible= visible, fill = 'tonextx', opacity = 0.8)
        visible = False
        count+=1
        data.append(trace)
        dics.append(dic)
    
    layout = dict(
        title='Economic Indicators',
        showlegend = False,
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
                visible = False),
            type='date'),
            )

    updatemenus=list([
        dict(
            buttons= dics,              
            direction = 'down',
            pad = {'r': 10, 't': 10},
            showactive = True,
            x = 0.53,
            xanchor = 'left',
            y = 1.08,
            yanchor = 'top' ) ])    

    fname = '/var/www/ljb.solutions/html/graphs/' + 'fundamentals.html'
    fig = dict(data=data, layout=layout)
    fig['layout']['updatemenus'] = updatemenus
    plotly.offline.plot(fig, filename = fname)
    with open(fname, 'r') as content_file:
        content = content_file.read()
    return content

def main():

## analyze and show any fundamental
    fundamentals = sys.argv[1] # fundamental type (database type)
    try:
        initDate= (sys.argv[2])
        finalDate = (sys.argv[3])
    except:
        finalDate= todaysDate()
        initDate = str(int(finalDate.split('-',1)[0])-3) + '-' + (finalDate.split('-',1)[1])# 4 years ago

    table_name = showAllTableNamesAssets(fundamentals)

    change,fundamentalsData = getChangeOfTables(table_name,initDate,finalDate)
    dic = {}
    descriptions = []
    for i in table_name:
        description = getStockDescription(i)
        descriptions.append(description)
    
    plot = makingFundamentalPlot(fundamentalsData,descriptions)
    dic['graph'] = plot
    dic['change'] = [descriptions,change]
## output
    info_json = json.dumps(dic,sort_keys=True)
    print(info_json) 
    
if __name__ == '__main__':
    main()