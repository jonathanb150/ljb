#!/usr/bin/env python
# File Name: StockObject.py

# Importing everything we need
import numpy as np
from core import dbConnection
import scipy
import sys, os
import _mysql
import mysql.connector
import pandas as pd
import time
from datetime import datetime, timedelta
import sys


#StockObject class is used to store stock,index, or any kind of financial data that follows
## the following format : Dates,Closing,High,Low,Opening,Vol or Dates,Closing
class stockObject:
    def __init__(self,stock_name = None, all_data=None, init_date= None, final_date= None,
        period1=100, period2=50, period3=20, sma1=None, sma2= None, sma3 = None, requested_data = None, SMAsintersection = None):
        self.period1 = period1
        self.period2 = period2
        self.period3 = period3
        self.init_date = init_date
        self.final_date = final_date
        self.stock_name = stock_name
        self.all_data = None
        self.requested_data = None
        self.sma1 = sma1
        self.sma2 = sma2
        self.sma3 = sma3
        self.SMAs_intersectionIndex_up = None
        self.SMAs_intersectionIndex_down = None
       
## Finds Crossover of 3 SMAs and store the corresponding indexes
# Pre-condition:stockObject with data and periods
# Output: intersections between SMAs as attributes
    def SMAsCrossover(self):
        if isinstance(self.requested_data , type(None)):
            sys.exit(['No Data associated with stock'])
        else:
#Getting Simple Moving Averagesd
            #plt.figure()
            sma1 = self.SMA(self.period1)
            #graphMe((self.requested_data).index.values,sma1,color='orange',ls = '--',label='SMA %d' %self.period1)
            sma2 = self.SMA(self.period2)
            #graphMe((self.requested_data).index.values,sma2,color='blue',ls='--',label='SMA %d' %self.period2)
            sma3 = self.SMA(self.period3)
            #graphMe((self.requested_data).index.values,sma3,color = 'magenta', ls='--',label='SMA %d' %self.period3)
            self.sma1=sma1
            self.sma2=sma2
            self.sma3=sma3
            index_up,index_down = self.__SMAsIntersections()
            self.SMAs_intersectionIndex_up = index_up
            self.SMAs_intersectionIndex_down = index_down

#finding stochastic of desired data
# Pre condition: stockObject with data, dates and a period to evaluate. MUST CALCULATE all(1 extra year) and requested data as pre-req
    def stochastic(self,**kwargs):
        period = kwargs.get('period',14)
        stochastic = np.array([])
        if isinstance(self.requested_data, type(None)):
            return 'Requested data must be calculated first'
        stoch_data = self.all_data[255-period:]
        for x in range(len(self.requested_data)):
            highest_high = (stoch_data['high_price'][x:period+x]).max()
            lowest_low = (stoch_data['low_price'][x:period+x]).min()
            current_close = stoch_data['closing_price'][x+period-1]
    #Getting stochastic value
            K = np.array([100*(current_close - lowest_low)/(highest_high - lowest_low)])
            stochastic = np.concatenate([stochastic,K])
        stoch = arrSMA(stochastic,3)
        D = arrSMA(stoch,5)
        return (stoch,D)

#Getting Moving Average
# Calculates moving Average of desired data. REQUESTED DATA MUST BE CALCULATED AS PRE-REQ
    def SMA(self,period):  
        if isinstance(self.all_data, type(None)):
            sys.exit(['No Data associated with stock'])
    #getting SMA
        elif isinstance(self.requested_data,pd.DataFrame) or isinstance(self.requested_data,pd.Series):
            sma_data = self.all_data[255-period+1:]
            sma = np.array(range(len(self.requested_data)),dtype='float64')
            if isinstance(self.requested_data,pd.DataFrame):
                for x in range(len(sma)):
                    temp = sma_data['closing_price'][x:(x+period)]
                    temp = sum(temp)/period
                    sma[x] = temp;
                return sma
            else:
                for x in range(len(sma)):
                    temp = sma_data[x:(x+period)]
                    temp = sum(temp)/period
                    sma[x] = temp;
                return sma
        else:
            sys.exit(['ERROR, data is not a DataFrame'])

#Find intersections between moving averages and plot it
# Pre condition: self.SMAs and Periods must match and exist
    def __SMAsIntersections(self):
        index_up=[]
        index_down =[]
        if isinstance(self.sma1, type(None)):
            sys.exit(['SMAs have not been calculated yet!'])
            return
        else:
            for x in range(len(self.sma1)-1):
                if self.sma1[x+1]>self.sma2[x+1] and self.sma2[x+1]>self.sma3[x+1]:
                    if self.sma1[x]<=self.sma2[x] or self.sma2[x]<=self.sma3[x] or self.sma1[x]<=self.sma3[x]:
                        index_down= np.append(index_down,[x+1])
                        
                elif self.sma1[x+1]<self.sma2[x+1] and self.sma2[x+1]<self.sma3[x+1]:
                    if self.sma1[x]>=self.sma2[x] or self.sma2[x]>=self.sma3[x] or self.sma1[x]>=self.sma3[x]:
                        index_up = np.append(index_up,[x+1])
            else:    
                index_up = np.array(index_up,dtype=np.int32)
                index_down = np.array(index_down,dtype=np.int32)
                index_up = index_up.tolist()
                index_down = index_down.tolist()
        return index_down,index_up
        
 # Gets Data from SQL Database in DataFrame format. Also calculates the desired data
 ## with corresponding dates and save everything as attributes
      
    def getData_df(self):
        db = dbConnection()
        cursor=db.cursor()
        query = ("SELECT * FROM `%s` WHERE date<='%s' AND date>='%s' ORDER BY date ASC" % (self.stock_name,self.final_date,self.init_date))
        dic = {}
        try:
            cursor.execute(query)
            df = pd.DataFrame(cursor.fetchall())
        except:
            self.all_data = [0]
            return 'No data'
        if len(df.columns)>4: # there is one extra column pandas creates
            header = ['dates','opening_price','high_price','low_price','closing_price','vol']
        else:
            header = ['dates','closing_price']
        count = 1
        for i in header:
            dic[count] = i
            count+=1
        ## SETTING DF PARAMETERS
        if df.empty:
            df = 0
            self.all_data = [0]
        else:

            df.drop([0], axis=1, inplace=True)
            df.rename(columns=dic,inplace=True) 
            df.set_index('dates', inplace=True)

            for i in header[1:]:
                df[i] = pd.to_numeric(df[i])

            self.init_date = df.index.values[0]
            self.final_date = df.index.values[-1]
            self.all_data = df

        return df

## This function finds the most important LongTerm Entry Points
## Pre condition: array of short and long term entry points
    def finalEntryPoints(self,longTermEntryPoints,shortTermEntryPoints):
        values,longTermEntryPoints = self.__findConsecutivePoints(longTermEntryPoints,shortTermEntryPoints)
        outcome = self.__analyzePoints(values,longTermEntryPoints)
        return outcome,longTermEntryPoints
    
    # Finding long term entry points surrounded by two short term points
    def __findConsecutivePoints(self,longTermEntryPoints,shortTermEntryPoints):
        values=[] #initializing
        count = 0
        for i in range(len(longTermEntryPoints)):
            temp1 = 999999
            for j in range(len(shortTermEntryPoints)):#finding first value
                if abs(longTermEntryPoints[i]-shortTermEntryPoints[j])<abs(longTermEntryPoints[i]-temp1) and longTermEntryPoints[i]>shortTermEntryPoints[j]:
                    temp1 = shortTermEntryPoints[j]
            temp2 = 999999
            for j in range(len(shortTermEntryPoints)): #finding second value
                if abs(longTermEntryPoints[i]-shortTermEntryPoints[j])<abs(longTermEntryPoints[i]-temp2) and longTermEntryPoints[i]>shortTermEntryPoints[j]:
                    if shortTermEntryPoints[j]!= temp1:
                        temp2 = shortTermEntryPoints[j]
            if temp1!=999999 and temp2!=999999:
                    values.extend([temp1,temp2])
            else:
                count+=1
        if count!=0:
            longTermEntryPoints = longTermEntryPoints[count:]
        return (values,longTermEntryPoints)
                
                
    def __analyzePoints(self,values,longTermEntryPoints):
        outcome = []
        for i in range(len(longTermEntryPoints)):
            price1=self.requested_data['closing_price'][values[i]]
            price2=self.requested_data['closing_price'][values[i+1]]
            avg = (price1+price2)/2
            a = self.requested_data['closing_price'][longTermEntryPoints[i]]
          #first calculate if the  avg of previous two points are within 5% of current point
            if abs(avg-a)>(a*(0.04)):
                if avg>a:
                    #TIME TO SELL
                    outcome.append('SELL')
                else:
                    #TIME TO BUY
                    outcome.append('BUY')  
            else:
                if avg>a:
                    #TIME TO BUY
                    outcome.append('BUY')   
                else: 
                     #TIME TO SELL
                    outcome.append('SELL')
            values.pop(0)
        return outcome
# Find correct intersection points
## This function uses the calculated entry points to get THE REAL POINTS
## Compares current poits with 200 SMA to screen outliers
    def realIntersectionPoints(self,longTermEntryPoints_up,longTermEntryPoints_down):
        intersectionsUp = np.array(longTermEntryPoints_up,dtype=np.int32)
        intersectionsDown = np.array(longTermEntryPoints_down,dtype=np.int32)
        longTermEntryPoints_up = np.array(longTermEntryPoints_up,dtype=np.int32)
        longTermEntryPoints_down = np.array(longTermEntryPoints_down, dtype=np.int32)

        counter = 0
        for i in range(len(longTermEntryPoints_up)):## HERE IS THE ERROR SMA LENGTH IS LOWER THAN REQUESTED
            if self.requested_data['closing_price'][longTermEntryPoints_up[i]] > 0.95*(self.sma1[longTermEntryPoints_up[i]]):
                counter +=1 
            else:
                intersectionsUp = np.delete(intersectionsUp,counter)
        counter = 0

        for i in range(len(longTermEntryPoints_down)):
            if self.requested_data['closing_price'][longTermEntryPoints_down[i]] > 0.95*(self.sma1[longTermEntryPoints_down[i]]) and self.requested_data['closing_price'][longTermEntryPoints_down[i]] < 1.05*(self.sma1[longTermEntryPoints_down[i]]):
                counter +=1 
                (self.requested_data['closing_price'].iloc[longTermEntryPoints_down[i]])
                (self.sma1[longTermEntryPoints_down[i]])
            else:
                intersectionsDown = np.delete(intersectionsDown,counter) 
        self.SMAs_intersectionIndex_up = intersectionsUp
        self.SMAs_intersectionIndex_down = intersectionsDown
        return None
    
# Analyzes change on interest rates and gives DataFrame with dates and corresponding interest rate values 
# where chances of crash or recession are high
# Input: self.requested_data of interest rates
# Output: DataFrame with key Interest Rates changes    
    def analyzingInterestRates(self):
        if len((self.all_data).columns)>2:
            sys.exit('Stock Object does not have interest Rate Info')
        else:
            counter =0
            keep = 0
            for i in range(len(self.all_data)-2):
                if self.all_data['closing_price'][counter+2] < self.all_data['closing_price'][counter+1] and self.all_data['closing_price'][counter+1] >= self.all_data['closing_price'][counter]:
                    keep = (self.all_data['closing_price'][counter+1])
                    self.all_data=(self.all_data).drop(self.all_data.index[counter])
                else:
                    if keep != self.all_data['closing_price'][counter]:
                        self.all_data=(self.all_data).drop(self.all_data.index[counter])
                    else:
                        counter+=1
                        keep = 0
                        
                        
            if keep != self.all_data['closing_price'][counter]:
                self.all_data=(self.all_data).drop(self.all_data.index[counter])
                self.all_data=(self.all_data).drop(self.all_data.index[counter])
            else:
                self.all_data=(self.all_data).drop(self.all_data.index[counter+1])
        return None 
## OTHER FUNCTIONS!

#GET SMA of something that is not  a StockObject
def arrSMA(arr,period):  
    sma = np.arange(len(arr)-period)
    for x in range(len(sma)):
        temp = arr[x:(x+period)]
        temp = sum(temp)/period
        sma[x] = temp;
    return sma
  
