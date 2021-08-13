#!/usr/bin/env python
#FILE NAME: statisticalAnalysis.py
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from core import *
from stockObject import stockObject
import pandas as pd
import math
import json
import sys
import mysql.connector
import datetime
from datetime import timedelta
import numpy as np
import json
import sys
import xlsxwriter

#get tableName from database
def getTableName(asset):
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT `tableName` FROM `items` WHERE symbol='%s'" %(asset)
    cursor.execute(query)
    row = cursor.fetchone()
    return row[0]

# gets only positive or negative numbers from data
def stats_condition(data,condition):
    newdata = [0]

    if condition == 'positive':
        for x in data:
            if x>0:
                newdata.append(x)
    elif condition =='negative':
        for x in data:
            if x<0:
                newdata.append(x)
    else:
        return('Condition not valid')
    if len(newdata)>1:
        newdata = newdata[1:]
    stats = [np.median(newdata),np.average(newdata),np.std(newdata)]
    return stats

# counts positive and negative numbers
def data_counter(data):
    positive = 0
    negative = 0
    for x in data:
        if x>0:
            positive+=1
        elif x<0:
            negative+=1
    return [positive,negative]

def main():
    stock = stockObject()
    stock.stock_name = getTableName(sys.argv[1]) # string of stock symbol
    stock.init_date= sys.argv[2]# initial date
    stock.final_date= sys.argv[3]# final date

    dailychange = []
    weeklychange = []
    monthlychange= []
    yearlychange= []
    
    #date of change
    dailydates = []
    weeklydates = []
    monthlydates = []
    yearlydates = []

    stock.getData_df()

#getting all dates 
    dates = stock.all_data.index.values
    dt = timedelta(days=7)
    initdate= dates[0]
    finaldate = initdate+dt
    delta = (dates[-1]-finaldate)
    i = 0
#looping to get weekly,monthly and yearly values
    while delta>=dt:
        mask = (stock.all_data.index>=initdate) & (stock.all_data.index<=finaldate)
        values = stock.all_data.loc[mask]
        for x in range(len(values)-1):
            dailychange.append(((values['closing_price'].iloc[x+1] - values['closing_price'].iloc[x])/values['closing_price'].iloc[x+1]) *100)
            dailydates.append(values.index[x+1])
        weeklydates.append(initdate)
        weeklychange.append(((values['closing_price'].iloc[-1] - values['closing_price'].iloc[0])/values['closing_price'].iloc[0]) *100)
        if i==0: #saving  this value for monthly/yearly calculation
            initvalue1 = values['closing_price'].iloc[0]
            initvalue1_date = values.index[0]
            initvalue2 = values['closing_price'].iloc[0]
            initvalue2_date = values.index[0]
        else:
            if np.mod(i,4)==0: # 4 weeks/1month
                monthlydates.append(initvalue1_date)
                monthlychange.append(((values['closing_price'].iloc[-1] - initvalue1)/initvalue1) *100)
                initvalue1 = values['closing_price'].iloc[-1]
                initvalue1_date = values.index[-1]
            if np.mod(i,52)==0: # 52 weeks/1year
                yearlydates.append(initvalue2_date)
                yearlychange.append(((values['closing_price'].iloc[-1] - initvalue2)/initvalue2) *100)
                initvalue2 = values['closing_price'].iloc[-1]
                initvalue2_date = values.index[-1]
        #updating
        i+=1
        initdate= initdate + dt
        finaldate = initdate + dt
        delta = (dates[-1]-finaldate)

    stats = [] 
    x = 9

    stats.append(np.median(dailychange))
    stats.append(np.average(dailychange))
    stats.append(np.std(dailychange))
    stats.extend(stats_condition(dailychange,'positive'))
    stats.extend(stats_condition(dailychange,'negative'))

    stats.append(np.median(weeklychange))
    stats.append(np.average(weeklychange))
    stats.append(np.std(weeklychange))
    stats.extend(stats_condition(weeklychange,'positive'))
    stats.extend(stats_condition(weeklychange,'negative'))

    stats.append(np.median(monthlychange))
    stats.append(np.average(monthlychange))
    stats.append(np.std(monthlychange))
    stats.extend(stats_condition(monthlychange,'positive'))
    stats.extend(stats_condition(monthlychange,'negative'))

    stats.append(np.median(yearlychange))
    stats.append(np.average(yearlychange))
    stats.append(np.std(yearlychange))
    stats.extend(stats_condition(yearlychange,'positive'))
    stats.extend(stats_condition(yearlychange,'negative'))
    
    daily_counter = data_counter(dailychange)
    weekly_counter = data_counter(weeklychange)
    monthly_counter = data_counter(monthlychange)
    yearly_counter = data_counter(yearlychange)
    daily_counter = data_counter(dailychange)

    df11_1 = pd.DataFrame({'Dates':dailydates})
    df11 = pd.DataFrame({'Daily Change':dailychange})
    df11_2 = pd.DataFrame({'Dates':weeklydates})
    df12 = pd.DataFrame({'Weekly Change':weeklychange})
    df11_3 = pd.DataFrame({'Dates':monthlydates})
    df13 = pd.DataFrame({'Monthly Change':monthlychange})
    df11_4 = pd.DataFrame({'Dates':yearlydates})
    df14 = pd.DataFrame({'Yearly Change':yearlychange})
    df1 = pd.concat([df11_1,df11,df11_2,df12,df11_3,df13,df11_4,df14],ignore_index=False, axis=1)

    df2 = pd.DataFrame({'Stats':['Median','AVG','ST DEV','POS Median','POS AVG','POS ST DEV','NEG Median','NEG AVG','NEG ST DEV']
        ,'Daily stats':stats[0:x],'Weekly Stats':stats[x:2*x],'Monthly Stats':stats[2*x:3*x],'Yearly Stats':stats[3*x:]})
    df2.set_index('Stats')

    df3 = pd.DataFrame({'Counter':['POSITIVE','NEGATIVE'],'Days':daily_counter,'Weeks':weekly_counter,
        'Months':monthly_counter,'Years':yearly_counter})


# Create a Pandas Excel writer using XlsxWriter as the engine.
    writer = pd.ExcelWriter('/var/www/ljb.solutions/html/graphs/%sstats.xlsx' % (sys.argv[1]), engine='xlsxwriter')
# Write each dataframe to a different worksheet.
    df1.to_excel(writer, sheet_name='Sheet1')
    df2.to_excel(writer, sheet_name='Sheet2')
    df3.to_excel(writer, sheet_name='Sheet3')
    stock.all_data.to_excel(writer, sheet_name='Sheet4')
# Close the Pandas Excel writer and output the Excel file.
    writer.save()
if __name__ == '__main__':
    main()
    
    
    