#!/usr/bin/env python
#FILE NAME: debtToCash.py
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from financialObject import financialObject
from stockObject import stockObject
from core import *
import pandas as pd
import numpy as np
import scipy
import json
import sys
import mysql.connector
import datetime
import plotly
import plotly.plotly as py
import plotly.graph_objs as go

#sort company by change in price in many different periods
def priceChangeSort(stock,changeInPeriods):
    change = []
    for i in changeInPeriods:
        change.append(((stock.all_data[-1]-stock.all_data[-i])/stock.all_data[-i])*100)
    return change


def sortingData(financialChanges,tables):   
    sorted_data = sorted(financialChanges, reverse = True)
    #rearrenging tables
    sorted_tables = []
    counter = 0
    for i in sorted_data:
        for x in range(len(tables)):
            if i == financialChanges[counter]:
                sorted_tables.append(tables[counter])
                counter = 0
                break
            else:
                counter+=1
    return sorted_data,sorted_tables

def jsonOutputforPHP(dic,names,info):
    counter = 0
    for i in names:
        dic[i] = info[counter]
        counter+=1
    return dic

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

def showAllSymbolsAssets(asset,subtype = False,append = False): ##ALL tables for selected ASSETS. CHECKED WHEN ADDED TO DATABASE
# asset = tables of that type, append = append _1d or _f if desired
    tables = []
    conn = dbConnection()
    cursor = conn.cursor()
    if subtype == False:
        query = "SELECT tableName from `items` WHERE type='%s'" %asset
    else:
        query = "SELECT tableName from `items` WHERE type='%s' AND sub_type ='%s'" %(asset,subtype)
    cursor.execute(query)
    row = cursor.fetchone()
    while isinstance(row,tuple):
        if append == False:
            tables.append(row[0])
        else:
            tables.append(row[0]+ append)
        row = cursor.fetchone()
           
    return tables

def showAllTableNamesAssets(asset,subtype = False,append = False): ##ALL tables for selected ASSETS. CHECKED WHEN ADDED TO DATABASE
# asset = tables of that type, append = append _1d or _f if desired
    tables = []
    conn = dbConnection()
    cursor = conn.cursor()
    if subtype == False:
        query = "SELECT tableName from `items` WHERE type='%s'" %asset
    else:
        query = "SELECT tableName from `items` WHERE type='%s' AND sub_type ='%s'" %(asset,subtype)
    cursor.execute(query)
    row = cursor.fetchone()
    while isinstance(row,tuple):
        if append == False:
            tables.append(row[0])
        else:
            tables.append(row[0]+ append)
        row = cursor.fetchone()
           
    return tables

def getStockDescription(tableName):
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT name from `items` WHERE tableName='%s'" %tableName
    cursor.execute(query)
    row = cursor.fetchone()
    description = row[0]
    
    return description


def main():

## analyze and show any fundamental
    fundamentals = 'us_stock' # fundamental type (database type)
    years = 10
    try:
        initDate= (sys.argv[2])
        finalDate = (sys.argv[3])
    except:
        finalDate= todaysDate()
        initDate = str(int(finalDate.split('-',1)[0])-10) + '-' + (finalDate.split('-',1)[1])# 50 years ago

    table_name = showAllTableNamesAssets(fundamentals)
    counter = 0
    mojon =[]
    stock = financialObject()
    for i in table_name:
        stock.name = i[:-3] + '_f'
        stock.initdate = initDate
        stock.finaldate = finalDate
        stock.getFinancials('Cash','Debt')
        try:
            if len(stock.cash) == years*4:
                if counter == 0:
                    cash = (stock.cash[:]).copy(deep=True)
                    debt = (stock.debt[:]).copy(deep=True)
                    counter+=1
                    print(cash)
                else:
                    cash = (stock.cash[:]).copy(deep=True) + cash
                    debt = (stock.debt[:]).copy(deep=True) + debt
                    print(cash)
        except:
        	mojon = i
    ratio = cash/debt
    trace = go.Scatter(x = ratio.index.values, y= ratio.values[:])
    plotly.offline.plot([trace], filename='/var/www/ljb.solutions/html/graphs/debtToCash.html')
    print(ratio) 
    print(mojon)
    
if __name__ == '__main__':
    main()