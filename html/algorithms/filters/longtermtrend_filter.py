#!/usr/bin/env python
#FILE NAME: longtermtrend_filter.py
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from core import *
import json
import sys
from stockObject import stockObject
import mysql.connector
import datetime


def showTableName(symbol):
# asset = tables of that type
    tables = []
    conn = dbConnection()
    cursor = conn.cursor()
    try:
        query = "SELECT tableName from `items` WHERE symbol='%s'" %symbol
        cursor.execute(query)
    except:
        return 'Error No Table Name'
    row = cursor.fetchone()
  
    return row[0]

def showAllStockSymbols():
# asset = tables of that type
    tables = []
    conn = dbConnection()
    cursor = conn.cursor()
    try:
        query = "SELECT symbol from `items` WHERE type='us_stock'"
        cursor.execute(query)
    except:
        return 'Error No Table Name'
    row = cursor.fetchall()
    for i in row:
    	tables.append(i[0])
    
    return tables

def todaysDate():
    date = datetime.datetime.today()
    return date

def main():
    #stock_symbols = showAllStockSymbols()
    stock_symbols = (sys.argv[1]).split() ## table names
    filtered_stocks = [['Stocks']]
    stock = stockObject()
    if len(sys.argv)<=2:
        final = todaysDate()
        stock.final_date = todaysDate()
    else:
        final = datetime.datetime.strptime(sys.argv[2],'%Y-%m-%d')
        stock.final_date = datetime.datetime.strptime(sys.argv[2],'%Y-%m-%d')

    delta = datetime.timedelta(days=1075) #3years
    stock.init_date = (final - delta).strftime('%Y-%m-%d')
    stock.final_date = final.strftime('%Y-%m-%d')

    mojon = []
    # looping through each stock
    for i in stock_symbols:
        tablename = showTableName(i)
        stock.stock_name = tablename
        try:
            stock.getData_df()
            # keeping same dates
            stock.final_date = final
            stock.init_date = (final - delta).strftime('%Y-%m-%d')

            previous_price = stock.all_data['closing_price'][0]
            today = stock.all_data['closing_price'][-1]
            change = (today - previous_price)/previous_price *100
            if change > -3:
                filtered_stocks.append([i])
        except:
            mojon.append(tablename)
            stock = stockObject()
            if len(sys.argv)<=2:
                stock.final_date = final
            else:
                stock.final_date = final
            stock.init_date = (final - delta).strftime('%Y-%m-%d')
            
    dic = {}
    dic['table'] = filtered_stocks
# dumping dic
    info_json = json.dumps(dic)
    print(info_json)

    
## analyze commodities
if __name__ == '__main__':
    main()