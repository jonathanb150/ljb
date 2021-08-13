#!/usr/bin/env python
# File Name: StockObject.py

# Importing everything we need
from core import graphMe,graphMeScatter
import numpy as np
import scipy
import matplotlib.pyplot as plt
import sys, os
import _mysql
import mysql.connector
import pandas as pd
import matplotlib
matplotlib.use('Agg')
import matplotlib.dates as mdates
import matplotlib.pyplot as plt
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
        if isinstance(self.all_data , type(None)):
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
# Pre condition: stockObject with data, dates and a period to evaluate
    def stochastic(self,**kwargs):
        period = kwargs.get('period',14)
        stochastic = np.array([])
        index1 = self.all_data.index.get_loc(self.init_date)
        index2 = self.all_data.index.get_loc(self.final_date)
        stoch_data = self.all_data[index1-period+1:index2+1]
        for x in range(len(self.requested_data['closing_price'])-1):
            highest_high = (stoch_data['high_price'][x:period+x]).max()
            lowest_low = (stoch_data['low_price'][x:period+x]).min()
            current_close = stoch_data['closing_price'][x+period-1]
    #Getting stochastic value
            K = np.array([100*(current_close - lowest_low)/(highest_high - lowest_low)])
            stochastic = np.concatenate([stochastic,K])
        stoch = arrSMA(stochastic,3)
        D = arrSMA(stoch,5)
    # Graphing stochastic
        #plt.figure()
        #ax1=figure = plt.subplot(2,1,1)
        #figure = graphMe(self.requested_data.index.values,self.requested_data['closing_price'], label = self.stock_name)
        #figure = plt.scatter((self.requested_data).index[(self.SMAs_intersection)],(self.requested_data)['closing_price'][(self.SMAs_intersection)],
        #           marker = '*',s=200,c='black',label='BUY')
        #ax2= plt.subplot(2,1,2,sharex=ax1)
        #figure = graphMe((self.requested_data).index.values[len(self.requested_data)-len(stoch):],stoch, label = 'stoch')
        #figure = graphMe(self.requested_data.index.values[len(self.requested_data)-len(D):],D,color='blue')
        
        return (stoch,D)

#Getting Moving Average
# Calculates moving Average of desired data
    def SMA(self,period):  
        if isinstance(self.all_data, type(None)):
            sys.exit(['No Data associated with stock'])
    #getting SMA
        elif isinstance(self.all_data,pd.DataFrame) or isinstance(self.all_data, pd.Series):
            index1 = (self.all_data).index.get_loc(self.init_date)
            index2 = (self.all_data).index.get_loc(self.final_date)
            sma_data = (self.all_data)[index1-period+1:index2+1]
            sma = np.array(range(len(sma_data)-period+1),dtype='float64')
            if isinstance(self.all_data,pd.DataFrame):
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
# Pre condition: self.SMAs must exist
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
        data_df= self.getData()
        self.all_data = data_df
        if len((self.all_data).columns)>2:
            header = ['dates','closing_price','high_price','low_price','opening_price', 'vol']
        else:
            header = ['dates','closing_price']
        counter =1
        dic={}
        for x in header:
            dic[counter]=x
            counter+=1
        
        data_df.rename(columns=dic,inplace=True)
        data_df.set_index('dates', inplace=True)
        new_df =pd.to_numeric(data_df[header[1]])
        if len(header)>2:
            for x in header[2:]:
                new_df = pd.concat([new_df,pd.to_numeric(data_df[x])],axis=1)
        if isinstance(self.init_date ,str):
            self.init_date = datetime.strptime(self.init_date, "%Y-%m-%d").date()
        if isinstance(self.final_date,str):
            self.final_date = datetime.strptime(self.final_date, "%Y-%m-%d").date()
        
        self.all_data = new_df
        #Checking if the dates requested exist
        result = False
        while result is False:
            try:
                initDate = (self.all_data).index.get_loc(self.init_date)
                result = True
            except KeyError:
                initDate = self.init_date -timedelta(days=1)
                self.init_date =initDate
        result = None
        while result is None:
            try:
                finalDate = (self.all_data).index.get_loc(self.final_date)
                result = 'Done'
            except KeyError:
                finalDate = self.final_date -timedelta(days=1)
                self.final_date =finalDate
        initDate = self.all_data.index[initDate]
        finalDate = self.all_data.index[finalDate]
        self.requested_data = (self.all_data)[initDate:finalDate]

#Gets All data from desired stock from database in numpy arr format        
    def getData(self):
        try:
            db = mysql.connector.connect(host='localhost',user='root',passwd='bj37133*',db='finance')
        except mysql.connector.Error as err:
            if err.errno == errorcode.ER_ACCESS_DENIED_ERROR:
                sys.exit("Something is wrong with your user name or password")
            elif err.errno == errorcode.ER_BAD_DB_ERROR:
                sys.exit("Database does not exist")
            else:
                sys.exit(err)
        else:
            cursor = db.cursor()
            query = ("SELECT * FROM `%s`" % (self.stock_name))
            cursor.execute(query)
            row = cursor.fetchone()
            data = np.zeros(len(row))
            while isinstance(row,tuple):
                temp = np.asarray(row)
                data = np.vstack([data,temp])
                row = cursor.fetchone()
        df=pd.DataFrame(data)    
        return df.loc[1:,1:]

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
        for i in range(len(longTermEntryPoints_up)):
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
        if len(self.requested_data.shape)>1:
            sys.exit('Stock Object does not have interest Rate Info')
        else:
            counter =0
            keep = 0
            for i in range(len(self.requested_data)-2):
                if self.requested_data[counter+2] < self.requested_data[counter+1] and self.requested_data[counter+1] >= self.requested_data[counter]:
                    keep = (self.requested_data[counter+1])
                    self.requested_data=(self.requested_data).drop(self.requested_data.index[counter])
                else:
                    if keep != self.requested_data[counter]:
                        self.requested_data=(self.requested_data).drop(self.requested_data.index[counter])
                    else:
                        counter+=1
                        keep = 0
                        
                        
            if keep != self.requested_data[counter]:
                self.requested_data=(self.requested_data).drop(self.requested_data.index[counter])
                self.requested_data=(self.requested_data).drop(self.requested_data.index[counter])
            else:
                self.requested_data=(self.requested_data).drop(self.requested_data.index[counter+1])
        return None 
## OTHER FUNCTIONS!

#GET SMA of something that is not  a StockObject
def arrSMA(arr,period):  
    sma = np.arange(len(arr)-1)
    for x in range(len(sma)):
        temp = arr[x:(x+period)]
        temp = sum(temp)/period
        sma[x] = temp;
    return sma
  
