#!/usr/bin/env python
#FILE NAME: historicalPrices.py
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from core import *
import pandas as pd
import math
import json
import sys
import mysql.connector
import datetime
import numpy as np
import json
import sys

def jsonOutputforPHP(dic,names,info):
    counter = 0
    for i in names:
        dic[i] = info[counter]
        counter+=1
    return dic

def desiredDate():
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
    desired_date = str(int(init_date[:4]) - 10) + '-01-01' # January first
    todays_year = int(init_date[:4])

    return desired_date,todays_year

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

    
def minDate(symbol):
    symbol = getStockTableName(symbol)
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT MIN(date) from `%s`" %symbol
    cursor.execute(query)
    row = cursor.fetchone()
    mindate = row[0]
    
    return mindate

def yeartoDateChange(symbol):
    symbol = getStockTableName(symbol)
    final = todaysDate()
    init = final[:4] + '-01-01'
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT date,close from `%s` WHERE date>='%s' AND date<='%s' ORDER by date DESC" %(symbol,init,final)
    cursor.execute(query)
    df = pd.DataFrame(cursor.fetchall())
    header = {0:'dates',1:'closing_price'}
    df.rename(columns=header,inplace=True)
    df.set_index('dates', inplace=True)
    #-------------------------------------------------
    yearly_change = (float(df['closing_price'][0]) - float(df['closing_price'][-1])) / float(df['closing_price'][-1]) * 100

    return yearly_change

def yearlyChange(symbol,init,final):
    symbol = getStockTableName(symbol)
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT date,close from `%s` WHERE date>='%s' AND date<'%s' ORDER by date DESC" %(symbol,init,final)
    cursor.execute(query)
    df = pd.DataFrame(cursor.fetchall())
    header = {0:'dates',1:'closing_price'}
    df.rename(columns=header,inplace=True)
    df.set_index('dates', inplace=True)
    #-------------------------------------------------
    yearly_change = str(round((float(df['closing_price'][0]) - float(df['closing_price'][-1])) / float(df['closing_price'][-1]) * 100,2))+'%'

    return yearly_change

def monthlyChanges(symbol,init):
    symbol = getStockTableName(symbol)
    monthly_changes = []
    init = datetime.datetime.strptime(init, '%Y-%m-%d')
    for i in range(12): # 12 months = 1 year
        if i ==7 or i == 9 or i == 11: # AUG,OCT,DEC
            timedelta = datetime.timedelta(days=31)
        elif i == 1:
            timedelta = datetime.timedelta(days=28) # FEB
        elif i % 2 == 0: #even = Jan,March,May,Jul
            timedelta = datetime.timedelta(days=31)
        else:
            timedelta = datetime.timedelta(days=30)

        final = init+timedelta # Last day of month

        conn = dbConnection()
        cursor = conn.cursor()
        query = "SELECT date,close from `%s` WHERE date>='%s' AND date<'%s' ORDER by date DESC" %(symbol,init,final)
        cursor.execute(query)
        df = pd.DataFrame(cursor.fetchall())
        header = {0:'dates',1:'closing_price'}
        df.rename(columns=header,inplace=True)
        df.set_index('dates', inplace=True)
        
        temp_change = str(round((float(df['closing_price'][0]) - float(df['closing_price'][-1])) / float(df['closing_price'][-1]) * 100,2))+'%'
        monthly_changes.append(temp_change)

        init = final

    return monthly_changes

def getStockTableName(symbol):
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT tableName from `items` WHERE symbol='%s'" %symbol  
    cursor.execute(query)
    row = cursor.fetchone()
    description = row[0]
    
    return description

def monthlyStatistics(table):
    header_statistics = ['MONTH','AVG','MEDIAN','POS AVG','NEG AVG']
    statistics = []
    temp = []
    count = 1
    for i in range(len(table[0])-1): #because 'Year'
        temp = []
        for i in range(len(table)-1): #because header
            temp.append(float(table[i+1][count][:-1]))

        positive = []
        negative = []
        for x in temp:
            if x>0:
                positive.append(x)
            else:
                negative.append(x) 
        
        month = table[0][count]
        avg = str(round(np.mean(temp),2))+'%'
        median = str(round(np.median(temp),2))+'%'
        positiveavg = str(round(np.mean(positive),2))+'%'
        negativeavg = str(round(np.mean(negative),2))+'%'

        statistics.append([month,avg,median,positiveavg,negativeavg])

        count+=1
    statistics.insert(0,header_statistics)

    return statistics


def main():
    symbol = sys.argv[1]# string of stock symbol
    monthly_changes =[ ['YEAR','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC']]
    yearly_changes = []
    yearly_changes_header = []
    min_date = minDate(symbol)
    min_date = str(min_date)
    desired_date,todays_year = desiredDate() # desired = starts at january first

    if int(min_date[:4]) < int(desired_date[:4]):
        min_date = desired_date
    else:
    	min_date = str(int(min_date[:4])+1) + '-01-01'

    years = int(todays_year)-int(min_date[:4])
    init = min_date
    for i in range(years):
        final = str(int(init[:4]) + 1) + init[4:]
        year_change =(init[:4]+'-'+final[:4])
        yearly_changes_header.append(year_change)
        yearly_changes_temp = yearlyChange(symbol,init,final)
        monthly_changes_temp = monthlyChanges(symbol,init)
        monthly_changes_temp.insert(0,year_change)
        init = final
        monthly_changes.append(monthly_changes_temp)
        yearly_changes.append(yearly_changes_temp)

    table1 = monthly_changes
    table2 = [yearly_changes_header,yearly_changes]

    monthly_stats = monthlyStatistics(table1)

    year_to_date = yeartoDateChange(symbol)
    dic = {}
    dic['table1'] = table1
    dic['table2'] = table2
    dic['table3'] = monthly_stats
    info = json.dumps(dic)

    print(info)
        
if __name__ == '__main__':
    main()
    
    
    