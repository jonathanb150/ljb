#!/usr/bin/env/ python
#File name: statistical_backtesting.py
# This function returns; [buy: Yes/No , Sell: Yes/No , Current Price: XX , Upside Target: % , Downside Target: % ]

import sys,os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from statisticalAnalysisPackage import historical_changes,list_todf,statistics,getData_fromdb,data_counter
from core import *
import datetime
from datetime import timedelta
import pandas as pd
import numpy as np
import scipy
import json
import datetime
def buy_constraints(table1,table2):
# table2 = ['Daily POS AVG Change', 'Daily NEG AVG Change', 'Weekly POS AVG Change','Weekly NEG AVG Change', 
# 'Monthly POS AVG Change', 'Monthly NEG AVG Change','Yearly POS AVG Change', 'Yearly NEG AVG Change',
# 'POS/NEG Days','Daily Outliers POS/NEG']
# table1 = ['Daily Change','Weekly Change','Monthly Change','POS/NEG Days']
    points = 0
    if table1[0] < table2[3]:# daily drop
        points+=2
    elif table1[0]< table2[1]: 
        points+=1
    if table1[1]< table2[3]:# weekly drop
        points+=1
    if table1[2]< table2[5]:# monthly drop
        points+=1
    if table1[3]< table2[8]:# more negative days
        points+=1

    if points>4:
        results = 'Strong Buy'
    elif points>=3:
        results = 'Buy'
    else:
        results ='Neutral'

    return results

def sell_constraints(table1,table2):
    points=0
    if table1[0] > table2[2]:# daily rally
        points+=2
    elif table1[0] > table2[0]: 
        points+=1
    if table1[1] > table2[2]:# weekly drop
        points+=1
    if table1[2] > table2[4]:# monthly rally
        points+=1
    if table1[3] > table2[8]:# more positive days
        points+=1

    if points>4:
        results = 'Strong Sell'
    elif points>=3:
        results = 'Sell'
    else:
        results ='Neutral'

    return results

def table_1(all_data):
    daily = all_data[0]['close'].iloc[0]
    weekly = all_data[1]['close'].iloc[0]
    monthly = all_data[2]['close'].iloc[0]
    counter = data_counter(all_data[0]['close']) # pos and neg days
    counter2 = data_counter(all_data[1]['close']) # pos and neg weeks

    return [round(daily,2),round(weekly,2),round(monthly,2),round((counter[0]/counter[1]),4),round(counter2[0]/counter2[1],4)]

def readtxt():
    with open('/var/www/ljb.solutions/html/graphs/data.txt') as json_file:  
        data = json.load(json_file)

    return data['table']

def main():
    stock_symbol = sys.argv[1]
    final_date = sys.argv[2] 
    dt = datetime.timedelta(days=90)
    init_date = datetime.datetime.strptime(final_date,'%Y-%m-%d') - dt
    init_date = datetime.datetime.strftime(init_date,'%Y-%m-%d')
    
    all_data = historical_changes(stock_symbol,init_date,final_date)

# period info = [median, avg, std, pos_med, pos_avg, pos_std, neg_med, neg_avg, neg_std, pos_days, neg_days]
    daily_stats,weekly_stats,monthly_stats,yearly_stats = statistics(all_data)

# processing all data
    all_data = list_todf(all_data)

    table2 = readtxt()
    table1 = [['Daily Change','Weekly Change','Monthly Change','POS/NEG Days','POS/NEG Weeks'], table_1(all_data)]

    buy_results = buy_constraints(table1[1], table2[1])
    sell_results = sell_constraints(table1[1], table2[1])

    prices = getData_fromdb(stock_symbol,final_date,init_date)


    dic ={}
    dic['table1'] = table1
    dic['table2'] = [['Price','BUY','SELL'],[round(prices['closing_price'][0],2),buy_results,sell_results]]
    info_json = json.dumps(dic)
    print(info_json)

if __name__ == '__main__':
	main()