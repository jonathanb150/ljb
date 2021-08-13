#!/usr/bin/env python
#FILE NAME: getShares.py

import numpy as np
import pandas as pd
import scipy
import datetime
import sys,os, json
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
import mysql.connector
import core
from statisticalAnalysisPackage import *
from financialObject import financialObject


def getShares(symbol):
    stock = financialObject()
    stock.name = symbol
    stock.finaldate = todaysDate()
    stock.initdate = datetime.datetime.strptime(todaysDate(),'%Y-%m-%d') - timedelta(days=365*60)

    stock.getFinancials('EPS', 'Profit')

    data = pd.DataFrame({'EPS':stock.eps,'Profit': stock.profit})

    data['Shares'] = 1/(data['EPS']/data['Profit'])
    mask = data['Shares']<0
    outlier = data['Shares'][mask].index.values
    data.loc[outlier] = float("NaN")

    data = data.dropna()
    shares = data['Shares']

    return shares


def main():
    symbol = sys.argv[1]
    shares = getShares(symbol)
    date_strings = [dt.strftime("%Y-%m-%d") for dt in shares.index.values]
    table = [date_strings,shares.values.tolist()]
    dic = {}
    dic['table'] = table
    info =json.dumps(dic)
    print(info)

if __name__ == '__main__':
    main()

