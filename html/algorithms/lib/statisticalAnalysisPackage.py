#!/usr/bin/env python
#FILE NAME: statisticalAnalysisPackage.py
import sys, os
sys.path.append("/lib")
from core import dbConnection
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

def getTableName_hourly(asset):
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT `hourly_data_table` FROM `items` WHERE symbol='%s'" %(asset)
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

#getting stock data
def getData_fromdb(assetname,finaldate,initdate, hourly = False):
    import pandas as pd
    if hourly == True:
        tablename = getTableName_hourly(assetname)
    else:
        tablename = getTableName(assetname)
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT date,open,high,low,close,volume FROM `%s` where date>='%s' AND date<='%s'ORDER BY date DESC" %(tablename,initdate,finaldate)   
    cursor.execute(query)
    pd = pd.DataFrame(cursor.fetchall())
    if len(pd)!=0:
        pd.columns = ['dates','open','high','low','closing_price','volume']
        pd = pd.set_index('dates')
        pd.open=pd.open.astype(float)
        pd.high=pd.high.astype(float)
        pd.low=pd.low.astype(float)
        pd.closing_price=pd.closing_price.astype(float)
    
    return pd

#getting close stock data
def getValueData_fromdb(assetname,finaldate,initdate):
    import pandas as pd
    tablename = getTableName(assetname)
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT date,value FROM `%s` where date>='%s' AND date<='%s'ORDER BY date DESC" %(tablename,initdate,finaldate)   
    cursor.execute(query)
    pd = pd.DataFrame(cursor.fetchall())
    pd.columns = ['dates','closing_price']
    pd = pd.set_index('dates')
    pd.closing_price=pd.closing_price.astype(float)
    
    return pd


# returns historical yearly change of specified stock
def historicalYearlyChanges(stock_symbol, initdate= 0, finaldate = 0):
    if finaldate ==0:
        finaldate = datetime.datetime.strptime(todaysDate(),'%Y-%m-%d')
        dt = datetime.timedelta(days=365*10)
        initdate = finaldate - dt
    else:
        finaldate = datetime.datetime.strptime(finaldate,'%Y-%m-%d')
        initdate = datetime.datetime.strptime(initdate,'%Y-%m-%d')
        
    final = finaldate
    init = finaldate - datetime.timedelta(days=365)# minus one year

    price = getData_fromdb(stock_symbol,finaldate,initdate)
    min_date = datetime.datetime.combine(price.index.values[-1], datetime.time.min)
    
    if min_date> initdate:  # checking if there is data x years from finaldate
        initdate = min_date

    change = []
    header = []
    while init+datetime.timedelta(days=5)>=initdate:
        price = getData_fromdb(stock_symbol,final,init)
        change.append((price['closing_price'][0]-price['closing_price'][-1])/price['closing_price'][-1]*100)
        
        header.append(datetime.datetime.strftime(init,'%Y-%m-%d'))
        final = init
        init = final - datetime.timedelta(days=365) # plus one year
    
    return [header,change]

def historicalMonthlyChanges(stock_symbol, initdate= 0, finaldate = 0):
    if finaldate ==0:
        finaldate = datetime.datetime.strptime(todaysDate(),'%Y-%m-%d')
        dt = datetime.timedelta(days=365*10)
        initdate = finaldate - dt
    else:
        finaldate = datetime.datetime.strptime(finaldate,'%Y-%m-%d')
        initdate = datetime.datetime.strptime(initdate,'%Y-%m-%d')
        
    final = finaldate
    init = final - datetime.timedelta(days=30) # minus one month

    price = getData_fromdb(stock_symbol,finaldate,initdate)
    min_date = datetime.datetime.combine(price.index.values[-1], datetime.time.min)
    
    if min_date> initdate:  # checking there is data x years from finaldate
        initdate = min_date

    change = []
    header = []
    while init+datetime.timedelta(days=5)>=initdate:
        price = getData_fromdb(stock_symbol,final,init)
        change.append((price['closing_price'][0]-price['closing_price'][-1])/price['closing_price'][-1]*100)
        
        header.append(datetime.datetime.strftime(init,'%Y-%m-%d'))
        final = init
        init = final - datetime.timedelta(days=30) # minus one month
    
    return [header,change]

def historicalWeeklyChanges(stock_symbol, initdate= 0, finaldate = 0):
    if finaldate ==0:
        finaldate = datetime.datetime.strptime(todaysDate(),'%Y-%m-%d')
        dt = datetime.timedelta(days=365*10)
        initdate = finaldate - dt
    else:
        finaldate = datetime.datetime.strptime(finaldate,'%Y-%m-%d')
        initdate = datetime.datetime.strptime(initdate,'%Y-%m-%d')
        
    final = finaldate
    init = final - datetime.timedelta(days=7) # minus 7 days

    price = getData_fromdb(stock_symbol,finaldate,initdate)
    min_date = datetime.datetime.combine(price.index.values[-1], datetime.time.min)
    
    if min_date> initdate:  # checking there is data x years from finaldate
        initdate = min_date

    change = []
    header = []
    while init+datetime.timedelta(days=5)>=initdate:

        price = getData_fromdb(stock_symbol,final,init)
        change.append((price['closing_price'][0]-price['closing_price'][-1])/price['closing_price'][-1]*100)
        
        header.append(datetime.datetime.strftime(init,'%Y-%m-%d'))
        final = init
        init = final - datetime.timedelta(days=7) # minus 7 days

    return [header,change]

def historicalDailyChanges(stock_symbol, initdate= 0, finaldate = 0):
    if finaldate ==0:
        finaldate = datetime.datetime.strptime(todaysDate(),'%Y-%m-%d')
        dt = datetime.timedelta(days=365*10)
        initdate = finaldate - dt
    else:
        finaldate = datetime.datetime.strptime(finaldate,'%Y-%m-%d') 
        initdate = datetime.datetime.strptime(initdate,'%Y-%m-%d')

    price_data = getData_fromdb(stock_symbol,finaldate,initdate)

    change = []
    header = []
    counter = 0
    err = 0
    for price in price_data['closing_price']:
        try:
            change.append((price_data['closing_price'][counter]-price_data['closing_price'][counter+1])/price_data['closing_price'][counter+1]*100)
            header.append(datetime.datetime.strftime(price_data.index.values[counter],'%Y-%m-%d'))
        except:
            err=1
        counter+=1
    return [header,change]

def historical1HourChanges(stock_symbol, initdate= 0, finaldate = 0):
    if finaldate ==0:
        finaldate = datetime.datetime.strptime(todaysDate(hourly = True),'%Y-%m-%d %H:%M:%S')
        dt = datetime.timedelta(hours=24*7)
        initdate = finaldate - dt
    else:
        finaldate = datetime.datetime.strptime(finaldate,'%Y-%m-%d %H:%M:%S') 
        initdate = datetime.datetime.strptime(initdate,'%Y-%m-%d %H:%M:%S')

    price_data = getData_fromdb(stock_symbol,finaldate,initdate, hourly = True)

    change = []
    header = []
    counter = 0
    err = 0
    for price in price_data['closing_price']:
        try:
            change.append((price_data['closing_price'][counter]-price_data['closing_price'][counter+1])/price_data['closing_price'][counter+1]*100)
            header.append(datetime.datetime.strftime(price_data.index.values[counter],'%Y-%m-%d %H:%M:%S'))
        except:
            err=1
        counter+=1
    return [header,change]

def historical4HoursChanges(stock_symbol, initdate= 0, finaldate = 0):
    if finaldate ==0:
        finaldate = datetime.datetime.strptime(todaysDate(),'%Y-%m-%d %H:%M:%S')
        dt = datetime.timedelta(hours=24*7)
        initdate = finaldate - dt
    else:
        finaldate = datetime.datetime.strptime(finaldate,'%Y-%m-%d %H:%M:%S')
        initdate = datetime.datetime.strptime(initdate,'%Y-%m-%d %H:%M:%S')
        
    final = finaldate
    init = final - datetime.timedelta(hours=4) # minus 4 hours

    price = getData_fromdb(stock_symbol,finaldate,initdate, hourly = True)

    if type(price.index.values[-1])!= type(init):
        min_date = datetime.datetime.combine(datetime.datetime.utcfromtimestamp((price.index.values[-1].tolist()/1e9)), datetime.time.min)
    else:
        min_date = datetime.datetime.combine(price.index.values[-1], datetime.time.min)
    
    if min_date> initdate:  # checking there is data x years from finaldate
        initdate = min_date

    change = []
    header = []
    while init+datetime.timedelta(hours=2)>=initdate:

        price = getData_fromdb(stock_symbol,final,init, hourly = True)

        if len(price)>=2:
            change.append((price['closing_price'][0]-price['closing_price'][-1])/price['closing_price'][-1]*100)
            header.append(datetime.datetime.strftime(init,'%Y-%m-%d %H:%M:%S'))

        final = init
        init = final - datetime.timedelta(hours = 4) # minus 4 hours

    return [header,change]

def historical8HoursChanges(stock_symbol, initdate= 0, finaldate = 0):
    if finaldate ==0:
        finaldate = datetime.datetime.strptime(todaysDate(),'%Y-%m-%d %H:%M:%S')
        dt = datetime.timedelta(hours=24*7)
        initdate = finaldate - dt
    else:
        finaldate = datetime.datetime.strptime(finaldate,'%Y-%m-%d %H:%M:%S')
        initdate = datetime.datetime.strptime(initdate,'%Y-%m-%d %H:%M:%S')
        
    final = finaldate
    init = final - datetime.timedelta(hours=8) # minus 8 hours

    price = getData_fromdb(stock_symbol,finaldate,initdate, hourly = True)
    if type(price.index.values[-1])!= type(init):
        min_date = datetime.datetime.combine(datetime.datetime.utcfromtimestamp((price.index.values[-1].tolist()/1e9)), datetime.time.min)
    else:
        min_date = datetime.datetime.combine(price.index.values[-1], datetime.time.min)
    
    if min_date> initdate:  # checking there is data x years from finaldate
        initdate = min_date

    change = []
    header = []
    while init+datetime.timedelta(hours=2)>=initdate:

        price = getData_fromdb(stock_symbol,final,init, hourly = True)
        if len(price)>=2:
            change.append((price['closing_price'][0]-price['closing_price'][-1])/price['closing_price'][-1]*100)
            header.append(datetime.datetime.strftime(init,'%Y-%m-%d %H:%M:%S'))

        final = init
        init = final - datetime.timedelta(hours = 8) # minus 8 hours

    return [header,change]

def historical12HoursChanges(stock_symbol, initdate= 0, finaldate = 0):
    if finaldate ==0:
        finaldate = datetime.datetime.strptime(todaysDate(),'%Y-%m-%d %H:%M:%S')
        dt = datetime.timedelta(hours=24*7)
        initdate = finaldate - dt
    else:
        finaldate = datetime.datetime.strptime(finaldate,'%Y-%m-%d %H:%M:%S')
        initdate = datetime.datetime.strptime(initdate,'%Y-%m-%d %H:%M:%S')
        
    final = finaldate
    init = final - datetime.timedelta(hours=12) # minus 12 hours

    price = getData_fromdb(stock_symbol,finaldate,initdate, hourly = True)
    if type(price.index.values[-1])!= type(init):
        min_date = datetime.datetime.combine(datetime.datetime.utcfromtimestamp((price.index.values[-1].tolist()/1e9)), datetime.time.min)
    else:
        min_date = datetime.datetime.combine(price.index.values[-1], datetime.time.min)
    
    if min_date> initdate:  # checking there is data x years from finaldate
        initdate = min_date

    change = []
    header = []
    while init+datetime.timedelta(hours=2)>=initdate:

        price = getData_fromdb(stock_symbol,final,init, hourly = True)
        if len(price)>=2:
            change.append((price['closing_price'][0]-price['closing_price'][-1])/price['closing_price'][-1]*100)
            header.append(datetime.datetime.strftime(init,'%Y-%m-%d %H:%M:%S'))

        final = init
        init = final - datetime.timedelta(hours = 12) # minus 12 hours

    return [header,change]

def yearlyVolatily(stock_symbol, initdate= 0, finaldate = 0):
    if finaldate ==0:
        finaldate = datetime.datetime.strptime(todaysDate(),'%Y-%m-%d')
        dt = datetime.timedelta(days=365*10)
        initdate = finaldate - dt
    else:
        finaldate = datetime.datetime.strptime(finaldate,'%Y-%m-%d')
        initdate = datetime.datetime.strptime(initdate,'%Y-%m-%d')
        
    final = finaldate
    init = finaldate - datetime.timedelta(days=365)
    
    price = getData_fromdb(stock_symbol,finaldate,initdate)
    min_date = datetime.datetime.combine(price.index.values[-1], datetime.time.min)
    
    if min_date> initdate:  # checking there is data x years from finaldate
        initdate = min_date
        
    volatility = []
    header = []
    
    while init+datetime.timedelta(days=5)>=initdate:
        price = getData_fromdb(stock_symbol,final,init)
        max = price['closing_price'].max()
        min = price['closing_price'].min()
        
        if price['closing_price'].idxmax()> price['closing_price'].idxmin():
            volatility.append((max-min)/min*100)
        else:
            volatility.append((min-max)/max*100)

        header.append(datetime.datetime.strftime(init,'%Y-%m-%d'))
        final = init
        init = final - datetime.timedelta(days=365) # minus one year
    
    return [header,volatility]

# list with daily,weekly,monthly,yearly changes
def historical_changes_hourly(stock_symbol,initdate,finaldate):

    hourlychange = historical1HourChanges(stock_symbol, initdate, finaldate)
    hours4change = historical4HoursChanges(stock_symbol, initdate, finaldate)
    hours8change= historical8HoursChanges(stock_symbol, initdate, finaldate)
    hours12change= historical12HoursChanges(stock_symbol, initdate, finaldate)

    return [hourlychange, hours4change, hours8change, hours12change]
# list with daily,weekly,monthly,yearly changes
def historical_changes(stock_symbol,initdate,finaldate):

    dailychange = historicalDailyChanges(stock_symbol, initdate, finaldate)
    weeklychange = historicalWeeklyChanges(stock_symbol, initdate, finaldate)
    monthlychange= historicalMonthlyChanges(stock_symbol, initdate, finaldate)
    yearlychange= historicalYearlyChanges(stock_symbol, initdate, finaldate)

    return [dailychange, weeklychange, monthlychange, yearlychange]

def stat_info(change_list):
    if len(change_list)==0:
        change_list= [0,0]
    stats = [] 
    # period info = [median, avg, std, pos_med, pos_avg, pos_std, neg_med, neg_avg, neg_std, pos_days, neg_days] 
    stats.append(np.median(change_list))
    stats.append(np.average(change_list))
    stats.append(np.std(change_list))
    stats.extend(stats_condition(change_list,'positive'))
    stats.extend(stats_condition(change_list,'negative'))
    stats.extend(data_counter(change_list))

    return stats
    

def statistics(all_data):
    daily_stats = []
    weekly_stats = []
    monthly_stats = []
    yearly_stats = []
# period info = [median, avg, std, pos_med, pos_avg, pos_std, neg_med, neg_avg, neg_std, pos_days, neg_days]
    daily_stats.append(stat_info(all_data[0][1]))
    weekly_stats.append(stat_info(all_data[1][1]))
    monthly_stats.append(stat_info(all_data[2][1]))
    yearly_stats.append(stat_info(all_data[3][1]))
        
    return daily_stats,weekly_stats,monthly_stats,yearly_stats
    
def list_todf(all_data):
    df_list = []

    for data in all_data:
        if not (data[1]):
            df1 = 'mojon'
        if not (data[0]):
            df1 = 'mojon'
        else:
            df1= pd.DataFrame(data[1],data[0])
            df1.columns=['close']
            df_list.append(df1)

    return df_list

def todaysDate(hourly = False):
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

    if hourly == True:
        init_date = init_date + ' 00:00:00'
    return init_date

# Get stock closing price between desired init and final date
def stockClosingPrice(stock_name, initdate= 0, finaldate=0):
    # stock price info
    if initdate==0:
        finaldate = todaysDate()
        initdate = datetime.datetime.strptime(todaysDate(),'%Y-%m-%d') - datetime.timedelta(days=365*30)
    price_data = getData_fromdb(stock_name,finaldate,initdate)
    closing_price = price_data['closing_price'].to_frame()
    closing_price.rename(columns={'closing_price':stock_name}, inplace=True)
    return closing_price

# Get all stock info between desired init and final date
def stockInfo(stock_name, initdate= 0, finaldate=0):
    # stock price info
    if initdate==0:
        finaldate = todaysDate()
        initdate = datetime.datetime.strptime(todaysDate(),'%Y-%m-%d') - datetime.timedelta(days=365*30)
    price_data = getData_fromdb(stock_name,finaldate,initdate)
    return price_data

# Get all financial data of stock
def allFinancials(stock_symbol):
    # stock financial info
    finaldate = todaysDate()
    initdate = datetime.datetime.strptime(todaysDate(),'%Y-%m-%d') - datetime.timedelta(days=365*30)
    stock_fundamental = financialObject()
    stock_fundamental.name = stock_symbol + '_f'
    stock_fundamental.finaldate = finaldate
    stock_fundamental.initdate = initdate
    stock_fundamental.getFinancials('Revenue','Cash','Equity','Debt','Profit','EPS','Fcf','Assets','DividendYieldQuarterly','MarketCapQuarterly')
    dic = {'Revenue': stock_fundamental.revenue,'Cash': stock_fundamental.cash,
                      'Equity': stock_fundamental.equity,'Debt':stock_fundamental.debt,
                      'Profit':stock_fundamental.profit,'EPS':stock_fundamental.eps,
                      'Fcf':stock_fundamental.fcf,'Assets':stock_fundamental.assets,
                      'DividendYieldQuarterly':stock_fundamental.dividendyieldquarterly,
                      'MarketCapQuarterly':stock_fundamental.marketcapquarterly}
    return dic

# historical fundamental data
def fundamentalsData(fundamentals_header = ['UMCSENT','cpaltt01usm659n','GDPQOQ','UNRATE','FEDFUNDS','DGS3MO','DGS1','DGS10']):
# fundamental info
    finaldate = todaysDate()
    initdate = datetime.datetime.strptime(todaysDate(),'%Y-%m-%d') - datetime.timedelta(days=365*50)
    dic = {}

    for i, x in enumerate(fundamentals_header):
        data = getValueData_fromdb(x,finaldate,initdate)
        data.columns = [x]
        dic[x]= data #adding data to dic
        
    return dic

# Puts together two dataframes with different time intervals
def cleandata_sametimeframe(data1,data2, days=20):
    if type(data1)==pd.Series:
        data1 = data1.to_frame()
    if type(data2)==pd.Series:
        data2 = data2.to_frame()
    errors = []
    header = np.append(data1.columns.values, data2.columns.values)  
    df = pd.DataFrame(columns= header)   

    df['dates'] = data1.index.values
    df.set_index('dates',inplace=True)
    for pos,info in enumerate(data1.index.values):
        for pos2,info2 in enumerate(data2.index.values):
            try:
                lambda1 =  info - data2.index[pos2]
            except:
                errors.append((pos2,info2))
                data2.drop(data2.index.values[pos2],inplace=True)
                break
            else:
                if lambda1> datetime.timedelta(days=-days) and lambda1<datetime.timedelta(days=days):
                    df.loc[df.index[pos],df.columns.values] = np.append(data1.iloc[pos].values,data2[data2.columns.values[0]].iloc[pos2])
                    data2.drop(data2.index.values[pos2],inplace=True)
                    break

    df.dropna(inplace=True, how='any')
    return df

# compares dataframe columns with first column
def compareColumns(dataframe):
    columns = dataframe.columns.values[1:]
    column1= dataframe.columns.values[0]
    
    dates = dataframe.index.values
    df = pd.DataFrame({'dates': dates})
    for x in columns:
        new_column = []
        for i in range(len(dataframe)):
            temp1 = dataframe[column1][i] 
            temp2 = dataframe[x][i]
            if temp1> temp2:
                new_column.append(1)
            elif temp1<temp2:
                new_column.append(0)
        
        name = '%s vs %s' %(column1,x)
        df[name] = new_column
    df.set_index('dates',inplace=True)
    
    return df

def yoychange(dataframe):
    if type(dataframe)==pd.Series:
        dataframe = dataframe.to_frame()
        
    dataframe.dropna(inplace=True,how='any')   
    
    delta = datetime.timedelta(days=365)
    
    initdate= dataframe.index.values[0]
    finaldate= dataframe.index.values[-1]
    
    changes = []
    dates = []
    
    while initdate-finaldate>=delta:
        dates.append(dataframe.index.values[0])
        
        temp = initdate - delta
        current_value = dataframe.iloc[0].values[0]
        
        temp2 = dataframe[dataframe.index.values<=temp]
        yearago_value= temp2.iloc[0].values[0]
        
        if yearago_value == 0:
            changes.append(0)
        else:
            changes.append((current_value - yearago_value) / (yearago_value) *100)
        
        dataframe = dataframe[1:] # updating dataframe
        initdate = dataframe.index.values[0] # updating date
        
    
    info = pd.DataFrame({'dates': dates ,'YoY Change': changes})
    info.set_index('dates',inplace=True)
    
    return info

def momchange(dataframe):
    if type(dataframe)==pd.Series:
        dataframe = dataframe.to_frame()
        
    dataframe.dropna(inplace=True,how='any')   
    
    delta = datetime.timedelta(days=30)
    
    initdate= dataframe.index.values[0]
    finaldate= dataframe.index.values[-1]
    
    changes = []
    dates = []
    
    while initdate-finaldate>=delta:
        dates.append(dataframe.index.values[0])
        
        temp = initdate - delta
        current_value = dataframe.iloc[0].values[0]
        
        temp2 = dataframe[dataframe.index.values<=temp]
        momago_value= temp2.iloc[0].values[0]
        
        if momago_value == 0:
            changes.append(0)
        else:
            changes.append((current_value - momago_value) / (momago_value) *100)
        
        dataframe = dataframe[1:] # updating dataframe
        initdate = dataframe.index.values[0] # updating date
        
    
    info = pd.DataFrame({'dates': dates ,'MoM Change': changes})
    info.set_index('dates',inplace=True)
    
    return info

def dailychange(dataframe):
    if type(dataframe)==pd.Series:
        dataframe = dataframe.to_frame()
        
    dataframe.dropna(inplace=True,how='any')   
    
    delta = datetime.timedelta(days=1)
    
    initdate= dataframe.index.values[0]
    finaldate= dataframe.index.values[-1]
    
    changes = []
    dates = []
    
    while initdate-finaldate>=delta:
        dates.append(dataframe.index.values[0])
        
        temp = initdate - delta
        current_value = dataframe.iloc[0].values[0]
        
        temp2 = dataframe[dataframe.index.values<=temp]
        previous_value= temp2.iloc[0].values[0]
        if previous_value == 0:
            changes.append(0)
        else:
            changes.append((current_value - previous_value) / (previous_value) *100)
        
        dataframe = dataframe[1:] # updating dataframe
        initdate = dataframe.index.values[0] # updating date
        
    
    info = pd.DataFrame({'dates': dates ,'Daily Change': changes})
    info.set_index('dates',inplace=True)
    
    return info

def cagr_percent(dataframe):
    if type(dataframe)==pd.Series:
        dataframe = dataframe.to_frame()
        
    dataframe.dropna(inplace=True,how='any')   

    ending_value = dataframe.iloc[0].values[0]
    beggining_value = dataframe.iloc[-1].values[-1]
    N = len(dataframe)
    cagr = ((ending_value/beggining_value)**(1/N))-1
    
    return cagr*100

def normalizedata(data):
    if type(data)==pd.Series or type(data)==pd.core.frame.DataFrame:
        if type(data)==pd.Series:
            data = data.to_frame()
        maximum = np.max(data.values)
        minimum = np.min(data.values)

        diff = maximum - minimum
    
        info = []
        dates = []
        for x in range(len(data)):
            info.append((data.values[x][0]-minimum)/diff)
            dates.append(data.index.values[x])
        
        info = pd.DataFrame({'dates':dates,data.columns.values[0]:info})
        info.set_index('dates',inplace=True)
        
        return info
    
    else:
        maximum = np.max(data.values)
        minimum = np.min(data.values)
        diff = maximum - minimum

        info = []

        for x in range(len(data)):
            info.append((data.values[x][0]-minimum)/diff)
        return info
# gets change previous x days    
def change_previousdays(dataframe, ndays = 180):
    if type(dataframe)==pd.Series:
        dataframe = dataframe.to_frame()
        
    dataframe.dropna(inplace=True,how='any')   
    
    delta = datetime.timedelta(days= ndays)
    
    initdate= dataframe.index.values[0]
    finaldate= dataframe.index.values[-1]
    
    changes = []
    dates = []
    
    while initdate-finaldate>=delta:
        dates.append(dataframe.index.values[0])
        
        temp = initdate - delta
        current_value = dataframe.iloc[0].values[0]
        
        temp2 = dataframe[dataframe.index.values<=temp]
        momago_value= temp2.iloc[0].values[0]
        
        if momago_value == 0:
            changes.append(0)
        else:
            changes.append((current_value - momago_value) / (momago_value) *100)
        
        dataframe = dataframe[1:] # updating dataframe
        initdate = dataframe.index.values[0] # updating date
        
    
    info = pd.DataFrame({'dates': dates ,'6M Change %s' %dataframe.columns.values[0] : changes})
    info.set_index('dates',inplace=True)
    
    return info

# gets change into the future x days  
def change_futuredays(dataframe, ndays = 180):
    if type(dataframe)==pd.Series:
        dataframe = dataframe.to_frame()
        
    dataframe.dropna(inplace=True,how='any')   
    
    delta = datetime.timedelta(days= ndays)
    
    initdate= dataframe.index.values[-1]
    finaldate= dataframe.index.values[0]
    
    changes = []
    dates = []
    
    while initdate+delta<=finaldate:
        dates.append(dataframe.index.values[-1])
        
        temp = initdate + delta
        current_value = dataframe.iloc[-1].values[0]
        
        temp2 = dataframe[dataframe.index.values<=temp]
        final_value= temp2.iloc[0].values[0]
        
        if current_value == 0:
            changes.append(0)
        else:
            changes.append((final_value - current_value) / (current_value) *100)
        
        dataframe = dataframe[:-1] # updating dataframe
        initdate = dataframe.index.values[-1] # updating date
        
    dates.reverse()
    changes.reverse()
    info = pd.DataFrame({'dates': dates ,'Next 6M Change %s' %dataframe.columns.values[0] : changes})
    info.set_index('dates',inplace=True)
    
    return info
    
def avg_yearlydata(dataframe):
    if type(dataframe)==pd.Series:
        dataframe = dataframe.to_frame()
        
    dataframe.dropna(inplace=True,how='any')   
    
    delta = datetime.timedelta(days=365)
    
    initdate= dataframe.index.values[0]
    finaldate= dataframe.index.values[-1]
    
    changes = []
    dates = []
    
    while initdate-finaldate>=delta:
        dates.append(dataframe.index.values[0])
        
        temp = initdate - delta
        current_values = dataframe[(dataframe.index.values<=init) & (dataframe.index.values>temp)]
        current_avg = np.average(current_values.values)
        
        changes.append(current_avg)
        
        dataframe = dataframe[dataframe.index.values<=temp]# updating dataframe
        initdate = temp # updating date
        
    
    info = pd.DataFrame({'dates': dates ,'AVG Value': changes})
    info.set_index('dates',inplace=True)
    
    return info