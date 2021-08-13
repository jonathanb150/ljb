#!/usr/bin/env python
#FILE NAME: massivemarketcap_filter.py
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from core import *
from financialObject import financialObject
import json
import sys
from financialObject import financialObject
import mysql.connector
import datetime



def todaysDate():
    date = datetime.datetime.today()
    return date

def main():
    stock_symbols = (sys.argv[1]).split() ## table names
    filtered_stocks = [['Stocks']]
    stock = financialObject()
    if len(sys.argv)<=2:
        stock.finaldate = todaysDate()
        final = todaysDate()
    else:
        stock.finaldate = datetime.datetime.strptime(sys.argv[2],'%Y-%m-%d')
        final = datetime.datetime.strptime(sys.argv[2],'%Y-%m-%d')

    delta = datetime.timedelta(days=60) 
    stock.initdate = (final - delta).strftime('%Y-%m-%d')
	# looping through each stock
    for i in stock_symbols:
        stock.name = i + '_f'
        stock.getFinancials('MarketCap')

        try:
            marketcap = stock.marketcap[0]
        except:
            marketcap = 0
            
        if marketcap > 100000000000:
            filtered_stocks.append([i])

    dic = {}
    dic['table'] = filtered_stocks
# dumping dic
    info_json = json.dumps(dic)
    print(info_json)

    
## analyze commodities
if __name__ == '__main__':
    main()