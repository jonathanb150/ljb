#!/usr/bin/env python
import pandas as pd
import pandas_datareader as pdr 
import sys
import urllib.request
import json
import os
import math


def main():
	#ticker = 'https://stooq.com/ --> access that website to see everything that is available
    ticker = 'https://stooq.com/q/d/l/?s='+ sys.argv[1]
    fname = sys.argv[1]+'.csv'
    file = urllib.request.urlretrieve(ticker,fname)
    df = pd.read_csv(fname)
    year = 253 # trading days
    ## getting the values we need
    current_price = df['Close'].iloc[-1]
    
    today = round((current_price - df['Close'].iloc[-2]) / df['Close'].iloc[-2]*100,2)
    oneweek = round((current_price - df['Close'].iloc[-5]) / df['Close'].iloc[-5]*100,2)
    onemonth = round((current_price - df['Close'].iloc[math.floor(-year/12)]) / df['Close'].iloc[math.floor(-year/12)]*100,2)
    threemonths = round((current_price - df['Close'].iloc[math.floor(-year/4)]) / df['Close'].iloc[math.floor(-year/4)]*100,2)
    sixmonth = round((current_price - df['Close'].iloc[math.floor(-year/2)]) / df['Close'].iloc[math.floor(-year/2)]*100,2)
    oneyear = round((current_price - df['Close'].iloc[-year]) / df['Close'].iloc[-year]*100,2)
    threeyears = round((current_price - df['Close'].iloc[-year*3]) / df['Close'].iloc[-year*3]*100,2)
    
    os.remove(fname)
    # printing output
    table = [['TIME','TODAY','1 WEEK','1 MONTH', '3 MONTHS', '6 MONTHS', '1 YEAR', '3 YEARS'],['CHANGE',today,oneweek,onemonth,threemonths,sixmonth,oneyear,threeyears]]
    dic = {}
    dic['table'] = table
    out = json.dumps(dic)
    print(out)
    
if __name__ == '__main__':
    main()