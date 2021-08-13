#!/usr/bin/env python
#FILE NAME: fundamentalstatus_filter.py
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

def fundamentalStatus(symbol):
# asset = tables of that type
    tables = []
    conn = dbConnection()
    cursor = conn.cursor()
    try:
        query = "SELECT fundamentals_status from `items` WHERE symbol='%s'" %symbol
        cursor.execute(query)
    except:
        return 'Error No Table Name'
    row = cursor.fetchone()
    
    return row[0]

def todaysDate():
    date = datetime.datetime.today()
    return date

def main():
    stock_symbols = (sys.argv[1]).split() ## table names
    filtered_stocks = [['Stocks']]

    mojon = []
    # looping through each stock
    for i in stock_symbols:
        try:
            status = fundamentalStatus(i)
            if status == 1:
               filtered_stocks.append([i])
        except:
            mojon.append(i)
            
    dic = {}
    dic['table'] = filtered_stocks
# dumping dic
    info_json = json.dumps(dic)
    print(info_json)

    
## analyze commodities
if __name__ == '__main__':
    main()