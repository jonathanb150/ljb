#!/usr/bin/env python
#FILE NAME: sectorAnalysis.py
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


#sort companies by change in price in the last x years
def priceChangeSort(tables_name,years):
    daysInYear = 252
    financialData =[]
    counter = 0
    tables_name1 = list(tables_name) # copying list
    for i in tables_name1:
        conn = dbConnection()
        cursor = conn.cursor()
        query = "SELECT date,close FROM %s ORDER BY date DESC" %i
        try:
            cursor.execute(query)
        except mysql.connector.ProgrammingError as err:
            tables_name.pop(counter)
        else:
            row = cursor.fetchone()
            if row is None: # in case there is no price info
                info = np.asarray(0)
            else:
                info = np.array(range(len(row)))
            while isinstance(row,tuple):
                arr = np.array(list(row))
                info = np.vstack([info,arr])
                row = cursor.fetchone()
                if info.shape[0]>2000:
                    info = pd.DataFrame(info[1:,:])
                    del info[0]
                    info = pd.to_numeric(info[1])
                    financialData_temp =[]
                    for w in years:
                        days = scipy.floor(daysInYear*w)
                        change = ((scipy.sum(info[0])/scipy.sum((info[days]))-1))*100
                        financialData_temp.append(change)
                    financialData.append(financialData_temp)
                    counter+=1
                    break
    ## not enough info for desired stock, delete from table_name
            if isinstance(info,np.ndarray) or info.shape[0]<1999:   
                tables_name.pop(counter)
    counter = 0
    for i in tables_name:
        financialData[counter].append(i)
        counter+= 1                 
    return financialData,tables_name

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

def getStockDescription(tableName):
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT name from `items` WHERE tableName='%s'" %tableName
    cursor.execute(query)
    row = cursor.fetchone()
    description = row[0]
    
    return description

def main():
    dic = {}
    categories = ['1Q','2Q','1Y','3Y']
## Analyze sectors. Anlyzes all SP500 sectors indexes

    index_names = ['RWR','XLB','XLI','XLY','XLP','XLE',
                   'XLF','XLU','XLV','XLK','IYT']
# Change in the last 1 and 3 years and 1,2 quarters
    sorted_data,sorted_tables = priceChangeSort(index_names,[0.25,0.5,1,3])
    descriptions = []

    for i in index_names:
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