#!usr/bin/env python3
#FILENAME: sortingStocks.py
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

def jsonOutput(sorted_tables,sorted_data):
## Variables to Return to PHP
    dic = {}
    info_header = ['stocks','change']
    info = [sorted_tables,sorted_data]
    for x in range(len(info)):
        dic[info_header[x]] = info[x]

    info_json =json.dumps(dic)
    return info_json

def sortingData(financialChanges,tables):   
    sorted_data = sorted(financialChanges, reverse = True)
    #rearrenging tables
    sorted_tables = []
    counter = 0
    for i in sorted_data:
        for x in range(len(tables)):
            if i == financialChanges[counter]:
                sorted_tables.append(tables[counter])
                counter = 0
                break
            else:
                counter+=1
    return sorted_data,sorted_tables

def checkingInput(financialOrPrice):
    if financialOrPrice=='price':
        return 'price'
    elif financialOrPrice=='trueValue':
        return 'trueValue'
    else:
        return 'finance'
        
## sorts companies by biggest financial in the  present
def biggestCompaniesbyFinancial(tables_name,financial,numberOfCompanies,init_date,final_date):
    quarter = 4
    financialData =[]
    counter = 0
    tables_name1 = list(tables_name) # copying list
    for i in tables_name1:
        conn = dbConnection()
        cursor = conn.cursor()
        try:
            query = ("SELECT * FROM `%s` WHERE type='%s' AND date<='%s' AND date>='%s' ORDER BY date DESC" % (i[0],financial,final_date,init_date))
            cursor.execute(query)
        except:
            counter+=1
            i = tables_name1[counter]
        else:
            row = cursor.fetchone()
            if row is None: # in case there is no financial info
                info = np.asarray(0)
            else:
                info = np.array(range(len(row)))
            while isinstance(row,tuple):
                arr = np.array(list(row))
                info = np.vstack([info,arr])
                row = cursor.fetchone()
                if info.shape[0]>quarter:
                    if info.any():
                        info = pd.DataFrame(info[1:,:])
                        del info[0]
                        header = {1:'dates',2:'closing_price'}
                        info.rename(columns=header,inplace=True)
                        info.set_index('dates', inplace=True)
                        info = pd.to_numeric(info['closing_price'])
                        if financial != 'MarketCap' or financial != 'Equity':
                            financialData.append(scipy.sum(info[-4:]))
                        else:
                            financialData.append(info[-1])
                        counter+=1
                        break
                    else:
                        info = np.asarray(0) 
    ## not enough info for desired stock, delete from table_name
        if isinstance(info,np.ndarray):   
            tables_name.pop(counter)  
    if len(tables_name)<numberOfCompanies:
        numberOfCompanies = len(tables_name)
            
    sorted_data,sorted_tables =sortingData(financialData,tables_name)
        
             
    return sorted_data[0:numberOfCompanies],sorted_tables[0:numberOfCompanies]

#sort companies by financial in the last x years
def financialChangeSort(tables_name,financial,years,init_date,final_date):
    init_date = str(int(init_date[:4])-1)+ init_date[4:]
    quarters = 4*years
    financialData =[]
    counter = 0
    tables_name1 = list(tables_name) # copying list
    for i in tables_name1:
        conn = dbConnection()
        cursor = conn.cursor()
        query = ("SELECT * FROM `%s` WHERE type='%s' AND date<='%s' AND date>='%s' ORDER BY date DESC" % (i[0],financial,final_date,init_date) )
        cursor.execute(query)
        row = cursor.fetchone()
        if row is None: # in case there is no financial info
            info = np.asarray(0)
        else:
            info = np.array(range(len(row)))
        while isinstance(row,tuple):
            arr = np.array(list(row))
            info = np.vstack([info,arr])
            row = cursor.fetchone()
            if info.shape[0]>quarters+4:
                if info.any():
                    info = pd.DataFrame(info[1:,:])
                    del info[0]
                    header = {1:'dates',2:'closing_price'}
                    info.rename(columns=header,inplace=True)
                    info.set_index('dates', inplace=True)
                    info = pd.to_numeric(info['closing_price'])
                    change = ((scipy.sum(info[:4])/scipy.sum((info[quarters:quarters+4]))-1))*100
                    financialData.append(change)
                    counter+=1
                    break
                else:
                    info = np.asarray(0)
    ## not enough info for desired stock, delete from table_name
        if isinstance(info,np.ndarray):   
            tables_name.pop(counter)      
    sorted_data,sorted_tables =sortingData(financialData,tables_name)
        
             
    return sorted_data,sorted_tables

def financialGrowthSort(tables_name,final_date):
    financialData =[]
    counter = 0
    tables_name1 = list(tables_name) # copying list
    for i in tables_name1:
        stock_f = financialObject()
        stock_f.finaldate = final_date
        stock_f.name = i[0]
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
        (RevenueChangeY,ProfitChangeY,EquityChangeY,EpsChangeY,CashChangeY,RoiChangeY,DebtChangeY) = stock_f.financialChanges(4)
        changesY = stock_f.avgFinancialsChange(3,RevenueChangeY,ProfitChangeY,EquityChangeY,EpsChangeY,CashChangeY,RoiChangeY,DebtChangeY)
        if changesY == 'Not Enough Info':
            sys.exit(['Not Enough Financial Info'])
            tables_name.pop(counter)
        else:
            stock_f.analyzeGrowthCompany(3,100,changesY[0],changesY[1],changesY[2],changesY[4])
            financialData.append(stock_f.expectedValue)
            counter+=1


    sorted_data,sorted_tables =sortingData(financialData,tables_name)
    return sorted_data,sorted_tables
    
#sort companies by change in price in the last x years
def priceChangeSort(tables_name,init_date,final_date):
    financialData =[]
    counter = 0
    tables_name1 = list(tables_name) # copying list
    for i in tables_name1:
        conn = dbConnection()
        cursor = conn.cursor()
        query = "SELECT date,close FROM %s WHERE date<='%s' AND date>='%s' ORDER BY date DESC" %(i,final_date,init_date)
        try:
            cursor.execute(query)
        except mysql.connector.ProgrammingError as err:
            tables_name.pop(counter)
        else:
            row = cursor.fetchone()

            if row[1]==None or row[1]=='': # in case there is no price info
                info = [0] # no info
            else:
                info = np.array(range(len(row)))
            while isinstance(row,tuple):
                if len(info)>1:
                    arr = np.array(list(row))
                    info = np.vstack([info,arr])
                    row = cursor.fetchone()
                else:
                	break
     ## not enough info for desired stock, delete from table_name               
            if len(info)<=1:#no info
                tables_name.pop(counter)
            else:
                info = pd.DataFrame(info[1:,:])
                del info[0]
                info = pd.to_numeric(info[1])
                change = ((info.iloc[0]/info.iloc[-1])-1)*100
                financialData.append(change)
                counter+=1
    sorted_data,sorted_tables =sortingData(financialData,tables_name)
        
             
    return sorted_data,sorted_tables

# gives all tables ending in input str
# NOT USING IT NOW
def showTables():
    ending = '_f'
    conn = dbConnection()
    cursor = conn.cursor()
    query = "SHOW TABLES"
    cursor.execute(query)
    row = cursor.fetchone()
    info = np.array(range(len(row)))
    while isinstance(row,tuple):
        arr = np.array(list(row))
        if arr[0][-2:] == '%s' %ending:
            info = np.vstack([info,arr])
            row = cursor.fetchone()
        else:
            row = cursor.fetchone()
    info = np.delete(info,0,0)
    info = info.tolist()
    return info

def sectorSorting(sector): ## CHECK WHEN ADDED TO DATABASE
    tablesBySector = []
    if sector != 'all':
        conn = dbConnection()
        cursor = conn.cursor()
        query = "SELECT symbol from `items` WHERE sector='%s'" %sector
        cursor.execute(query)
        row = cursor.fetchone()
        while isinstance(row,tuple):
            tablesBySector.append([row[0]+'_f'])
            row = cursor.fetchone()
    
        return tablesBySector
    else:
        tables = showTables()
            
        return tables
    
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
    
## sorts companies by financial or price change over x number of years
## input : sort:  numberOfCompanies, by : 'financial' or 'price', in sector:'sector', order by :change in: financial , in last : 'numberofYears',
## sector input is optional --> sector is passed as a number, zero is equal to no sector selected
def main():
    #inputs
    numberOfCompanies = int(sys.argv[1])
    financial = sys.argv[2]
    sector = sys.argv[3]
    financial2OrPrice = sys.argv[4]
    years = int(sys.argv[5])
    init_date = (sys.argv[6]) #not important for trueValue
    final_date =(sys.argv[7])
    InputChecked = checkingInput(financial2OrPrice)
    
    stock_f = financialObject()
    stock_f.initdate = init_date
    stock_f.finaldate = final_date
    if InputChecked == 'finance':
        tablesBySector = sectorSorting(sector)
        sorted_data,sorted_tables = biggestCompaniesbyFinancial(tablesBySector,financial,numberOfCompanies,init_date,final_date)
        sorted_data,sorted_tables = financialChangeSort(sorted_tables,financial2OrPrice,years,init_date,final_date)
        for i in range(len(sorted_tables)):
            sorted_tables[i] = sorted_tables[i][0]
    elif InputChecked == 'price':
        tablesBySector = sectorSorting(sector)

        sorted_data,sorted_tables = biggestCompaniesbyFinancial(tablesBySector,financial,numberOfCompanies,init_date,final_date)

        for i in range(len(sorted_tables)):
            sorted_tables[i]=sorted_tables[i][0][:-2]+'_1d'
        sorted_data,sorted_tables =priceChangeSort(sorted_tables,init_date,final_date)
    elif InputChecked == 'trueValue':
        tablesBySector = sectorSorting(sector)
        sorted_data,sorted_tables = biggestCompaniesbyFinancial(tablesBySector,financial,numberOfCompanies,init_date,final_date)
        sorted_data,sorted_tables = financialGrowthSort(sorted_tables,final_date)
        for i in range(len(sorted_tables)):
            sorted_tables[i] = sorted_tables[i][0]
        
    #PHP Output/JSON

    info_json = jsonOutput(sorted_tables,sorted_data)
    print(info_json)

    return sorted_data,sorted_tables

if __name__=='__main__':
    main()
