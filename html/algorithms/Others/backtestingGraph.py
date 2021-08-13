#!usr/bin/env python3
#FILENAME: backtestingGraph.py
#INPUT: ticker of company , and final date
#OUTPUT: graph of company

import sys
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from core import *
from stockObject import stockObject
import plotly.plotly as py
import urllib.request
import plotly
import plotly.graph_objs as go
import json
from datetime import datetime
from datetime import timedelta

def main():
    #ticker = 'https://stooq.com/ --> access that website to see everything that is available
    apiTicker= sys.argv[1]
    tableName = getTableName(apiTicker)
    final_date = datetime.strptime(sys.argv[2],'%Y-%m-%d')
    delta = timedelta(days=3650)
    # making graph
    stock = stockObject()
    stock.stock_name = tableName
    stock.init_date = final_date - delta
    stock.final_date = final_date
    stock.getData_df()
    #plotting
    description = getStockDescription(tableName)
    makeplot(stock.all_data.index.values,stock.all_data['closing_price'],description)  


def makeplot(dates,prices,description):
    trace1 = go.Scattergl(x = dates , y = prices)
    
    data = [trace1]
    
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
                    dict(label='3y',
                         step='all'),

                    ]),
                         ),
            type='date',domain=[0, 1]
        ),
        yaxis=dict(
            domain=[0, 1],
            anchor = 'y1'

        ))

    fname = '/var/www/ljb.solutions/html/graphs/backtestingGraph.html'
    fig = dict(data=data, layout=layout)
    fig['layout']['title'] = '%s Daily Chart' %description
    plotly.offline.plot(fig, filename = fname)


def getStockDescription(tableName):
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT name from `items` WHERE tableName='%s'" %tableName
    cursor.execute(query)
    row = cursor.fetchone()
    description = row[0]
    
    return description

def getTableName(apiTicker):
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT tableName from `items` WHERE apiTicker='%s'" %apiTicker 
    cursor.execute(query)
    row = cursor.fetchone()
    tableName= row[0]

    return tableName

def todaysDate():
    date = datetime.datetime.today()
    return date

if __name__ == '__main__':
	main()
 