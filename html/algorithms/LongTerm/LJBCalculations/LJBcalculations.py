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
import scipy
import datetime
import numpy as np
from typing import Union, List, Dict
import json
import sys

def main():
    dic ={}
     # creating stock object
    stock = stockObject()
     # creating financial object
    stock_f = financialObject()

    # Getting info from user
    stock.stock_name = sys.argv[1] + '_1d'
    stock_f.name = sys.argv[1] + '_f'
    stock.init_date = sys.argv[2]
    stock.final_date = sys.argv[3]

    stock_f.initdate = sys.argv[2]
    stock_f.finaldate = sys.argv[3]

    ## only need data of last year, we will get 2 years in case some is missing
    final_date = stock.final_date
    init_date = str(int(stock.final_date[:4]) - 2) + (stock.init_date[4:])
    print

    ## Getting data we need for calculations
    stock.getData_df()
    stock_f.getFinancials('Revenue')
    rev1Q = np.sum(stock_f.revenue[0])
    rev2Q = np.sum(stock_f.revenue[:2])
    rev1Y = np.sum(stock_f.revenue[:4])

    stock_f.getFinancials('Profit')
    prof1Q = np.sum(stock_f.profit[0])
    prof2Q = np.sum(stock_f.profit[:2])
    prof1Y = np.sum(stock_f.profit[:4])

    stock_f.getFinancials('Equity')
    equi1Q = (stock_f.equity[0])
    equi2Q = (stock_f.equity[1])
    equi1Y = (stock_f.equity[3])

    stock_f.getFinancials('Cash')
    cash1Q = np.sum(stock_f.cash[0])
    cash2Q = np.sum(stock_f.cash[:2])
    cash1Y = np.sum(stock_f.cash[:4])

    stock_f.getFinancials('Debt')
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
    if debt1Y !=0 :
        marketCaptoLongTermDebt = marketCap/debt1Y
        cashToDebt = cash1Y/debt1Y
    else: # 99999 means NO DEBT
        marketCaptoLongTermDebt = 99999
        cashToDebt = 99999
    revenueToIncome = rev1Y/prof1Y
    marketCaptoRevenue = marketCap/rev1Y
    marketCaptoIncome = marketCap/prof1Y

    dic = {}
    json_table = [['Market Cap', 'Market Cap to Income', 'Market Cap to Revenue', 'Market Cap to Long Term Debt', 'Revenue To Income', 'Cash to Debt'], [marketCap, marketCaptoIncome, marketCaptoRevenue, marketCaptoLongTermDebt, revenueToIncome, cashToDebt]]
    dic['table'] = json_table
    info_json = json.dumps(dic)
    print(info_json)
    
    
if __name__ == '__main__':
    main()
    

    

    
    
