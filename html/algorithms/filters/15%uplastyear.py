#!/usr/bin/env python
#FILE NAME: recent15%up.py
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from core import *
import json
import sys
from stockObject import stockObject
import mysql.connector
import datetime



def todaysDate():
    date = datetime.datetime.today()
    return date

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

def main():
    stock_symbols = (sys.argv[1]).split() ## table names
    filtered_stocks = [['Stocks']]
    stock = stockObject()
    if len(sys.argv)<=2:
        stock.final_date = todaysDate()
        final = todaysDate()
    else:
        stock.final_date = datetime.datetime.strptime(sys.argv[2],'%Y-%m-%d')
        final = datetime.datetime.strptime(sys.argv[2],'%Y-%m-%d')

    delta = datetime.timedelta(days=365) 
    stock.init_date = (final - delta).strftime('%Y-%m-%d')

    mojon = []
    # looping through each stock
    for i in stock_symbols:
        tablename = showTableName(i)
        stock.stock_name = tablename
        stock.getData_df()
        stock.final_date = final
        stock.init_date = (final - delta).strftime('%Y-%m-%d')
        
        try:
            minimum = stock.all_data['closing_price'][:].min()
            current = stock.all_data['closing_price'][-1]
            if current >= minimum*1.15:
                filtered_stocks.append([i])
        except:
            mojon.append(tablename)
            stock = stockObject()
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