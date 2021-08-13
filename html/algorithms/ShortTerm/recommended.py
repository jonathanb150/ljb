#!/usr/bin/python3.5
#FILE NAME: reccomended.py
# Analyzes stocks in short term

## FUNDAMENTAL ANALYSIS ALGORITHM + TECHNICAL
## CALCULATES STOCK GROWTH, FINANCIAL STABILITY, REAL PRICE, AND FUTURE VALUE AND TECHNICALS
## USE ONLY FOR STOCKS
import sys, os
sys.path.append("/home/ubuntu/www/algorithms/lib")
from financialObject import financialObject
from stockObject import stockObject
import matplotlib
matplotlib.use('Agg')
import scipy
import plotly.plotly as py
import plotly
import plotly.graph_objs as go
import matplotlib.pyplot as plt,mpld3
import json
import sys


def main():
    stock_f = financialObject()
    stock = stockObject()
        #inputs
    stock_f.name = sys.argv[1] + '_f' # str company symbol
    stock.stock_name = sys.argv[1] + '_1d'# str company symbol
    pe1 = float(sys.argv[2]) # number
    MatureOrGrowth = sys.argv[3] # str Mature or Growth
    assetHeavy = sys.argv[4] # empty str in this case 
    stock.init_date = sys.argv[5] # init date Yr-mm-dd
    stock.final_date = sys.argv[6]# final date Yr-mm-dd
    stock_f.finaldate = sys.argv[6]# final date Yr-mm-dd
    
## GETTING ALL FINANCIAL INFO AND STORING AS ATTRIBUTES
    stock_f.getFinancials('Revenue')
    stock_f.getFinancials('Profit')
    stock_f.getFinancials('Cash')
    stock_f.getFinancials('Equity')
    stock_f.getFinancials('Eps')
    stock_f.getFinancials('Debt')
## updating financials
    stock_f.revenue = financialsLast3Years(stock_f,stock_f.revenue)
    stock_f.profit = financialsLast3Years(stock_f,stock_f.profit)
    stock_f.cash = financialsLast3Years(stock_f,stock_f.cash)
    stock_f.equity = financialsLast3Years(stock_f,stock_f.equity)
    stock_f.eps = financialsLast3Years(stock_f,stock_f.eps)
    stock_f.debt = financialsLast3Years(stock_f,stock_f.debt)

## Finding financial changes over time
    (RevenueChangeY,ProfitChangeY,EquityChangeY,EpsChangeY,CashChangeY,RoiChangeY,DebtChangeY) = stock_f.financialChanges(4)
    (RevenueChangeQ,ProfitChangeQ,EquityChangeQ,EpsChangeQ,CashChangeQ,RoiChangeQ,DebtChangeQ) = stock_f.financialChanges(1)
## Change in the last 2 quarters and last 3 years
## output order [RevenueChange,ProfitChange,EquityChange,EpsChange,CashChange,RoiChange]
    changesQ = stock_f.avgFinancialsChange(2,RevenueChangeQ,ProfitChangeQ,EquityChangeQ,EpsChangeQ,CashChangeQ,RoiChangeQ,DebtChangeQ)
    changesY = stock_f.avgFinancialsChange(3,RevenueChangeY,ProfitChangeY,EquityChangeY,EpsChangeY,CashChangeY,RoiChangeY,DebtChangeQ)
    if changesY == 'Not Enough Info':
        sys.exit(['Not Enough Financial Info'])
## Analyzing financials and getting true and expected price    
    dic= {}
    if MatureOrGrowth == 'Mature':
        stock_f.analyzeMatureCompany(3,pe1,changesY[0],changesY[1],changesY[2],changesY[4],assetHeavy)
    else:
        stock_f.analyzeGrowthCompany(3,pe1,changesY[0],changesY[1],changesY[2],changesY[4])

    dic['truePrice'] =stock_f.trueValue
    dic['expectedPrice'] = stock_f.expectedValue

#riskReward Ratio using last 3 years info
    period = 3
    points = stock_f.riskRewardRatio(period,RevenueChangeY,ProfitChangeY,EquityChangeY,CashChangeY,RoiChangeY)
    targetShort = stock_f.trueValue*1.2
    targetLong = stock_f.expectedValue
## TECHNICAL INFO SHORT TERM
    stock.getData_df()
    stock.period1 = 50
    stock.period2 = 20
    stock.period3 = 14
    stock.SMAsCrossover()
    resistance_level50 = stock.sma1[-1]
    stoch,d = stock.stochastic()
    longTermEntryPoints_up = stock.SMAs_intersectionIndex_up
    longTermEntryPoints_down = stock.SMAs_intersectionIndex_down
    #Determining if we are in short term bull or bear market ( above 100 SMA BULL, below BEAR)
    stock.period1=100
    stock.period2 = 200
    stock.SMAsCrossover()# only to stored new smas as attribute
    stock.realIntersectionPoints(longTermEntryPoints_up,longTermEntryPoints_down)  

    #Recommended entry Point Short Term
    if len(stock.SMAs_intersectionIndex_up)>1:
        entryShort = stock.requested_data['closing_price'][stock.SMAs_intersectionIndex_up[-1]]
    else:
        entryShort = 'not found'
    
    if scipy.average(d[-4:])<25:
        stochShort =  'Oversold'  
    elif scipy.average(d[-4:])>80:
        stochShort =  'Overbought'  
    else:
        stochShort = 'Medium' 
## TECHNICAL INFO LONG TERM
    stock.period1 = 100
    stock.period2 = 50
    stock.period3 = 20
    stock.SMAsCrossover()
    resistance_level50 = stock.sma2[-1]
    stoch,d = stock.stochastic()
    longTermEntryPoints_up = stock.SMAs_intersectionIndex_up
    longTermEntryPoints_down = stock.SMAs_intersectionIndex_down
    #Determining if we are in short term bull or bear market ( above 100 SMA BULL, below BEAR)
    stock.period1=200
    stock.period2 = 100
    stock.SMAsCrossover()# only to stored new smas as attribute
    stock.realIntersectionPoints(longTermEntryPoints_up,longTermEntryPoints_down)  
       #Recommended entry Point Long Term
    if len(stock.SMAs_intersectionIndex_up)>1:
        entryLong = stock.requested_data['closing_price'][stock.SMAs_intersectionIndex_up[-1]]
    else:
        entryLong = 'not found'
    
    if scipy.average(d[-4:])<25:
        stochLong =  'Oversold'  
    elif scipy.average(d[-4:])>80:
        stochLong =  'Overbought'  
    else:
        stochLong = 'Medium'
        
    resistance_level200 = stock.sma1[-1]
    resistance_level100 = stock.sma2[-1]
    
## Just in case changes are not real   
    changesQ2 = stock_f.avgRealFinancialsChange(2,RevenueChangeQ,ProfitChangeQ,EquityChangeQ,EpsChangeQ,CashChangeQ,RoiChangeQ,DebtChangeQ)
    changesY2 = stock_f.avgRealFinancialsChange(3,RevenueChangeY,ProfitChangeY,EquityChangeY,EpsChangeY,CashChangeY,RoiChangeY,DebtChangeY)
    if changesY2 != changesY:
        dic['truePrice'] = 0
        dic['truePrice2'] = 0
        changesY = changesY2
        changesQ = changesQ2
     
## Making Json
    names = ['rev','prof','equi','eps','cash','roi','rev3','prof3','equi3',
             'eps3','cash3','roi3','revq','profq','equiq','epsq','cashq','roiq',
             'revq2','profq2','equiq2','epsq2','cashq2','roiq2','currentRoi',
             'riskRewardRatio','entry_pointShort','entry_pointLong','resistance50','resistance100',
             'resistance200','target_mediumTerm','target_longTerm','stochShort','stochLong']

    info = [RevenueChangeY[0],ProfitChangeY[0],EquityChangeY[0],EpsChangeY[0],CashChangeY[0],
            RoiChangeY[0],changesY[0],changesY[1],changesY[2],changesY[3],changesY[4],changesY[5],
            RevenueChangeQ[0],ProfitChangeQ[0],EquityChangeQ[0],EpsChangeQ[0],CashChangeQ[0],
            RoiChangeQ[0],changesQ[0],changesQ[1],changesQ[2],changesQ[3],changesQ[4],changesQ[5],
            stock_f.roi[0]*100,points,entryShort,entryLong,resistance_level50,resistance_level100,resistance_level200,
            targetShort,targetLong,stochShort,stochLong]

    info_json = jsonOutputforPHP(dic,names,info)
    print(info_json)
    
#---------------------------------------OTHER FUNCTIONS-----------------------------------------
def jsonOutputforPHP(dic,names,info):
    counter =0
    for i in names:
        dic[i] = info[counter]
        counter+=1
    info_json = json.dumps(dic)
    
    return info_json


def financialsLast3Years(stock_f,financial_df):
    finalyear = int(stock_f.finaldate[:4])
    counter = 0
    if int(((financial_df.index.values[-1]).strftime('%Y-%m-%d'))[:4])>finalyear:
        sys.exit('Not Enough Financial Info')
    while counter!=50000: # 50k so It doesnt run forever in case of error
        dates = (financial_df.index.values[counter]).strftime('%Y-%m-%d')
        year = int(dates[:4])
        if year == finalyear:
            break
        counter+= 1

    temp_month = int(dates[5:7])
    desired_month = int(stock_f.finaldate[5:7])
    if (desired_month-temp_month)<=3 and (desired_month-temp_month)>=0:
        financial_temp_df = financial_df[counter:counter+12+4]# 12 = 3 years in quarters, +4 = an extra year to find change in last 3 years
    else:
        financial_temp_df = financial_df[counter:counter+12+4]
        counter+=1
        dates = (financial_df.index.values[counter]).strftime('%Y-%m-%d')
        temp_month2 = int(dates[5:7])
        year = int(dates[:4])
        if (desired_month-temp_month2)<=3 and (desired_month-temp_month2)>=0 and finalyear==year: # if new diff less than previous diff
            financial_temp_df = financial_df[counter:counter+12+4]
        else:
            counter+=1
            dates = (financial_df.index.values[counter]).strftime('%Y-%m-%d')
            temp_month3 = int(dates[5:7])
            if (desired_month-temp_month3)<=3 and (desired_month-temp_month3)>=0 and finalyear==year:
                financial_temp_df = financial_df[counter:counter+12+4]
            else:
                counter+=1
                financial_temp_df = financial_df[counter:counter+12+4]   
    return financial_temp_df

if __name__ =='__main__':
    main()
    