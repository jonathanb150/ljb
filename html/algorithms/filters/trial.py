#!/usr/bin/env python
#FILE NAME: trial.py
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
    
    delta = datetime.timedelta(days=365) #1year1
    stock.init_date = (stock.final_date - delta).strftime('%Y-%m-%d')
    stock.final_date = stock.final_date.strftime('%Y-%m-%d')

    mojon = []
    # looping through each stock
    for i in stock_symbols:
        tablename = showTableName(i)
        stock.stock_name = tablename
        stock.getData_df()

        #keeping same dates
        stock.final_date = final
        stock.init_date = (final - delta).strftime('%Y-%m-%d')
        #initializing
        negative_days = 0
        positive_days= 0
        negativepositive_days = 0
        gain =0.6
        daily_gain = 1
        gain_days = 0
        daily_gains = 0
        daily_losses = 0
        negative_days =0
        data = stock.all_data.copy()
        data['positive_diff'] = 0
        data['negative_diff'] = 0
        data['total_range'] = 0
        data['daily_change'] = 0
        counter=0
        for x in stock.all_data.index:
            try:
                opening = data['opening_price'][x]
                closing = data['closing_price'][x]
                high = data['high_price'][x]
                low = data['low_price'][x]
                data.loc[x,'positive_diff'] = ((high - opening) / opening )* 100
                data.loc[x,'negative_diff'] = ((low - opening )/ opening )* 100
                data.loc[x,'total_range'] = ((high - low) / low )* 100
                data.loc[x,'daily_change'] = ((closing - opening) / opening )* 100

                if data['positive_diff'][x] >= gain:
                    gain_days+=1
                if data['negative_diff'][x] <= -gain:
                    negative_days+=1
                if data['negative_diff'][x] <= -gain and data['positive_diff'][x] >= gain:
                    negativepositive_days+=1                    
                if data['daily_change'][x] >= daily_gain:
                    daily_gains+=1
                elif data['daily_change'][x] <= -daily_gain:
                    daily_losses+=1
                counter+=1
            except:
                mojon.append(tablename)
                stock = stockObject()
                stock.final_date = final
                stock.init_date = (stock.final_date - delta).strftime('%Y-%m-%d')

    mean1 = data['positive_diff'].mean()
    mean2 = data['negative_diff'].mean() 
    mean3 = data['daily_change'].mean() 
    mean4 = data['total_range'].mean()     
    #dic = {}
    #dic['table'] = filtered_stocks
    print(data)
    print('gain: %s percent' %gain)
    print('gain days: %s' %gain_days)
    print('negative days: %s' %negative_days)
    print('negative and positive days: %s' %negativepositive_days)
    print('daily gain: %s percent' %daily_gain)
    print('gain days: %s' %daily_gains)
    print('loss days: %s' %daily_losses)
    print('positive mean: %s' %mean1)
    print('negative mean: %s' %mean2)
    print('daily change mean: %s' %mean3)
    print('total range mean: %s' %mean4)

# dumping dic
    #info_json = json.dumps(dic)
    #print(info_json)

    
## analyze commodities
if __name__ == '__main__':
    main()