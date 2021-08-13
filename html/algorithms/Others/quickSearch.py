#!usr/bin/env python3
#FILENAME: quickSearch.py
#INPUT: ticker of company
#OUTPUT: table and graph of company

import sys
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from core import *
from stockObject import stockObject
import plotly.plotly as py
import urllib.request
import plotly
import plotly.graph_objs as go
import json
import datetime

def main():
    #ticker = 'https://stooq.com/ --> access that website to see everything that is available
    apiTicker= sys.argv[1]
    tableName = getTableName(apiTicker)
    changes = ['Change']

    # Getting info from user
    final_date = todaysDate()
    deltas = [datetime.timedelta(days=1095),datetime.timedelta(days=365),datetime.timedelta(days=182),datetime.timedelta(days=91),datetime.timedelta(days=30),datetime.timedelta(days=7)]
    counter = 0
    for i in deltas:
        counter +=1
        init_date = (final_date-i).strftime('%Y-%m-%d')
        change,info = priceChange(tableName,init_date,final_date.strftime('%Y-%m-%d'))
        changes.append(change)
        if counter == 1:
            stock = stockObject()
            stock.stock_name = tableName
            stock.init_date = init_date
            stock.final_date = final_date
            stock.getData_df()
            #plotting
            description = getStockDescription(tableName)
            makeplot(stock.all_data.index.values,stock.all_data['closing_price'],description)  
    #one day change
    change = round(((info.iloc[0]/info.iloc[1])-1)*100,2)
    changes.append(change)



    # printing output
    table = [['Time','3 Years','1 Year', '6 Months','3 Months', '1 Month', '1 Week', '1 Day'],changes]    
    dic = {}
    dic['table'] = table
    info = json.dumps(dic)
    print(info)

def priceChange(table_name,init_date,final_date):
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT date,close FROM `%s` WHERE date<='%s' AND date>='%s' ORDER BY date DESC" %(table_name,final_date,init_date)
    try:
        cursor.execute(query)
    except mysql.connector.ProgrammingError as err:
        tables_name.pop(counter)
    else:
        row = cursor.fetchone()

        if row[1]==None or row[1]=='': # in case there is no price info
            info = [0] # no info
        else:
            info = np.array(range(len(row)))
        while isinstance(row,tuple):
            if len(info)>1:
                arr = np.array(list(row))
                info = np.vstack([info,arr])
                row = cursor.fetchone()
            else:
                break
     ## not enough info for desired stock, delete from table_name               
        if len(info)<=1:#no info
            change = 0
        else:
            info = pd.DataFrame(info[1:,:])
            del info[0]
            info = pd.to_numeric(info[1])
            change = round(((info.iloc[0]/info.iloc[-1])-1)*100,2)
             
    return change,info

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

    fname = '/var/www/ljb.solutions/html/graphs/quickSearch.html'
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
 