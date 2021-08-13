#!/usr/bin/env python
##
from stockObject import stockObject
from financialObject import financialObject
from core import dbConnection
import plotly.plotly as py
import plotly
import plotly.graph_objs as go
from datetime import datetime, timedelta
import tensorflow as tf
import numpy as  np
import scipy
import pandas as pd
import matplotlib.pyplot as plt
import sys

#input stock_f, array of financial info to be analyzed (revenue,profit,cash,equity,eps, or debt) and pricevstime
#output array of analyzed financial info
def financialChangevsTime(stock_f,financialInfo,pricevsTime):
    counter =0
    for i in financialInfo:
        stock_f.getFinancials(i)
        if i == 'Revenue':
            data = np.zeros(len(stock_f.revenue))
            for x in range(len(data)-1):
                data[x+1]= (stock_f.revenue[x+1]/stock_f.revenue[x]-1)*100
            data = pd.DataFrame(data,(stock_f.revenue).index.values, columns = ['revenue_change'])
            data['revenue'] = stock_f.revenue
            financialInfo[counter] = data[1:]
            counter+=1
        elif i == 'Profit':
            data = np.zeros(len(stock_f.profit))
            for x in range(len(data)-1):
                data[x+1]= (stock_f.profit[x+1]/stock_f.profit[x]-1)*100
            data = pd.DataFrame(data,(stock_f.profit).index.values, columns = ["profit_change"])
            data['profit'] = stock_f.profit
            financialInfo[counter] = data[1:]
            counter+=1
        elif i == 'Cash':
            data = np.zeros(len(stock_f.cash))
            for x in range(len(data)-1):
                data[x+1]= (stock_f.cash[x+1]/stock_f.cash[x]-1)*100
            data = pd.DataFrame(data,(stock_f.cash).index.values, columns = ['cash_change'])
            data['cash'] = stock_f.cash
            financialInfo[counter] = data[1:]
            counter+=1
        elif i == 'Equity':
            data = np.zeros(len(stock_f.equity))
            for x in range(len(data)-1):
                data[x+1]= (stock_f.equity[x+1]/stock_f.equity[x]-1)*100
            data = pd.DataFrame(data,(stock_f.equity).index.values, columns = ['equity_change'])
            data['equity'] = stock_f.equity
            financialInfo[counter] = data[1:]
            counter+=1
        elif i == 'Eps':
            data = np.zeros(len(stock_f.eps))
            for x in range(len(data)-1):
                data[x+1]= (stock_f.eps[x+1]/stock_f.eps[x]-1)*100
            data = pd.DataFrame(data,(stock_f.eps).index.values, columns = ['eps_change'])
            data['eps'] = stock_f.eps
            financialInfo[counter] = data[1:]
            counter+=1
        elif i == 'Debt':
            data = np.zeros(len(stock_f.debt))
            for x in range(len(data)-1):
                data[x+1]= (stock_f.debt[x+1]/stock_f.debt[x]-1)*100
            data = pd.DataFrame(data,(stock_f.debt).index.values, columns = ["debt_change"])
            data['debt'] = stock_f.debt
            financialInfo[counter] = data[1:]
            counter+=1       
    financialsArray = addCorrespondingPrice(financialInfo,pricevsTime)
    return financialsArray

#inputs stockObject
#outputs price Change vs time  and price vs time of desired stock
def priceChangevsTime(stock):
    price = stock.requested_data['closing_price']
    dates = stock.requested_data.index.values
    priceChange = []
    for x in range(len(price)-1):
        change = float(price[x+1]/price[x])
        priceChange.extend([change]) 
    data = pd.Series(priceChange,dates[1:], name = 'closing_price')
    

    return data,price
## Input an array with dataframes of financial info, and a dataframe with price vs time info
## output : attaches corresponding price vs time to financial info and returns an array
## containing all Dataframes
def addCorrespondingPrice(financialsArray,pricevsTime):
    for i in financialsArray:
        i['closing_price']=float(0)
        counter = 0
        for x in range(len(i)):
            temp_date = i.index.values[counter]
            ## checking if date exists
            result = False
            while result is False:
                try:
                    realDate = pricevsTime.index.get_loc(temp_date)
                    result = True
                except KeyError:
                    temp_date = temp_date -timedelta(days=1)
            else:
                temp_price = float(pricevsTime[realDate])
                i['closing_price'][counter] = temp_price
                counter+=1
    return financialsArray

#input: array of financial arrays (must contain 6 dataframes) to plot and i. i: 0= plot change , i: 1= plot numbers
def plotFinancial(financialInfoArray,i):    
    
    fig = plotly.tools.make_subplots(rows=3, cols=2)
    
    #data
    trace0 = financialInfoArray[0].columns[i]
    trace1 = financialInfoArray[1].columns[i]
    trace2 = financialInfoArray[2].columns[i]
    trace3 = financialInfoArray[3].columns[i]
    trace4 = financialInfoArray[4].columns[i]
    trace5 = financialInfoArray[5].columns[i]
    
    trace0 = go.Scatter(x = financialInfoArray[0][trace0] , y = financialInfoArray[0]['closing_price'],
                        mode = 'markers', name = '%s' %trace0)
    trace1 = go.Scatter(x = financialInfoArray[1][trace1] , y = financialInfoArray[1]['closing_price'],
                        mode = 'markers',name = '%s' %trace1)
    trace2 = go.Scatter(x = financialInfoArray[2][trace2] , y = financialInfoArray[2]['closing_price'],
                        mode = 'markers',name = '%s' %trace2)
    trace3 = go.Scatter(x = financialInfoArray[3][trace3] , y = financialInfoArray[3]['closing_price'],
                        mode = 'markers',name = '%s' %trace3)
    trace4 = go.Scatter(x = financialInfoArray[4][trace4] , y = financialInfoArray[4]['closing_price'],
                        mode = 'markers',name = '%s' %trace4)
    trace5 = go.Scatter(x = financialInfoArray[5][trace5] , y = financialInfoArray[5]['closing_price'],
                        mode = 'markers',name = '%s' %trace5)

    
    data = [trace0,trace1,trace2,trace3,trace4,trace5]
    
    fig.append_trace(data[0], 1, 1)
    fig.append_trace(data[1], 1, 2)
    fig.append_trace(data[2], 2, 1)
    fig.append_trace(data[3], 2, 2)
    fig.append_trace(data[4], 3, 1)
    fig.append_trace(data[5], 3, 2)

    fig['layout']['title'] = 'Financials vs Price'
    fig['layout']['showlegend'] = True
    fig['layout'].update(height=1000, width=1000)
    
    plotly.offline.plot(fig, filename='plot.html')

def machineLearningAlgorithm(input_X,input_Y):
    # training Data
    train_X = np.asarray(input_X['revenue'])
    train_Y = np.asarray(input_Y)
    print(train_X)
    print(train_Y)
    print(train_X.shape)
    #algorithm parameters
    learning_rate = 0.1
    training_epochs = 1000
    display_step = 200
    # Data Set Paramaters
    n_features = 1
    numLabels = 20
    
    # Output layer
    X = tf.placeholder(tf.float32) ## input
    Y = tf.placeholder(tf.float32) ## output

    # Output layer
    W = tf.Variable(tf.random_normal([numLabels, n_features]), name="weights")
    b = tf.Variable(tf.random_normal([n_features]), name="biases")

    
    # Construct a linear model
    pred = tf.add(tf.multiply(X, W), b)
    
    # Mean squared error
    cost = tf.reduce_sum(tf.pow(pred-Y, 2))/(2*n_features)
    
    # Gradient descent
    #  Note, minimize() knows to modify W and b because Variable objects are trainable=True by default
    optimizer = tf.train.GradientDescentOptimizer(learning_rate).minimize(cost)
    
    # Initialize the variables
    init = tf.global_variables_initializer()
    
    # Start training
    with tf.Session() as sess:
        # Run the initializer
        sess.run(init)
    
        # Fit all training data
        for epoch in range(training_epochs):
            for (x, y) in zip(train_X, train_Y):
                sess.run(optimizer, feed_dict={X: x, Y: y})

        # Display logs per epoch step
            if (epoch+1) % display_step == 0:
                c = sess.run(cost, feed_dict={X: train_X, Y:train_Y})
                print("Epoch:", '%04d' % (epoch+1), "cost=", "{:.9f}".format(c), \
                    "W=", sess.run(W), "b=", sess.run(b))
    
        # Graphic display
        fig = plt.figure()
        plt.plot(train_X, train_Y, 'ro', label='Original data')
        plt.plot(train_X, sess.run(W) * train_X + sess.run(b), label='Fitted line')
        plt.legend()
        fig.savefig('optimization.png')
    
def main():
    stock = stockObject()
    name = sys.argv[1]
    stock.init_date = sys.argv[2]
    stock.final_date = sys.argv[3]
    stock.stock_name = name+'_1d'
    
    # getting stock price info
    stock.getData_df()
    (priceChange_vsTime,pricevsTime) = priceChangevsTime(stock)
    dates = pricevsTime.index.values
    
    #getting financial info
    stock_f = financialObject()
    stock_f.name = name+'_f'
    financialArray = financialChangevsTime(stock_f,['Revenue','Profit','Cash','Equity','Eps','Debt'],pricevsTime)
    # creating financial arrays with all info needed 
    revvsTime = financialArray[0]
    profvsTime = financialArray[1]
    cashvsTime = financialArray[2]
    equivsTime = financialArray[3]
    epsvsTime =  financialArray[4]
    debtvsTime = financialArray[5]
    #plotting data
    #plotdata = [revvsTime,profvsTime,cashvsTime,equivsTime,epsvsTime,debtvsTime]
    #plotFinancial(plotdata,1)
    
    #rearranging data for model .Using last 5 years
    years = 5*4
    datax = pd.DataFrame()
    datax['eps']=epsvsTime['eps'][:years]
    datax['revenue']=revvsTime['revenue'][:years]
    datax['profit']=profvsTime['profit'][:years]
    datax['cash']=cashvsTime['cash'][:years]
    datax['equity']=equivsTime['equity'][:years]
    datax['debt']=debtvsTime['debt'][:years]

    datay = pd.DataFrame()
    datay['eps']=epsvsTime['closing_price'][:years]
    datay['revenue']=revvsTime['closing_price'][:years]
    datay['profit']=profvsTime['closing_price'][:years]
    datay['cash']=cashvsTime['closing_price'][:years]
    datay['equity']=equivsTime['closing_price'][:years]
    datay['debt']=debtvsTime['closing_price'][:years]
    
    #rearranging data for model
    train_X = datax
    train_Y = datay['eps']/datay['eps'].max()
# input --> weights --> hidden l1 (func)--> weights --> hidden l2 (func)--> etc..
# compare output and intended output (cost)
# optimize cost --> optimizer
#back propagation 
##--> feed forward + backprop = epoch

  
    #Dividing everything over max value
    train_X['eps'] = train_X['eps']/train_X['eps'].max()
    train_X['revenue'] = train_X['revenue']/train_X['revenue'].max()
    train_X['profit'] = train_X['profit']/train_X['profit'].max()
    train_X['cash'] = train_X['cash']/train_X['cash'].max()
    train_X['equity'] = train_X['equity']/train_X['equity'].max()
    train_X['debt'] = train_X['debt']/train_X['debt'].max()

    
    machineLearningAlgorithm(train_X,train_Y)


if __name__=='__main__':
    main()