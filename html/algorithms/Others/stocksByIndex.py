#!/usr/bin/env python
#FILE NAME: stocksInSector.py
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from core import *
import json
import sys
import mysql.connector


def jsonOutputforPHP(dic,names,info):
    counter = 0
    for i in names:
        dic[i] = info[counter]
        counter+=1
    return dic

def showAllSymbolsAssets(asset,subtype = False): ##ALL symbolsand sectors for selected ASSETS. CHECKED WHEN ADDED TO DATABASE
# asset = tables of that type
    tables = []
    conn = dbConnection()
    cursor = conn.cursor()
    if subtype == False:
        query = "SELECT symbol,name,sector from `items` WHERE type LIKE '%%%s%%' ORDER BY symbol" %asset
    else:
        query = "SELECT symbol,name,sector from `items` WHERE type LIKE '%%%s%%' AND sub_type ='%s' ORDER BY symbol" %(asset,subtype)
    cursor.execute(query)
    row = cursor.fetchone()
    while isinstance(row,tuple):
        tables.append([row[0],row[1],row[2]])
        row = cursor.fetchone()
           
    return tables


def main():
    dic = {}
## Analyze sectors. Anlyzes all stocks 
    symbols = showAllSymbolsAssets('stock')
# Looping through each sector
    for x in symbols:
        sector = x[2]
        if sector != 'N/A':
            if sector in dic:
                dic[sector].append(x[0] + ' ' + x[1])
            else:
                dic[sector] = [x[0] + ' ' + x[1]]
# dumping dic
    info_json = json.dumps(dic)
    print(info_json)

    
## analyze commodities
if __name__ == '__main__':
    main()