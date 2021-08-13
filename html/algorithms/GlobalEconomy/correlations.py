#!/usr/bin/env python
#FILE NAME: correlations.py
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from financialObject import financialObject
from stockObject import stockObject
from core import *
import pandas as pd
import numpy as np
import scipy
from scipy.stats import linregress
import json
import sys
import mysql.connector
import datetime
import plotly
import plotly.plotly as py
import plotly.graph_objs as go
import shutil


def jsonOutputforPHP(dic,names,info):
    counter = 0
    for i in names:
        dic[i] = info[counter]
        counter+=1
    return dic


def getStockDescription(tableName):
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT name from `items` WHERE tableName='%s'" %tableName
    cursor.execute(query)
    row = cursor.fetchone()
    description = row[0]
    
    return description

def getStockType(tableName):
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT type from `items` WHERE tableName='%s'" %tableName
    cursor.execute(query)
    row = cursor.fetchone()
    description = row[0]
    
    return description

def linearRegression(listWithStocksInfo):
    x = listWithStocksInfo[0]['closing_price'].tolist()
    y = listWithStocksInfo[1]['closing_price'].tolist()
    if len(x) > len(y):
        x = x[:len(y)]
        y= y[:len(y)]
    elif len(x) < len(y):
        x = x[:len(x)]
        y= y[:len(x)]  

    info = linregress(x,y)
    p = np.polyfit(x,y,1)
    return p,info

def linearizedPlot(listWithStocksInfo,listWithDescriptions):
    i = 0
    description = listWithDescriptions[1] + ' vs ' + listWithDescriptions[0]
    x_values = listWithStocksInfo[i]['closing_price'].tolist()
    y_values = listWithStocksInfo[i+1]['closing_price'].tolist()

    trace1 = go.Scatter(x = x_values, y = y_values,mode = 'markers')
    p= linearRegression(listWithStocksInfo)
    info = [['Slope','Correlation'],[p[1][0],p[1][2]]]

    maxVal = (listWithStocksInfo[i]['closing_price']).max()
    minVal = (listWithStocksInfo[i]['closing_price']).min()
    x_values = np.linspace(minVal,maxVal,100)

    y_values = np.polyval(p[0],x_values)
    trace2 = go.Scatter(x = x_values, y = y_values)


    data = [trace1,trace2]
          
    layout = dict(
        title= description,
        showlegend = False,
        xaxis=dict(
            domain = [0.1,0.85],
            title = listWithDescriptions[0]+' stock price in $',
            showgrid=False,
            rangeslider=dict(
                    yaxis=dict(
                            rangemode='auto'),
                visible = False),
            ),
                    yaxis=dict(
                            title= listWithDescriptions[1]+' stock price in $',
                            side = 'left',
                            showgrid=False),
            )

    fname = '/var/www/ljb.solutions/html/graphs/CorrelationLinearRegression.html'
    fig = dict(data=data, layout=layout)
    fig['layout'].update(height=700)
    plotly.offline.plot(fig, filename = fname)
    with open(fname, 'r') as content_file:
        content = content_file.read()

    dic = {'graph':content, 'table':info}
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

def main():

## analyze and show any fundamental
    tableNames = sys.argv[1] # i.e --> 'AAPL_1d GOOGL_1d' --> space in between stocks
    try:
        initDate= sys.argv[2] # init date str
        finalDate= sys.argv[3] # final date  str
    except:
        finalDate= todaysDate() # init date str
        initDate= str(int(finalDate[:4])-4) + finalDate[4:] # final date  str

    tableNames = tableNames.split()
    descriptions =[]
    fundamentals = []
    stock = stockObject()
    
    for i in range(len(tableNames)):
        description = getStockDescription(tableNames[i])
        descriptions.append(description)
  
        stock.stock_name = tableNames[i]
        stock.init_date = initDate
        stock.final_date = finalDate
        stock.getData_df()
        fundamentals.append(stock.all_data)

    dic = linearizedPlot(fundamentals,descriptions)
    info_json = json.dumps(dic,sort_keys=True)
    print(info_json)     

    
if __name__ == '__main__':
    main()