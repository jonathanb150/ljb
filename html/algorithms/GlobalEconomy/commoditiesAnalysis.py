#!/usr/bin/env python
#FILE NAME: commoditiesAnalysis.py
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



def todaysDate():
    date = [datetime.today().year,datetime.today().month,
            datetime.today().day]
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

#sort companies by change in price in the last x years
def priceChangeSort(tables_name,years):
    daysInYear = 252
    financialData =[]
    final = todaysDate()
    init = str(int(final[:4])-4) + final[4:]
    for i in tables_name:
        financialData_temp = []
        stock = stockObject(stock_name=i, init_date= init, final_date= final)
        stock.getData_df()
        for w in years:
            days = scipy.floor(daysInYear*w)
            change = (((stock.all_data['closing_price'][-1])/(stock.all_data['closing_price'][int(-days)]))-1)*100
            financialData_temp.append(change)

        financialData_temp.append(i)
        financialData.append(financialData_temp)

    return financialData

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

def showAllSymbolsAssets(asset,subtype = False,append = False): ##ALL tables for selected ASSETS. CHECKED WHEN ADDED TO DATABASE
# asset = tables of that type, append = append _1d or _f if desired
    tables = []
    conn = dbConnection()
    cursor = conn.cursor()
    if subtype == False:
        query = "SELECT symbol from `items` WHERE type='%s'" %asset
    else:
        query = "SELECT symbol from `items` WHERE type='%s' AND sub_type ='%s'" %(asset,subtype)
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

def getStockDescription(symbol):
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT name from `items` WHERE symbol='%s'" %symbol  
    cursor.execute(query)
    row = cursor.fetchone()
    description = row[0]
    
    return description

def showAllSymbolsAssets(asset,subtype = False,append = False): ##ALL tables for selected ASSETS. CHECKED WHEN ADDED TO DATABASE
# asset = tables of that type, append = append _1d or _f if desired
    tables = []
    conn = dbConnection()
    cursor = conn.cursor()
    if subtype == False:
        query = "SELECT symbol from `items` WHERE type='%s'" %asset
    else:
        query = "SELECT symbol from `items` WHERE type='%s' AND sub_type ='%s'" %(asset,subtype)
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

def main():
    dic ={}
    categories = ['1Q','2Q','1Y','3Y']
##------------------------------------------------------------------------------    
## Analyze commodities and sort them
    commodities_names = showAllTableNamesAssets('commodity')
# Change in the last 1 and 3 years
    sorted_data = priceChangeSort(commodities_names,[0.25,0.5,1,3])

    descriptions = []
    for i in commodities_names:
        descriptions.append(getStockDescription(i))

# storing in dic
    names = descriptions
    info = sorted_data
    dic = jsonOutputforPHP(dic,names,info)
    info_json = json.dumps(dic)
    print(info_json)

## analyze commodities
if __name__ == '__main__':
    main()