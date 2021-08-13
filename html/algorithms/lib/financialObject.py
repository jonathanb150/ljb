#!/usr/bin/env python
#FILE NAME: financialObject.py

import numpy as np
from core import dbConnection,todaysDate
import scipy
import mysql.connector
import pandas as pd
import sys
import math
import datetime

class financialObject:
    def __init__(self,name= "MSFT",price = None, revenue = None, profit = None, cash = None, equity= None,
            eps = None, debt = None, roi = None, marketcap = None, marketcapquarterly = None, dividendyieldquarterly = None,
             assets = None, dividendyield = None, fcf = None, truevalue = None, expectedvalue = None, finaldate = 0, initdate = 0, expectedgrowth = None):
        self.name = name
        self.price = price
        self.revenue = revenue
        self.profit = profit
        self.cash = cash
        self.equity = equity
        self.eps = eps
        self.debt = debt
        self.roi = roi
        self.marketcap = marketcap
        self.assets = assets
        self.true_value = truevalue
        self.expected_value = expectedvalue
        self.finaldate = finaldate
        self.initdate = finaldate
        self.dividendyield = dividendyield
        self.marketcapquarterly = marketcapquarterly
        self.dividendyieldquarterly = dividendyieldquarterly
    # Find all financials, dates and financial name must be provided
    def getFinancials(self,financialInfo='Equity',*args):
        financials = [financialInfo]
    # Checking that initial and final date were provided
        try:
            self.initdate
            self.finaldate
            self.name
        except:
            return 'No dates provided'
        for i in args:
            financials.append(i)
    # connecting to database and extracting data
        db = dbConnection()
        cursor=db.cursor()

        for i in financials:
            try:
                query = ("SELECT * FROM `%s` WHERE type='%s' AND date<='%s' AND date>='%s' ORDER BY date DESC" % (self.name,i,self.finaldate,self.initdate))
                cursor.execute(query)
            except:
                return 'No data'
            if len(query) == 0:
                sys.exit('No Financial Info')

            df = pd.DataFrame(cursor.fetchall())

            if len(df) > 1: # there must be financial info
            # making header and transforming everything to ints
                header = {1:'dates',2:'closing_price'}
                df.rename(columns=header,inplace=True)
                df.set_index('dates', inplace=True)
                df =pd.to_numeric(df[header[2]]) 
                setattr(self,i.lower(),df.iloc[0:])
            else: # no financial info
                df = None
        return None

 # Find financials change over time and calculates true value of the company
 # Input = financial Object with rev,equity,cash,eps,debt,profit data   
 # Output = true Value of the company, expected Value in 5 years   

    def analyzeCompany(self,period,peRatio,changes,mature=False):    
        eps = scipy.sum(self.eps[:4])

        EquityChange = round(changes[4]/100,2)
        CashChange = round(changes[3]/100,2)     
        if changes[0]> changes[1]:
            mainChange = round(changes[0]/100,2)
            secondaryChange = round(changes[1]/100,2)
        else:
            mainChange = round(changes[1]/100,2)
            secondaryChange = round(changes[0]/100,2)
        if mature == False:
            growth_avg = 0.5*mainChange+secondaryChange*0.15+0.25*CashChange+0.1*EquityChange
        else:
            growth_avg = 0.4*mainChange+0.3*secondaryChange+0.2*CashChange+0.1*EquityChange

        if eps < 0 or peRatio<0:
            self.currentPrice()
            self.expected_value = round(self.price*0.9*((1+growth_avg)*(1+0.9*growth_avg)*(1+0.8*growth_avg)),2)
            self.true_value = round(self.expected_value/((1+0.15)**period),2)
        else:
            self.expected_value = round(peRatio*(eps)*((1+growth_avg)*(1+0.9*growth_avg)*(1+0.8*growth_avg)),2)
            self.true_value = round(self.expected_value/((1+0.15)**period),2)

    def predictedStockChange(self,period,changes,mature=False):    
        eps = scipy.sum(self.eps[:4])

        EquityChange = round(changes[4]/100,2)
        CashChange = round(changes[3]/100,2)     
        if changes[0]> changes[1]:
            mainChange = round(changes[0]/100,2)
            secondaryChange = round(changes[1]/100,2)
        else:
            mainChange = round(changes[1]/100,2)
            secondaryChange = round(changes[0]/100,2)
        if mature == False:
            growth_avg = round(0.5*mainChange+secondaryChange*0.15+0.25*CashChange+0.1*EquityChange,2)
        else:
            growth_avg = round(0.4*mainChange+0.3*secondaryChange+0.2*CashChange+0.1*EquityChange,2)

        self.expectedgrowth = growth_avg

#OUTPUT is a number out of 10. 10 being very safe, 0 being extremely risky
    def riskRewardRatio(self):
        points = 0
# IF REV AND/OR PROFIT IS GROWING
        if  scipy.sum(self.revenue[0:4])>scipy.sum(self.revenue[-4:]) and scipy.sum(self.profit[0:4])>scipy.sum(self.profit[-4:]):
            points+=4
        elif  scipy.sum(self.revenue[0:4])>scipy.sum(self.revenue[-4:]) or scipy.sum(self.profit[0:4])>scipy.sum(self.profit[-4:]):
            points+=2
# IF CASH IS GROWING
        if scipy.average(self.cash[0:2])>1.1*scipy.average(self.cash[3:8]):
            points+=2
        elif scipy.average(self.cash[0:2])>1.15*scipy.average(self.cash[8:12]):
            points+=1
# IF CASH IS LARGER THAN DEBT
        if self.cash[0]>self.debt[0]:
            points+=3
        elif 3*self.cash[0]>self.debt[0]:
            points+=2
        elif 2*self.cash[0]>self.debt[0]:
            points+=1
# IF EQUITY IS GROWING
        if scipy.average(self.equity[0:2])>scipy.average(self.equity[6:10]):
            points+=1     
        return points

    def currentPrice(self):
    # connecting to database and extracting data
            try:
                date = self.finaldate
            except:
                date = todaysDate()
            db = dbConnection()
            cursor=db.cursor()
            query = ("SELECT close from `%s` WHERE date<='%s'  ORDER BY date DESC" %(self.name[:-2]+'_1d',date))
            cursor.execute(query)
            row = cursor.fetchone()
            self.price = float(row[0])

    def currentPeRatio(self):
        name = self.name[:-2] + '_1d'
        final = self.finaldate
        init = str(int(final[:4]) - 3) + self.finaldate[4:]
        db = dbConnection()
        cursor = db.cursor()
        query = ("SELECT close FROM %s WHERE date>='%s'AND date<='%s' ORDER BY date DESC" %(name,init,final))
        price =[]
        cursor.execute(query)
        temp_price = cursor.fetchone()
        while isinstance(temp_price,tuple):
            price.append(float(temp_price[0]))
            temp_price = cursor.fetchone()
            
        eps1y = float(scipy.sum(self.eps[:4]))
        #eps2y= float(scipy.sum(self.eps[4:8]))
        eps3y = float(scipy.sum(self.eps[8:12]))
        
        finalvalue= int(scipy.floor(len(price)/3))
        
        pe = [price[0]/eps1y]
        pe.append(np.mean(price[0:finalvalue])/eps1y)
        pe.append((np.mean(price[2*finalvalue:])/eps3y))
        ## returns PE today, One year AVG, 3 Year avg
        return pe

    def priceStatistics(self,initdate):
    # connecting to database and extracting data
            db = dbConnection()
            cursor=db.cursor()
            today = todaysDate()
            today = datetime.datetime.strptime(today, '%Y-%m-%d')
            initdate = datetime.datetime.strptime(initdate, '%Y-%m-%d')
            finaldate=initdate+datetime.timedelta(days=90)
        
            if today-finaldate>datetime.timedelta(days=1):
                query = ("SELECT close from `%s` WHERE date>'%s' AND date<'%s' ORDER BY date DESC" %(self.name[:-2]+'_1d',initdate,finaldate))
                try:
                    df = []
                    cursor.execute(query)
                    temp = cursor.fetchall()
                    for i in temp:
                        df.append(round(float(i[0]),2))

                    min_price = min(df)
                    max_price = max(df)
                except:
                    return 'Error, No Data'
            else:
                min_price = 'No data'
                max_price = 'No data'

            if today-initdate<datetime.timedelta(days=365):
                max_2y = 'No data' 
                max_1y = 'No data'
            elif today-initdate>datetime.timedelta(days=365) and today-initdate<datetime.timedelta(days=730):
                finaldate = initdate+datetime.timedelta(days=365)
                query = ("SELECT close from `%s` WHERE date>'%s' AND date<'%s'  ORDER BY date DESC" %(self.name[:-2]+'_1d',initdate,finaldate))
                cursor.execute(query)
                df = []
                temp = cursor.fetchall()
                for i in temp:
                    df.append(round(float(i[0]),2))

                max_1y = max(df)
                max_2y = 'No data'
            else:
                finaldate = initdate+datetime.timedelta(days=730)
                query = ("SELECT close from `%s` WHERE date>'%s' AND date<'%s'  ORDER BY date DESC" %(self.name[:-2]+'_1d',initdate,finaldate))
                df = []
                cursor.execute(query)
                temp = cursor.fetchall()
                for i in temp:
                    df.append(round(float(i[0]),2))
                max_1y = max(df[-255:])
                max_2y = max(df)  
            # HEADER ['min','max','max_1y','max_2y']   

            stats = [min_price,max_price,max_1y,max_2y]
            
            return stats

    def peRatio(self,finaldate):
        self.finaldate = finaldate
        self.initdate = str(int(finaldate[:4]) - 4) + finaldate[4:]    
        self.getFinancials('Eps')
        # pe [peNow,pe1YearAvg,pe2YearAvg]
        pe = self.currentPeRatio()
        return pe