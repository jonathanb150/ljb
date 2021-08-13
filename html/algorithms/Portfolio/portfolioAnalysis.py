#!/usr/bin/env python
#FILE NAME: portfolioAnalysis.py
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from core import *
import pandas as pd
import numpy as np
import scipy
import json
import sys
import mysql.connector
import datetime
import plotly as plotly
import plotly.graph_objs as go
import plotly.plotly as py
import json
import sys
import copy

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

def makingPlot(descriptions,capitalDist, plotName = 'portfolioDistribution'):
    
    fig =   {
            'data': [{'labels': descriptions,
              'values': capitalDist,
              'type': 'pie'}],
        'layout': {'title': 'Portfolio Distribution'}
        }
    fname = '/var/www/ljb.solutions/html/graphs/' + plotName + '.html'
    plotly.offline.plot(fig, filename= fname )
    with open(fname, 'r') as content_file:
        content = content_file.read()
    return content
    
def getStockDescription(symbol):
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT name from `items` WHERE symbol='%s'" %symbol
    cursor.execute(query)
    row = cursor.fetchone()
    description = row[0]
    
    return description

def getStockAssestType(symbol):
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT type from `items` WHERE symbol='%s'" %symbol
    cursor.execute(query)
    row = cursor.fetchone()
    description = row[0]
    
    return description

def standardDistribution():
    stocks = 50
    bonds = 25
    cash = 15
    others = 10
    values = [stocks,bonds,cash,others]
    names = ['stocks','bonds', 'cash', 'others']
    standardDistribution = [names,values]
    
    return standardDistribution

def main():
    symbols = (sys.argv[1]).split()# string of stock symbols
    descriptions = []
    for i in symbols:
        temp = getStockDescription(i)
        descriptions.append(temp)
    
    capitalDist = (sys.argv[2]).split() # string of capital distribution
    counter = 0
    for i in capitalDist:
        capitalDist[counter] = float(i)
        counter+=1
        
    totalCapital = float(sys.argv[3]) # total capital str
    cash = totalCapital - scipy.sum(capitalDist)
    
    descriptions.append('Cash')
    capitalDist.append(cash)
  ## PLOTTING STOCKS-----------------------------------------------------------  
    graph1 = makingPlot(descriptions,capitalDist)
  ## PLOTTING ASSET DISTRIBUTION-----------------------------------------------
    counter = 0
    bonds = 0
    stocks = 0
    currencies = 0
    for i in symbols: ## stocks,bonds,cash,others
        tempType = getStockAssestType(i)
        if tempType[-5:] == 'stock':
            stocks = stocks + capitalDist[counter]
        elif tempType == 'bond':
            bonds = bonds + capitalDist[counter]
        elif tempType == 'currency':
            currencies = currencies + capitalDist[counter]
        counter+=1
    others = totalCapital - cash - bonds - stocks
    assetDist = [stocks,bonds,cash,others]

    plotName = 'AssetDistribution'
    graph2 = makingPlot(['Stocks','Bonds','Cash','Others'],assetDist,plotName)
  ## NEW Distribution ---------------------------------------------------------
    currentDist = np.array(assetDist)/totalCapital*100
    currentDist = currentDist.tolist()
    standardDist = standardDistribution()


  ## Portfolio difference from recommendedDistribution ------------------------  
    dic = {}
    dic['standardDist'] = standardDist
    dic['currentDist'] = [['Stocks','Bonds','Cash','Others'],currentDist] 
    dic['graph1'] = graph1
    dic['graph2'] = graph2

    info_json = json.dumps(dic)
    print(info_json)
        
if __name__ == '__main__':
    main()
    
    
    