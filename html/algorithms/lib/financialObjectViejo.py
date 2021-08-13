#!/usr/bin/env python
#FILE NAME: financialObject.py

import numpy as np
import scipy
import mysql.connector
import pandas as pd
import sys
import math

class financialObject:
    def __init__(self,name= "MSFT_f", revenue = None, profit = None, cash = None, equity= None,
                 eps = None, debt = None, roi = None, marketcap = None, assets = None,
                 trueValue = None, expectedValue = None, finaldate = 0):
        self.name = name
        self.revenue = revenue
        self.profit = profit
        self.cash = cash
        self.equity = equity
        self.eps = eps
        self.debt = debt
        self.roi = roi
        self.marketcap = marketcap
        self.assets = assets
        self.trueValue = trueValue
        self.expectedValue = expectedValue
        self.finaldate = finaldate
        
    def getFinancials(self,financialInfo='Equity'):
        try:
            db = mysql.connector.connect(host='localhost',user='root',passwd='bj37133*',db='finance')
        except mysql.connector.Error as err:
            if err.errno == errorcode.ER_ACCESS_DENIED_ERROR:
                sys.exit(["Something is wrong with your user name or password"])
            elif err.errno == errorcode.ER_BAD_DB_ERROR:
                sys.exit(["Database does not exist"])
            else:
                sys.exit(err)
        else:
            cursor=db.cursor()
            query = ("SELECT * FROM %s WHERE type='%s' AND date<='%s' AND date>='%s' ORDER BY date DESC" % (self.name,financialInfo,self.finaldate,self.initdate) )
            cursor.execute(query)
            row = cursor.fetchone()
            if isinstance(row,tuple):
                data = np.zeros(len(row))
                while isinstance(row,tuple):
                    temp = np.asarray(row)
                    data = np.vstack([data,temp])
                    row = cursor.fetchone()

                new_df = pd.DataFrame(data)    
                del new_df[0]
                header = {1:'dates',2:'closing_price'}
                new_df.rename(columns=header,inplace=True)
                new_df.set_index('dates', inplace=True)
                new_df =pd.to_numeric(new_df[header[2]])
                if financialInfo == 'Revenue':
                    self.revenue = new_df.iloc[1:]
                elif financialInfo == 'Profit':
                    self.profit = new_df.iloc[1:]
                elif financialInfo == 'Cash':
                    self.cash = new_df.iloc[1:]
                elif financialInfo == 'Equity':
                    self.equity = new_df.iloc[1:]
                elif financialInfo == 'Eps':
                    self.eps = new_df.iloc[1:]
                elif financialInfo == 'Debt':
                    self.debt = new_df.iloc[1:]
                elif financialInfo == 'MarketCap':
                    self.marketcap = new_df.iloc[1:]
                elif financialInfo == 'Assets':
                    self.assets = new_df.iloc[1:]

            else:
                sys.exit(['No Financial Info'])
        return None
 # Find financials change over time and calculates true value of the company
 # Input = financial Object with rev,equity,cash,eps,debt,profit data   
 # Output = true Value of the company, expected Value in 5 years   
    def financialChanges(self,quarters):
        if isinstance(self.debt,pd.core.series.Series) or isinstance(self.debt,pd.core.frame.DataFrame):
            self.roi = self.profit/(self.equity+self.debt)
        else:
            self.roi = self.profit/self.equity
            
        self.roi = self.roi.iloc[::-1]
        #Initializing
        RevenueChange = np.asarray((range(math.floor(len(self.revenue)/quarters)-1)))
        ProfitChange = np.asarray((range(math.floor(len(self.profit)/quarters)-1)))
        CashChange = np.asarray((range(math.floor(len(self.cash)/quarters)-1)))
        EquityChange = np.asarray((range(math.floor(len(self.equity)/quarters)-1)))
        EpsChange = np.asarray((range(math.floor(len(self.eps)/quarters)-1)))
        RoiChange = np.asarray((range(math.floor(len(self.roi)/quarters)-1)))
        DebtChange = np.asarray((range(math.floor(len(self.debt)/quarters)-1)))
        
        RevenueChange2 = np.asarray((range(math.floor(len(self.revenue)/quarters)-1)),float)
        ProfitChange2 = np.asarray((range(math.floor(len(self.profit)/quarters)-1)),float)
        CashChange2 = np.asarray((range(math.floor(len(self.cash)/quarters)-1)),float)
        EquityChange2 = np.asarray((range(math.floor(len(self.equity)/quarters)-1)),float)
        EpsChange2 = np.asarray((range(math.floor(len(self.eps)/quarters)-1)),float)
        RoiChange2 = np.asarray((range(math.floor(len(self.roi)/quarters)-1)),float)
        DebtChange2 = np.asarray((range(math.floor(len(self.debt)/quarters)-1)),float)
        #Analyzing financial data --> finding changes
        names = ['revenue','profit','cash','equity','eps','roi','debt']
        counter = 0
        count =1
        for i in [RevenueChange,ProfitChange,CashChange,EquityChange,EpsChange,RoiChange,DebtChange]:
            for x in i:
                temp = names[counter]
                if temp == 'revenue':
                    RevenueChange2[x] = (sum(self.revenue.iloc[quarters*(count-1):quarters*count])-sum(self.revenue.iloc[quarters*count:quarters*(count+1)]))/scipy.absolute(sum(self.revenue.iloc[quarters*count:quarters*(count+1)]))*100
                    count+=1
                elif temp == 'profit':
                    ProfitChange2[x] = (sum(self.profit.iloc[quarters*(count-1):quarters*count])-sum(self.profit.iloc[quarters*count:quarters*(count+1)]))/scipy.absolute(sum(self.profit.iloc[quarters*count:quarters*(count+1)]))*100
                    count+=1
                elif temp == 'cash':
                    CashChange2[x] = (self.cash.iloc[quarters*(count-1)]-self.cash.iloc[quarters*count])/scipy.absolute(self.cash.iloc[quarters*count])*100
                    count+=1
                elif temp == 'equity':
                    EquityChange2[x] = (self.equity.iloc[quarters*(count-1)]-self.equity.iloc[quarters*count])/scipy.absolute(self.equity.iloc[quarters*count])*100
                    count+=1
                elif temp == 'eps':
                    EpsChange2[x] = (sum(self.eps.iloc[quarters*(count-1):quarters*count])-sum(self.eps.iloc[quarters*count:quarters*(count+1)]))/scipy.absolute(sum(self.eps.iloc[quarters*count:quarters*(count+1)]))*100
                    count+=1
                elif temp == 'roi':
                    RoiChange2[x] = (self.roi.iloc[quarters*(count-1)]-self.roi.iloc[quarters*count])/scipy.absolute(self.roi.iloc[quarters*count])*100
                    count+=1
                elif temp == 'debt':
                    DebtChange2[x] = (self.debt.iloc[quarters*(count-1)]-self.debt.iloc[quarters*count])/scipy.absolute(self.debt.iloc[quarters*count])*100
                    count+=1

            counter+=1
            count =1
        return (RevenueChange2,ProfitChange2,EquityChange2,EpsChange2,CashChange2,RoiChange2,DebtChange2)



    def analyzeGrowthCompany(self,period,peRatio,RevenueChange,ProfitChange,EquityChange,CashChange):    
        currentValue =0
        eps = []
        counter =0
        for i in range(len(scipy.floor(self.eps/4))):
            eps.append(scipy.sum(self.eps[counter:counter+4]))
            counter+=4
        if isinstance(EquityChange,str):
            EquityChange = 0
        EquityChange = EquityChange/100
        CashChange = CashChange/100     
        if RevenueChange> ProfitChange:
            mainChange = RevenueChange/100
        else:
            mainChange = ProfitChange/100

        growth_avg = 0.65*mainChange+0.25*CashChange+0.1*EquityChange

        if scipy.average(self.eps[:period+1]) < 0:
            self.expectedValue = 'EPS is Negative'
            self.expectedValue = peRatio*(abs(scipy.average(eps[:period])))*((1+growth_avg)*(1+0.9*growth_avg)*(1+0.8*growth_avg))
            self.trueValue = self.expectedValue/((1+0.15)**period) 
        else:
            self.expectedValue = peRatio*(scipy.average(eps[:period]))*((1+growth_avg)*(1+0.9*growth_avg)*(1+0.8*growth_avg))
            self.trueValue = self.expectedValue/((1+0.15)**period)        

    def analyzeMatureCompany(self,period,peRatio,RevenueChange,ProfitChange,EquityChange,CashChange,assetHeavy):
        currentValue =0
        eps = []
        counter =0
        for i in range(len(scipy.floor(self.eps/4))):
            eps.append(scipy.sum(self.eps[counter:counter+4]))
            counter+=4
        EquityChange = EquityChange/100
        CashChange = CashChange/100
        if RevenueChange> ProfitChange:
                mainChange = RevenueChange/100
                secondaryChange = ProfitChange/100
        else:
                mainChange = ProfitChange/100
                secondaryChange = RevenueChange/100 

        if assetHeavy == True:
            growth_avg = 0.4*mainChange+0.3*secondaryChange+0.2*EquityChange+0.1*CashChange

        else:
            growth_avg = 0.4*mainChange+0.3*secondaryChange+0.2*CashChange+0.1*EquityChange
        if scipy.average(self.eps[:period+1]) < 0:
            self.expectedValue = 'EPS is Negative'
            self.expectedValue = peRatio*(abs(scipy.average(eps[:period])))*((1+growth_avg)*(1+0.9*growth_avg)*(1+0.8*growth_avg))
            self.trueValue = self.expectedValue/((1+0.15)**period) 
        else:
            self.expectedValue = peRatio*(scipy.average(eps[:period]))*((1+growth_avg)*(1+0.9*growth_avg)*(1+0.8*growth_avg))
            self.trueValue = self.expectedValue/((1+0.15)**period)        

#OUTPUT is a number out of 10. 10 being very safe, 0 being extremely risky
    def riskRewardRatio(self,period,RevenueChange,ProfitChange,EquityChange,CashChange,RoiChange):
        if  len(RevenueChange) >= period and len(ProfitChange)>= period and len(EquityChange)>=period and len(CashChange)>=period:
            points = 0
            rev = scipy.average(RevenueChange[:period+1])
            prof = scipy.average(ProfitChange[:period+1])
            if prof>rev:
                main = prof
            else:
                main = rev
            equi = scipy.average(EquityChange[:period+1])
            cash = scipy.average(CashChange[:period+1])
            roi = scipy.average(RoiChange[:period+1])
            for i in [main,roi,equi,cash]:
                if i>15:
                    points+=2
                elif i>7:
                    points+=1.5
                elif i>0:
                    points+=.75
            # using current debt and cash
            if (self.cash[0])> (self.debt[0])*0.25:
                points+=2
            elif (self.cash[0])> (self.debt[0])*0.15:
                points+=1
            elif (self.cash[0])> (self.debt[0])*0.07:
                points+=0.5
            
        else:
            points = 'Not Enough Info'
            
        return points
    
    def avgFinancialsChange(self,period,RevenueChange,ProfitChange,EquityChange,EpsChange,CashChange,RoiChange,DebtChange):
        #avg change in the last period
        if len(RevenueChange) >= period and len(ProfitChange)>= period and len(EquityChange)>=period and len(CashChange)>=period and len(RoiChange)>=period and len(EpsChange)>=period and len(DebtChange)>=period:
            changes = []
            for i in [RevenueChange,ProfitChange,EquityChange,EpsChange,CashChange,RoiChange,DebtChange]:
                temp = (np.asarray(i[:period])/100) +1
                change = 1
                for x in range(len(temp)):
                    if temp[x]>2.5:
                        temp[x] = 1.9

                for x in range(period):
                    change_confirmation = temp[x]
                    if change_confirmation<=0:
                        change = change*0.001
                    else:
                        change = change_confirmation*change
                changes.append(((change**(1/period))-1)*100)
        else:
            changes = 'Not Enough Info'
                
        return changes
    
    def avgRealFinancialsChange(self,period,RevenueChange,ProfitChange,EquityChange,EpsChange,CashChange,RoiChange,DebtChange):
    #avg change in the last period
        if len(RevenueChange) >= period and len(ProfitChange)>= period and len(EquityChange)>=period and len(CashChange)>=period and len(RoiChange)>=period and len(EpsChange)>=period and len(DebtChange)>=period:
            changes = []
            for i in [RevenueChange,ProfitChange,EquityChange,EpsChange,CashChange,RoiChange,DebtChange]:
                temp = (np.asarray(i[:period])/100) +1
                change = 1
                for x in range(period):
                    change_confirmation = temp[x]
                    if change_confirmation<=0:
                        change = change*0.001
                    else:
                        change = change_confirmation*change
                changes.append(((change**(1/period))-1)*100)
        else:
            changes = 'Not Enough Info'
                
        return changes