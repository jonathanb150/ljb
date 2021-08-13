#!/usr/bin/env python
#FILE NAME: econAnalysis.py
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

def linearizedPlot(listWithStocksInfo,listWithDescriptions,listWithFundamentalsIndex):
    dics = []
    temp1 = []
    temp2 = []
    data_linear = []
    data_normal = []
    description = listWithDescriptions
    # QUICK FIX for yaxis names
    if len(description)== 1:
        description.append(0)
        description.append(0)
        description.append(0)
    elif len(description)== 2:
        description.append(0)
        description.append(0)
    elif len(description)== 3:
        description.append(0)
    ###################################
    
    for i in range(len(listWithStocksInfo)):
        temp1.append(True)
        temp2.append(False)
    visibility1 = temp1 + temp2
    visibility2 = temp2 + temp1
    ## creating dics
    dic1 ={}
    dic2 = {}
    dic1['method'] = 'update'
    dic2['method'] = 'update'
    dic1['label'] = 'Regular'
    dic2['label'] = 'Linearized'
    dic1['args'] = [{'visible': visibility2}]
    dic2['args'] = [{'visible': visibility1}]
    dics.append(dic1)
    dics.append(dic2)


    for i in range(len(listWithStocksInfo)):
        tempInfo = listWithStocksInfo[i]['closing_price']
        x_values = tempInfo.index.values.tolist()
        y_values1 = tempInfo/tempInfo[0]
        y_values2 = tempInfo
        if i==0:
            axis = 'y1'
            color = '#000000'
        elif i==1:
            axis = 'y2'
            color = '#c62100'
        elif i==2:
            axis = 'y3'
            color = '#30b71f'
        elif i==3:
            axis = 'y4'
            color = '#0008ff'
        elif i==4:
            axis = 'y5'
            color = '#45006b'
        elif i==5:
            axis = 'y6'
            color = '#63665f'

        trace = go.Scatter(x = x_values, y = y_values1,name = '%s' %description[i], yaxis = axis, line = dict(color = color), visible = False)
        data_linear.append(trace)
        trace = go.Scatter(x = x_values, y = y_values2,name = '%s' %description[i], yaxis = axis, line = dict(color = color), visible = True)
        data_normal.append(trace)

    data = []
    for i in (data_linear):
        data.append(i)
    for i in (data_normal):
        data.append(i)

    layout = dict(
        title='Asset Class/Fundamental Performance',
        xaxis=dict(
            domain = [0.1,0.8],
            showgrid=False,
            rangeslider=dict(
                    yaxis=dict(
                            rangemode='auto'),
                visible = False),
            type='date'),
        yaxis=dict(
                title='%s'%description[0],
                titlefont=dict(
                color='#000000'),
                position=0,
                side = 'left'),
        yaxis2=dict(
                overlaying='y',
                showgrid = False,
                title='%s'%description[1],
                titlefont=dict(
                color='#c62100'
                ),
                side = 'right',
                position=0.8),
        yaxis3=dict(
                title='%s'%description[2],
                showgrid = False,
                titlefont=dict(
                color='#30b71f'
                ),
                overlaying='y',
                side = 'right',
                position=0.9),
        yaxis4=dict(
                overlaying='y',
                showgrid = False,
                title='%s'%description[3],
                titlefont=dict(
                color='#0008ff'
                ),
                side = 'left',
                position=0.1),
        legend=dict(x=-.15, y=1.1))

    updatemenus=list([
    dict(
        buttons= dics,              
        direction = 'down',
        pad = {'r': 10, 't': 10},
        showactive = True,
        x = 0.7,
        xanchor = 'left',
        y = 1.1,
        yanchor = 'top' ) ])  

    fname = '/var/www/ljb.solutions/html/graphs/correlationChart.html'
    fig = dict(data=data, layout=layout)
    fig['layout'].update(height=700)
    fig['layout']['updatemenus'] = updatemenus
    plotly.offline.plot(fig, filename = fname)
    with open(fname, 'r') as content_file:
        content = content_file.read()
    print(content)

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
    types = []
    fundamentals = []
    stock = stockObject()
    
    for i in range(len(tableNames)):
        description = getStockDescription(tableNames[i])
        descriptions.append(description)
        type_temp = getStockType(tableNames[i])
        if type_temp[-11:] == 'fundamental':
            types.append(i) # append index position of us_fundamental, doing this because fundamentals will have a separate axis
        
        stock.stock_name = tableNames[i]
        stock.init_date = initDate
        stock.final_date = finalDate
        stock.getData_df()
        fundamentals.append(stock.all_data)

    linearizedPlot(fundamentals,descriptions,types)
    

    
if __name__ == '__main__':
    main()