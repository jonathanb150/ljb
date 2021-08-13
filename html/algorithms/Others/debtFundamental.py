#!/usr/bin/env python
#FILE NAME: debtFundamental.py
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from financialObject import financialObject
from stockObject import stockObject
from core import *
import pandas as pd
import json
import sys
import mysql.connector
import datetime


def fundamentalRatio(fundamental1,fundamental2): # get ratio between fundamental1 and fundamental2
    if len(fundamental1)==len(fundamental2):
        ratio = (fundamental1/fundamental2)*100
    else:
        sys.exit('fundamentals not same size')
           
    return ratio


def fundamentalsData(tables,initDate,finalDate):
    stockFundamentals = stockObject()
    fundamentalsData = []


    for i in range(len(tables)):
        stockFundamentals.stock_name = tables[i]
        stockFundamentals.final_date = finalDate
        stockFundamentals.init_date = initDate
        stockFundamentals.getData_df()
        fundamentalsData.append(stockFundamentals.all_data)

    return fundamentalsData
        
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


def main():

    try:
        initDate= (sys.argv[1])
        finalDate = (sys.argv[2])
    except:
        finalDate= todaysDate()
        initDate = str(int(finalDate.split('-',1)[0])-3) + '-' + (finalDate.split('-',1)[1])# 4 years ago

    table_name = ['TCMDO','GDP']

    data = fundamentalsData(table_name,initDate,finalDate)
    ratio = fundamentalRatio(data[0],data[1])

    file = ratio.to_csv('totalDebtToGDP.csv')
    
if __name__ == '__main__':
    main()