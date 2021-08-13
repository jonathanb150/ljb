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

def getStockData():
    '''
    This function gives back a dataframe with the following attributes:
    Open
    High
    Low
    Close
    Volume
    Stoch
    SMA50
    SMA100
    SMA200
    '''
    # creating stock object
    stock = stockObject()
    # Getting info from user
    stock.stock_name = getTableName(sys.argv[1]) # string of stock symbol
    # USING 1Year before Init date to calculate SMAS, stochastics etc, and then init date is updated
    stock.init_date = str(int(sys.argv[2][:4])-1)+sys.argv[2][4:]
    stock.final_date = sys.argv[3]

    stock.getData_df()
    stock.requested_data = stock.all_data[255:] # 255 is one year --> info from selected init date
    stock.init_date = sys.argv[2]

 ## SHORT TERM SMAS for Stoch
    stock.period1 = 100
    stock.period2 = 50
    stock.period3 = 20
    stock.SMAsCrossover()
    stoch,d = stock.stochastic()

 ## Long term SMAS
    stock.period1=200
    stock.period2=100
    stock.period3=50
    stock.SMAsCrossover()


    df = pd.DataFrame()
    df['Date'] = stock.requested_data.index.values
    df['Open'] = stock.requested_data['opening_price'].values
    df['High'] = stock.requested_data['high_price'].values
    df['Low'] = stock.requested_data['low_price'].values
    df['Close'] = stock.requested_data['closing_price'].values
    #print(len(df))
    #print(stoch)
    #print(len(stoch))
    #df['Stoch']= stoch 
    df['SMA50'] = stock.sma3
    df['SMA100'] = stock.sma2
    df['SMA200'] =  stock.sma1
    df['Vol'] = stock.requested_data['vol'].values

    return df

def getIndexData(index = 'SPX'):
    '''
    This function gives back a dataframe with the following attributes:
    Open
    High
    Low
    Close
    Volume
    '''
    # creating stock object
    stock = stockObject()
    # Getting info from user
    stock.stock_name = getTableName(index) # string of stock symbol
    # USING 1Year before Init date to calculate SMAS, stochastics etc, and then init date is updated
    stock.init_date = str(int(sys.argv[2][:4])-1)+sys.argv[2][4:]
    stock.final_date = sys.argv[3]

    stock.getData_df()

    data = stock.requested_data[255:] # actual data

    return data

def getStockChange(data):
    #daily change
    df = pd.DataFrame()
    df['Date'] = data['Date'][0:(len(data)-1)].values
    change = []
    for i in range(len(data['Close'])-1):
        change.append(round((data['Close'].iloc[i+1]-data['Close'].iloc[i])/data['Close'].iloc[i],6)*100)
    
    df['Daily Change'] = change

    #3month change
    dt = timedelta(days=30*3)
    temp_data = data.iloc[:]
    final = data['Date'].iloc[-2]
    change = []
    while temp_data['Date'].iloc[0]<final:
        
        final_data = temp_data[temp_data['Date']>= (temp_data['Date'].iloc[0]+dt)]
        if len(final_data)==0:
            missing = len(df) - len(change)
            arr = [0]*missing
            change = change + arr
            break
        
        change.append(round((final_data['Close'].iloc[0]-temp_data['Close'].iloc[0])/temp_data['Close'].iloc[0],3)*100)

        temp_data = temp_data[1:]


    df1 = pd.DataFrame()
    df1['3Month Change'] = change
    df = pd.concat([df,df1], ignore_index=True, axis=1)

    #6month change
    dt = timedelta(days=30*6)
    temp_data = data.iloc[:]
    final = data['Date'].iloc[-2]
    change = []
    while temp_data['Date'].iloc[0]<final:
        
        final_data = temp_data[temp_data['Date']>= (temp_data['Date'].iloc[0]+dt)]
        if len(final_data)==0:
            missing = len(df) - len(change)
            arr = [0]*missing
            change = change + arr
            break
        
        change.append(round((final_data['Close'].iloc[0]-temp_data['Close'].iloc[0])/temp_data['Close'].iloc[0],3)*100)

        temp_data = temp_data[1:]

    df1 = pd.DataFrame()
    df1['6Month Change'] = change
    df = pd.concat([df,df1], ignore_index=True, axis=1)

    #1year change
    dt = timedelta(days=30*12)
    temp_data = data.iloc[:]
    final = data['Date'].iloc[-2]
    change = []
    while temp_data['Date'].iloc[0]<final:
        
        final_data = temp_data[temp_data['Date']>= (temp_data['Date'].iloc[0]+dt)]
        if len(final_data)==0:
            missing = len(df) - len(change)
            arr = [0]*missing
            change = change + arr
            break
        
        change.append(round((final_data['Close'].iloc[0]-temp_data['Close'].iloc[0])/temp_data['Close'].iloc[0],3)*100)

        temp_data = temp_data[1:]

    df1 = pd.DataFrame()
    df1['1Year Change'] = change
    df = pd.concat([df,df1], ignore_index=True, axis=1)
    
    df.columns = ['Date','Daily Change', '3Month Change', '6Month Change', '1Year Change']
    df.set_index('Date')

    return df

def aboveValue(data1,data2):
    assert len(data1)==len(data2)
    
    data = []
    for i in range(len(data1)):
        if data1[i]>data2[i]:
            data.append(1)
        else:
            data.append(0)

    return data

def pos_neg_value(data):
    pos = []
    neg = []

    for i in range(len(data)):
        if data.iloc[i]>0:
            pos.append(data.iloc[i])
        else:
            neg.append(data.iloc[i])    

    pos_mean = np.mean(pos)
    pos_std = np.std(pos)  

    neg_mean = np.mean(neg)

    neg_std = np.std(neg)  


    return ((pos_mean+pos_std),(neg_mean-neg_std))

def stoch_vol_classifier(data):
    classifier = []

    mean = np.mean(data)
    std = np.std(data)

    max_val = mean+1*std
    min_val = mean-1*std



    for i in range(len(data)):
        if data[i]>=max_val:
            classifier.append(1)
        elif data[i]<=min_val:
            classifier.append(2)
        else:
            classifier.append(3)        

    return classifier

def processData(data_stock,stock_change):
    #Adding % changes
    data_stock.drop(data_stock.tail(1).index,inplace=True)
    data_stock['daily_change'] = stock_change['Daily Change']
    data_stock['3m_change'] = stock_change['3Month Change']
    data_stock['6m_change'] = stock_change['6Month Change']
    data_stock['1y_change'] = stock_change['1Year Change']

    #Targets for classification. Calculating if change has been positive
    data_stock['3m_target'] = (data_stock['3m_change']>0).astype(int)
    data_stock['6m_target'] = (data_stock['6m_change']>0).astype(int)
    data_stock['1y_target'] = (data_stock['1y_change']>0).astype(int)
    data_stock['all_target'] = np.array(data_stock['3m_target'])*np.array(data_stock['6m_target'])*np.array(data_stock['1y_target'])


    #comparing with SMAS
    data_stock['above_sma50'] = aboveValue(data_stock['Close'],data_stock['SMA50'])
    data_stock['above_sma100'] = aboveValue(data_stock['Close'],data_stock['SMA100'])
    data_stock['above_sma200'] = aboveValue(data_stock['Close'],data_stock['SMA200'])

    #Comparing with opening price (positive vs negative volatility)
    low_open = data_stock['Open'] - data_stock['Low']
    high_open = data_stock['High'] - data_stock['Open']
    data_stock['pos_volatility'] = aboveValue(high_open,low_open)

    # processing stochastic/Volume (1 --> high, 2--> low, 3--> middle)
    #data_stock['stoch_class'] = stoch_vol_classifier(data_stock['Stoch'])
    data_stock['vol_class'] = stoch_vol_classifier(data_stock['Vol'])

    #positive and negative  changes
    pos_threshold,neg_threshold = pos_neg_value(data_stock['daily_change'])
    data_stock['pos_out'] = (data_stock['daily_change'] >= pos_threshold).astype(int)
    data_stock['neg_out'] = (data_stock['daily_change'] <= neg_threshold).astype(int)

    # stock vs index
    #data_stock['above_index'] = aboveValue(stock_change,index_change['SMA50'])
    
    #interaction terms
    data_stock['pos_out_aboveSMA50'] = np.array(data_stock['above_sma50']) * np.array(data_stock['pos_out'])
    data_stock['pos_out_aboveSMA100'] = np.array(data_stock['above_sma100']) * np.array(data_stock['pos_out'])
    data_stock['pos_out_aboveSMA200'] = np.array(data_stock['above_sma200']) * np.array(data_stock['pos_out'])

    data_stock['neg_out_aboveSMA50'] = np.array(data_stock['above_sma50']) * np.array(data_stock['neg_out'])
    data_stock['neg_out_aboveSMA100'] = np.array(data_stock['above_sma100']) * np.array(data_stock['neg_out'])
    data_stock['neg_out_aboveSMA200'] = np.array(data_stock['above_sma200']) * np.array(data_stock['neg_out'])

    return data_stock



def main():

    data_stock = getStockData()
    #data_index = getIndexData()

    stock_change = getStockChange(data_stock)
    #index_change = getStockChange(data_index)

    processed_data = processData(data_stock,stock_change)
    processed_data.to_csv('/var/www/ljb.solutions/html/algorithms/Others/'+ str(sys.argv[1]) +'stock_data.csv')

    
if __name__ == '__main__':
    main()