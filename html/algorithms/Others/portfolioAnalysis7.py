#!/usr/bin/env python
#FILE NAME: yearlyChanges.py
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from core import *
import pandas as pd
import math
import numpy as np
import json
import sys
import mysql.connector
import datetime
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
    symbol = symbol + '_1d'
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT MIN(date) from `%s`" %symbol
    cursor.execute(query)
    row = cursor.fetchone()
    mindate = row[0]
    
    return mindate

def yeartoDateChange(symbol):
    symbol = symbol+'_1d'
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
    symbol = symbol+'_1d'
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT date,close from `%s` WHERE date>='%s' AND date<'%s' ORDER by date DESC" %(symbol,init,final)
    cursor.execute(query)
    df = pd.DataFrame(cursor.fetchall())
    header = {0:'dates',1:'closing_price'}
    df.rename(columns=header,inplace=True)
    df.set_index('dates', inplace=True)
    #-------------------------------------------------
    yearly_change = (float(df['closing_price'][0]) - float(df['closing_price'][-1])) / float(df['closing_price'][-1]) * 100

    return yearly_change,df

def monthlyChanges(df):
    length = math.floor((len(df)/12.0)) #monthly avg entries
    monthly_changes  = []
    up_trend= [0,0,0,0,0,0,0] # 0 so it works the first time
    down_trend = [999999,999999,999999,999999,999999,999999,999999] # large number so it works the first time
    initializer = 'neutral'
    #counters for up and down trend
    x = 0 # up
    y = 0 # down
    for i in range(12): # 11 because we want to iterate through 11 + 1 months / year
        temp = (float(df['closing_price'][i*length])- float(df['closing_price'][(i+1)*length-1])) / float(df['closing_price'][(i+1)*length-1]) * 100
        monthly_changes.append(temp)
        ## we also want to calculate rallies and drops over 15%
        month_prices = df[:(i+1)*length]
        temp_min = float(month_prices.min())
        temp_max = float(month_prices.max())
        if temp_min < temp_max: # up trend
            if initializer == 'up' or initializer == 'neutral':
                initializer = 'up'
                if up_trend[x]<temp_max:
                    up_trend[x] = temp_max
            elif temp_max>1.15*down_trend[y]:
                y += 1
                initializer = 'up'
                up_trend[x] = temp_max
        else: # down trend
            if initializer == 'down' or initializer == 'neutral':
                initializer = 'down'
                if temp_min<down_trend[y]:
                    down_trend[y] = temp_min
            elif temp_min*1.15<up_trend[x]:
                x += 1
                initializer = 'down'
                down_trend[y] = temp_min
    # delete extra zeros
    if up_trend[x] == 0:
        up_trend = up_trend[:x]
    else:
        up_trend = up_trend[:x+1]

    if down_trend[y] == 999999:
        down_trend = down_trend[:y]
    else:
        down_trend = down_trend[:y+1]

    trend = [up_trend,down_trend]

    return monthly_changes,trend

def monthlyCalculations(monthly_changes):
    counter = 0
    monthly_avg = []
    monthly_median = []
    for i in range(len(monthly_changes[0])):
        temp = []
        for i in range(len(monthly_changes)):
            temp.append(monthly_changes[i][counter])

        monthly_avg.append(np.mean(temp))
        monthly_median.append(np.median(temp))
        counter+= 1
    return monthly_avg,monthly_median


def main():
    symbol = sys.argv[1]# string of stock symbol
    
    yearly_changes = []
    monthly_changes = []
    min_date = minDate(symbol)
    min_date = str(min_date)
    desired_date,todays_year = desiredDate()

    if int(min_date[:4]) < int(desired_date[:4]):
        min_date = desired_date
    else:
    	min_date = str(int(min_date[:4])+1) + '-01-01'


    years = int(todays_year)-int(min_date[:4])
    init = min_date
    for i in range(years):
        final = str(int(init[:4]) + 1) + init[4:]
        yearly_changes_temp,df = yearlyChange(symbol,init,final)
        monthly_changes_temp,temp2 = monthlyChanges(df)
        print(temp2)
        init = final
        monthly_changes.append(monthly_changes_temp)
        yearly_changes.append(yearly_changes_temp)

    year_to_date = yeartoDateChange(symbol)

    yearly_avg = np.mean(yearly_changes)
    yearly_median = np.median(yearly_changes)
    avg_monthly = monthlyCalculations(monthly_changes)
    monthly_avg,monthly_median = monthlyCalculations(monthly_changes)

    dic = {}
    dic['monthly_changes'] = monthly_changes
    dic['yearly_changes'] = yearly_changes
    dic['monthly_avgs'] = monthly_avg
    dic['monthly_medians'] = monthly_median
    dic['yearly_median'] = yearly_median
    dic['yearly_avg'] = yearly_avg

    info = json.dumps(dic)
    print(info)



        
if __name__ == '__main__':
    main()
    
    
    