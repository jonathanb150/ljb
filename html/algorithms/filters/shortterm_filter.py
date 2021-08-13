#!/usr/bin/env python
#FILE NAME: shortterm_filter.py
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

def todaysDate():
    date = datetime.datetime.today()
    return date

#FILTERS STOCKS CLOSE TO EITHER 50 or 100 SMA
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
    delta_real = datetime.timedelta(days=60) 
    real_date = (final - delta_real)
    stock.init_date = (real_date - delta).strftime('%Y-%m-%d')

    mojon = []
	# looping through each stock
    for i in stock_symbols:
        tablename = showTableName(i)
        stock.stock_name = tablename
        stock.getData_df()
        stock.period1 = 200
        stock.period2 = 100
        stock.period3 = 50
        stock.requested_data = stock.all_data[255:] # 255 is one year
        stock.init_date = real_date
        stock.SMAsCrossover()
        try:
            sma50 = stock.sma1[-1]
            sma100 = stock.sma2[-1]
            current = stock.requested_data['closing_price'][-1] 
            stock.init_date = (real_date - delta).strftime('%Y-%m-%d')       
            if (current < sma100*1.03 and current > sma100*0.97) or (current < sma50*1.03 and current > sma50*0.97):
                filtered_stocks.append([i])
        except:
            mojon.append(tablename)
            stock = stockObject()
            stock.final_date = final
            stock.init_date = (real_date - delta).strftime('%Y-%m-%d')

    dic = {}
    dic['table'] = filtered_stocks
# dumping dic
    info_json = json.dumps(dic)
    print(info_json)

    
## analyze commodities
if __name__ == '__main__':
    main()