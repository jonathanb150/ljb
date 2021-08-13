#!usr/bin/env python3
#FILENAME: sorting stocks companies
#INPUT: number of companies, sort by financial, analyze number of years, by financial/price
##prints sorted stocks

import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from financialObjectNoPe import financialObject
from stockObject import stockObject
from core import *
import pandas as pd
import numpy as np
import scipy
import json
import sys
import mysql.connector

def main():
    #inputs
    name = sys.argv[1]
    finaldate = sys.argv[2]
    
    stock_f = financialObject()
    stock_f.name = name + '_f'
    stock_f.finaldate = finaldate
    stock_f.initdate = str(int(finaldate[:4]) - 4) + finaldate[4:]
    
    stock_f.getFinancials('Eps')
    pe = stock_f.currentPeRatio()
    
    dic = {}
    dic['peToday'] = round(pe[0],2)
    dic['pe1YAvg'] = round(pe[1],2)
    dic['pe3YAvg'] = round(pe[2],2)
    
    dic = json.dumps(dic)
    print(dic)

if __name__ == '__main__':
    main()