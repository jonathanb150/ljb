#!/usr/bin/env python
#FILE NAME: portfolioEmails.py
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from financialObject import financialObject
from stockObject import stockObject
from core import *
import pandas as pd
import numpy as np
import scipy
import json
import sys
import mysql.connector
import datetime


def jsonOutputforPHP(dic,names,info):
    counter = 0
    for i in names:
        dic[i] = info[counter]
        counter+=1
    return dic

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

def main():
    
    tableNames = sys.argv[1] # list of stock tableNames in portfolio
    tableNames = tableNames.split()
    targets = (sys.argv[2]).split()
    sellings = (sys.argv[3]).split()
    fundamentals = []
    dic = {}
    
    for i in range(len(tableNames)):
        stock = stockObject()
        stock.stock_name = tableNames[i]
        stock.final_date = todaysDate()
        stock.init_date = str(int(stock.final_date.split('-',1)[0])-4) + '-' + (stock.final_date.split('-',1)[1])# 4 years ago
        stock.getData_df()
        fundamentals.append(stock.all_data['closing_price'])

    counter = 0
    for i in fundamentals:
        temp = tableNames[counter]+'_within5%target'
        if (i[-1])*(1.05)>=int(targets[counter]) and (i[-1])*(0.95)<=int(targets[counter]):
            dic[temp] = True
            if (i[-1])*(1.01)>=int(targets[counter]) and (i[-1])*(0.99)<=int(targets[counter]):
                temp = tableNames[counter]+'_targetAchieved'
                dic[temp] = True    
        else:
            dic[temp] = False
            
            
        temp = tableNames[counter]+'_within5%selling'
        if (i[-1])*(1.05)>=int(sellings[counter]) and (i[-1])*(0.95)<=int(sellings[counter]):
            dic[temp] = True
            if (i[-1])*(1.01)>=int(sellings[counter]) and (i[-1])*(0.99)<=int(sellings[counter]):
                temp = tableNames[counter]+'_sellingAchieved'
                dic[temp] = True   
        else:
            dic[temp] = False
            
            
        temp = tableNames[counter]+'strongDrop'
        if (((i[-1])/i[-2])-1)*100<=-5:
            dic[temp] = (((i[-1])/i[-2])-1)*100 # percentage drop
        else:
            dic[temp] = False
            
            
        temp = tableNames[counter]+'strongRise'
        if (((i[-1])/i[-2])-1)*100>=5:
            dic[temp] = (((i[-1])/i[-2])-1)*100 # percentage rise 
        else:
            dic[temp] = False
            
        counter +=1
    
    info_json = json.dumps(dic)
    print(info_json)

if __name__ == '__main__':
    main()