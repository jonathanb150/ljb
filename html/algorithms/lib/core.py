#!/usr/bin/env python
# File Name:  core.py
# Core Package: contain all basic functions needed for analyzing data
#Importing all the libraries we need
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
import numpy as np
import scipy
import sys, os
from mysql.connector import errorcode
import mysql.connector
import pandas as pd
import math
from datetime import datetime, timedelta

# input: closing and opening price
def high_and_low(closing_price,opening_price,sma,period):
# deleting the "period" prices
    closing_price = closing_price[period-1:]
    opening_price = opening_price[period-1:]
    length = len(closing_price)
    highs = np.zeros(length,dtype=float)
    lows = np.zeros(length,dtype=float)
    for x in range(length)[1:length-1]:
        if opening_price[x] > opening_price[x-1] and opening_price[x] > opening_price[x+1] and opening_price[x] > sma[x]:
            highs[x] = opening_price[x]
            lows[x] = 0
        elif closing_price[x] < closing_price[x-1] and closing_price[x] < closing_price[x+1] and closing_price[x] < sma[x]:
            lows[x] = closing_price[x]
            highs[x] = 0
        else:
            lows[x] = 0
            highs[x] = 0
    return (highs,lows)

# Analyze percentage change of GDP,Interest Rate or Inflation quaterly and yearly
def analyzeData(ID,*ID2):
	yData = getData(ID)
	qData = getData(ID2)

	years = yData.size
	quarters = qData.size

	yearly_data = np.array([])
	quarterly_data = np.array([])

	for x in range(years):
		temp = yData[x+1]/yData[x]
		if temp > 1:
			temp = (temp-1)*100
		else:
			temp = (1-temp)*100
		yearly_data = np.concatenate(yearly_data,temp)
	for x in range(quarters):
		temp2 = qData[x+1]/qData[x]*100
		if temp2 > 1:
			temp2 = (temp2-1)*100
		else:
			temp = (1-temp2)*100
		quarterly_data = np.concatenate(quarterly_data,temp)
	return (quarterly_data,yearly_data)

#analyze GDP,interest rate or inflation
def analyzeMacroData(df_data):
    
    df_data['%Change'] = 0
    df_data['Outlook'] = 0
    periods = df_data.index.get.values()

    for x in range(len(periods)-1):
        temp = df_data.loc[x+1][0]/df_data.loc[x][0]
        if temp > 1:
            temp = (temp-1)*100
            df_data['Outlook'][periods[x+1]] = True
        else:
            temp = (1-temp)*100
            df_data['Outlook'][periods[x+1]] = False
            
        df_data['%change'][periods[x+1]] = temp
    return df_data

def arrSMA(arr,period):  
    sma = np.arange(len(arr)-1)
    for x in range(len(sma)):
        temp = arr[x:(x+period)]
        temp = sum(temp)/period
        sma[x] = temp;
    return sma

# add n number of values in list
def addValues(values,n):
    from math import floor
    from numpy import sum
    final_list = []
    for w in values:
        new_list = []
        for i in range(floor(len(w)/n)):
            new_list.append(sum(w[:n]))
            del w[:n]
        final_list.append(new_list)

    return final_list


def dbConnection():
    try:
        conn = mysql.connector.connect(host='localhost',user='ljb',passwd='GsnSdnrt^3475Sdnkfg#465',db='ljb')
    except:
        sys.exit('Could not connect to database')
    return conn
    
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

    