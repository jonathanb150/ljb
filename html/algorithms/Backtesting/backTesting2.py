#!/usr/bin/python3.5
#FILE NAME: backTesting.py
# Analyzes stocks in short term

## FUNDAMENTAL ANALYSIS ALGORITHM + TECHNICAL
## CALCULATES STOCK GROWTH, FINANCIAL STABILITY, REAL PRICE, AND FUTURE VALUE AND TECHNICALS
## USE ONLY FOR STOCKS
import sys, os
sys.path.append("/home/ubuntu/www/algorithms/lib")
from financialObjectNoPe import financialObject
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
    company = sys.argv[1]
    stock_f = financialObject()
    stock = stockObject()
        #inputs
    stock_f.name = company + '_f' # str company symbol
    stock.stock_name = company + '_1d'# str company symbol
    MatureOrGrowth = sys.argv[2] # str Mature or Growth
    assetHeavy = sys.argv[3] # empty str in this case 
    stock.init_date = sys.argv[4] # init date Yr-mm-dd
    stock.final_date = sys.argv[5]# final date Yr-mm-dd
    stock_f.finaldate = sys.argv[5]# final date Yr-mm-dd
    
    stock.getData_df()
    currentPrice = stock.requested_data['closing_price'][-1]
    avgCurrentPrice = scipy.average(stock.requested_data['closing_price'][-5])
    
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
         stock_f.analyzeMatureCompany(3,currentPrice,changesY[0],changesY[1],changesY[2],changesY[4],assetHeavy)
    else:
        stock_f.analyzeGrowthCompany(3,currentPrice,changesY[0],changesY[1],changesY[2],changesY[4])
    


    dic['truePrice'] =stock_f.trueValue
    trueValue = stock_f.trueValue
    dic['target_mediumTerm'] = stock_f.trueValue*1.2
    dic['target_longTerm'] = stock_f.expectedValue

    stock_f.analyzeGrowthCompany(3,currentPrice,changesQ[0],changesQ[1],changesQ[2],changesQ[4])
    trueShortTermPrice = stock_f.trueValue

#riskReward Ratio using last 3 years info
    period = 3
    points = stock_f.riskRewardRatio(period,RevenueChangeY,ProfitChangeY,EquityChangeY,CashChangeY,RoiChangeY)
    dic['riskRewardRatio'] = points

## TECHNICAL INFO SHORT TERM
    dic['currentPrice'] = currentPrice 
    stock.period1 = 200
    stock.period2 = 100
    stock.period3 = 50
    stock.SMAsCrossover() ## just to get the SMAs
    currentSMA200 = stock.sma1[-1]
    currentSMA100 = stock.sma2[-1]
    maxLastMonth = (stock.requested_data['closing_price'][-23]).max()
    maxLastMonth = (stock.requested_data['closing_price'][-23]).max()
    maxLast2Month = (stock.requested_data['closing_price'][-46]).max()
    avgLast2weeks = scipy.average(stock.requested_data['closing_price'][-10:])
    avgLastWeek = scipy.average(stock.requested_data['closing_price'][-5:])
    avgLast3days = scipy.average(stock.requested_data['closing_price'][-3:])
    avgOneWeekAgo = scipy.average(stock.requested_data['closing_price'][-10:-5])
    median2weeks = scipy.average(stock.requested_data['closing_price'][-10:])
    medianLastWeek = scipy.average(stock.requested_data['closing_price'][-5:])
    minPrice = (stock.requested_data['closing_price']).min()
    
    
    ## Do we want to buy the stock??
    # if true value is greater than current and smas line up and price crosses sma and the stock didnt go up like crazy in the past year OR short term growth is extremely fast
    if trueShortTermPrice>1.5*trueValue:
        boughtPrice = currentPrice
        dic['boughtPrice'] = boughtPrice
        dic['boughtPriceDescription'] = 0 ##'possibleBuyingPoint'
    else:
        boughtPrice = 0
        dic['boughtPrice'] = 0

# if true value is greater than current and smas line up and price crosses sma and the stock didnt go up like crazy in the past year
    if (boughtPrice ==0 and currentPrice<=trueValue and currentSMA100<=1.02*currentSMA200 and currentSMA200>stock.requested_data['closing_price'][-2] and currentSMA200<currentPrice and (minPrice*2>currentPrice or currentPrice<trueShortTermPrice*3)):
    	boughtPrice = currentPrice
    	dic['boughtPrice'] = boughtPrice
    	dic['boughtPriceDescription'] = 1 ##'realBuyingPoint'
    elif (boughtPrice ==0 and currentPrice<=trueValue and currentSMA200>stock.requested_data['closing_price'][-2] and currentSMA200<currentPrice and (minPrice*2>currentPrice or currentPrice<trueShortTermPrice*3)):
    	boughtPrice = currentPrice
    	dic['boughtPrice'] = boughtPrice
    	dic['boughtPriceDescription'] = 2 ##'secondBuyingPoint only if we sold with selling 2'        
        
    ## Do we want to sell the stock??
    # if change in fundamental or change in sma
    maxTrueValue = max([trueValue,trueShortTermPrice])

    if scipy.average(stock.sma1[-3:])<avgOneWeekAgo and scipy.average(stock.sma1[-3:])>avgLast3days and boughtPrice==0:
        sellPrice = currentPrice
        dic['sellPrice'] = sellPrice
        dic['sellingPriceDescription'] = 2 ##'MUST SELL
    elif (currentPrice>=maxTrueValue*1.2 and boughtPrice==0): # bad financials
        sellPrice = currentPrice
        dic['sellPrice'] = sellPrice   
        dic['sellingPriceDescription'] = 1 ##'MustSellPoint if negative'
    elif avgLastWeek<scipy.average(stock.sma2[-5:]) and medianLastWeek<scipy.average(stock.sma2[-5:]) and boughtPrice==0:
        sellPrice = currentPrice
        dic['sellPrice'] = sellPrice
        dic['sellingPriceDescription'] = 0 ##'possibleSellingPoint if profit >30% or 20%
    else:
        sellPrice = 0
        dic['sellPrice'] = 0
    dic['trueValue'] = trueValue*1.2
## Just in case changes are not real   
    changesQ2 = stock_f.avgRealFinancialsChange(2,RevenueChangeQ,ProfitChangeQ,EquityChangeQ,EpsChangeQ,CashChangeQ,RoiChangeQ,DebtChangeQ)
    changesY2 = stock_f.avgRealFinancialsChange(3,RevenueChangeY,ProfitChangeY,EquityChangeY,EpsChangeY,CashChangeY,RoiChangeY,DebtChangeY)
    if changesY2 != changesY:
        changesY = changesY2
        changesQ = changesQ2
            

     
## Making Json
    names = ['rev','prof','equi','eps','cash','roi','rev3','prof3','equi3',
             'eps3','cash3','roi3','revq','profq','equiq','epsq','cashq','roiq',
             'revq2','profq2','equiq2','epsq2','cashq2','roiq2','currentRoi']

    info = [RevenueChangeY[0],ProfitChangeY[0],EquityChangeY[0],EpsChangeY[0],CashChangeY[0],
            RoiChangeY[0],changesY[0],changesY[1],changesY[2],changesY[3],changesY[4],changesY[5],
            RevenueChangeQ[0],ProfitChangeQ[0],EquityChangeQ[0],EpsChangeQ[0],CashChangeQ[0],
            RoiChangeQ[0],changesQ[0],changesQ[1],changesQ[2],changesQ[3],changesQ[4],changesQ[5],
            stock_f.roi[0]*100]

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
    