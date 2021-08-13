#!usr/bin/env python3
#FILENAME: peRatioHistorical.py
#INPUT: returns yearly and historical avg pe Ratio in the last 12 years or available timeframe
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from financialObjectNoPe import financialObject
from stockObject import stockObject
import numpy as np
import scipy
import json
import datetime
from core  import dbConnection

def main():
#getting last X number of years
    years = 12
    dates = getDesiredDates(years)
# Creating stock to be analyzed
    stock_f = financialObject()
    stock_f.name = sys.argv[1] +'_f'
    stock_f.initdate = dates[0]
    stock_f.finaldate = dates [1]

# Getting yearly EPS for the last X years
    eps,eps_years = getYearlyEps(stock_f, dates)
# Getting yearly avg prices
    prices = getAvgYearlyPrice(stock_f)
# Calculating yearly PE ratios
    pe_ratios = getPeRatios(eps,prices)
    avg_pe = round(np.average(pe_ratios),2)
    median_pe = round(np.median(pe_ratios),2)
# Putting everything together and dumping
    header = ['Date','Pe Ratio']
    if len(eps)<=len(prices):
        loop = eps
    else:
        loop = prices
    table = []
    for x in range(len(loop)):
        temp = [str(eps_years[x]),pe_ratios[x]]
        table.append(temp)

    table.append(['AVG',avg_pe])
    table.append(['Median',median_pe])
    dic ={}
    table.insert(0,header)
    dic['table'] = table
 
    dic = json.dumps(dic)
    print(dic)

def todaysDate():
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
    return init_date

def getDesiredDates(years):
    final = todaysDate()
    initial = str(int(final[:4])-years) + final[4:]

    dates = [initial,final]

    return dates

def getYearlyEps(stock_f,dates):
    stock_f.getFinancials('Eps')
    # calculating yearly eps
    eps = []
    years = []
    c = 0
    for x in range(int(np.floor(len(stock_f.eps)/4))):
        eps.append(scipy.sum(stock_f.eps[c:c+4]))
        years.append(stock_f.eps.index.values[c])
        c+= 4
    return eps,years

def getAvgYearlyPrice(stock_f):

    #requesting price info
    name = stock_f.name[:-2] + '_1d'
    final = stock_f.finaldate
    init = stock_f.initdate
    db = dbConnection()
    cursor = db.cursor()
    query = ("SELECT close FROM %s WHERE date>='%s'AND date<='%s' ORDER BY date DESC" %(name,init,final))
    price =[]
    cursor.execute(query)
    temp_price = cursor.fetchone()
    while isinstance(temp_price,tuple):
        price.append(float(temp_price[0]))
        temp_price = cursor.fetchone()

    trading_days = 253
    years = int(np.floor(len(price)/trading_days))
    avg_prices = []
    c = 0
    for x in range(years):
        temp = np.average(price[c:c+trading_days])
        avg_prices.append(temp)
        c+= trading_days

    return avg_prices

def getPeRatios(eps,avg_prices):
    if len(eps)<=len(avg_prices):
        loops = len(eps)
    else:
        loops = len(avg_prices)
#Calculating Pe Ratio
    pe_ratios = []
    for x in range(loops):
        temp = round(avg_prices[x]/eps[x],2)
        pe_ratios.append(temp)

    return pe_ratios

if __name__ == '__main__':
    main()

