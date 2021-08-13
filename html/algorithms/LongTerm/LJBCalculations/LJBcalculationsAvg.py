#!/usr/bin/env python
#FILE NAME: TechnicalAnalysisStock

## TECHNICAL ANALYSIS ALGORITHM
## CALCULATES ENTRY POINTS BASED ON SMAs, STOCHASTIC, and CROSSOVERS
## USE ONLY FOR STOCKS
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from financialObject import financialObject
from stockObject import stockObject
from core import *
import numpy as np
import json
import sys

def getStockType(tableName):
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT type from `items` WHERE tableName='%s'" %tableName
    cursor.execute(query)
    row = cursor.fetchone()
    description = row[0]

def sectorSorting(sector): ## CHECK WHEN ADDED TO DATABASE
    tablesBySector = []
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SELECT symbol from `items` WHERE sector='%s'" %sector
    cursor.execute(query)
    row = cursor.fetchone()
    while isinstance(row,tuple):
        tablesBySector.append(row[0])
        row = cursor.fetchone()

    return tablesBySector


def main():
    stocksList = sys.argv[1]
    isSector = int(sys.argv[2])# 0 = stock, 1 = sector
    dic ={}
    count =0
    if isSector == 1:
        stocksList = sectorSorting(stocksList)
    else:
        stocksList = stocksList.split()
    
    for i in stocksList:
        count+=1
         # creating stock object
        stock = stockObject()
         # creating financial object
        stock_f = financialObject()

        # Getting info from user
        stock.stock_name = i + '_1d'
        stock_f.name = i + '_f'
        stock.init_date = sys.argv[3] # start date
        stock.final_date = sys.argv[4] # end date

        stock_f.initdate = sys.argv[3]
        stock_f.finaldate = sys.argv[4]

        ## only need data of last year, we will get 2 years in case some is missing
        final_date = stock.final_date
        init_date = str(int(stock.final_date[:4]) - 2) + (stock.init_date[4:])

        ## Getting data we need for calculations
        stock.getData_df()
        stock_f.getFinancials('Revenue')
        stock_f.getFinancials('Profit')
        stock_f.getFinancials('Equity')
        stock_f.getFinancials('Cash')
        stock_f.getFinancials('Debt')

        if len(stock_f.revenue) >4 and len(stock_f.profit) >4 and len(stock_f.equity) >4 and len(stock_f.cash) >4 and len(stock_f.debt) >4:
            rev1Q = np.sum(stock_f.revenue[0])
            rev2Q = np.sum(stock_f.revenue[:2])
            rev1Y = np.sum(stock_f.revenue[:4])

            prof1Q = np.sum(stock_f.profit[0])
            prof2Q = np.sum(stock_f.profit[:2])
            prof1Y = np.sum(stock_f.profit[:4])
        
            equi1Q = (stock_f.equity[0])
            equi2Q = (stock_f.equity[1])
            equi1Y = (stock_f.equity[3])
        
            cash1Q = np.sum(stock_f.cash[0])
            cash2Q = np.sum(stock_f.cash[:2])
            cash1Y = np.sum(stock_f.cash[:4])

            debt1Q = (stock_f.debt[0])
            debt2Q = (stock_f.debt[1])
            debt1Y = (stock_f.debt[3])

             # GETTING currentPrice
            conn = dbConnection()
            cursor = conn.cursor()
            query = "SELECT close FROM %s WHERE date<='%s' AND date>='%s' ORDER BY date DESC" %(stock.stock_name,final_date,init_date)
            try:
                cursor.execute(query)
            except mysql.connector.ProgrammingError as err:
                sys.exit(["Something went wrong with db."])
            else:
                currentPrice = float(cursor.fetchone()[0])



            # GETTING MARKETCAP
            conn = dbConnection()
            cursor = conn.cursor()
            query = "SELECT value FROM %s WHERE type='MarketCap'AND date<='%s' AND date>='%s' ORDER BY date DESC" %(stock_f.name,final_date,init_date)
            try:
                cursor.execute(query)
            except mysql.connector.ProgrammingError as err:
                sys.exit(["Something went wrong with db."])
            else:
                marketCap = float(cursor.fetchone()[0])

            calculations = [[marketCap,debt1Y],[cash1Y,debt1Y],[rev1Y,prof1Y],[marketCap,rev1Y],[marketCap,prof1Y],[marketCap,1]]
        #LJBCalcList = ['marketCaptoLongTermDebt','cashToDebt','revenueToIncome','marketCaptoRevenue','marketCaptoIncome']
            position = 0
            if count==1: # create the avg counter in the first loop only
                avgCounter = np.zeros(len(calculations))
                allData = [0,0,0,0,0,0]
            calcArray = []
            for i in calculations:
                ## in case there is division by zero
                if i[1]!=0:
                    calcArray.append(i[0]/i[1])
                    avgCounter[position]= avgCounter[position]+1
                    position+=1
                    data = np.asarray(calcArray)
                else:
                    calcArray.append(0)
                    data = np.asarray(calcArray)

            data = np.asarray(calcArray)
            allData = data + allData

        ## Averaging all data using Avgcount
    allData = allData/avgCounter

    dic = {}
    json_table = [['Market Cap', 'Market Cap to Income', 'Market Cap to Revenue', 'Market Cap to Long Term Debt', 'Revenue To Income', 'Cash to Debt'], [allData[5], allData[4], allData[3], allData[0], allData[2], allData[1]]]
    dic['table'] = json_table
    info_json = json.dumps(dic)
    print(info_json)
    
    
if __name__ == '__main__':
    main()
    

    

    
    
