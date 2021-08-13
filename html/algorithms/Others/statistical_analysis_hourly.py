#!/usr/bin/env/ python
#filename: statistical_backtesting_hourly.py
import sys
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
from statisticalAnalysisPackage import historical_changes_hourly,list_todf,statistics,getData_fromdb
import numpy as np
import pandas as pd
import math
import os,datetime,io
import json



def process_data(all_data):
    table = ['Hourly POS AVG Change', 'Hourly NEG AVG Change', '4hours POS AVG Change','4hours NEG AVG Change', 
    '8hours POS AVG Change', '8hours NEG AVG Change','12hours POS AVG Change', '12hours NEG AVG Change',
             'POS/NEG Days','Daily Outliers POS/NEG']
    table2_header = ['Hourly POS AVG Change', 'Hourly POS AVG Std', 'Hourly NEG AVG Change', 'Hourly NEG AVG Std',
    '4hours POS AVG Change', '4hours POS AVG Std','4hours NEG AVG Change', '4hours NEG AVG Std',
    '8hours POS AVG Change', '8hours POS AVG Std','8hours NEG AVG Change', '8hours NEG AVG Std',
    '12hours POS AVG Change','12hours POS AVG Change', '12hours NEG AVG Change','12hours NEG AVG Change',
    'POS/NEG Days']
    table2 = []
    results = []
    i = 0
    for data in all_data:

        values_sorted = data.sort_values('close')

        positive_sorted = (values_sorted[values_sorted>0]).dropna()
        negative_sorted = (values_sorted[values_sorted<0]).dropna()

        neg_avg = round(np.average(negative_sorted['close']),4)
        neg_std = round(np.std(negative_sorted['close']),4)
        pos_avg = round(np.average(positive_sorted['close']),4)
        pos_std = round(np.std(positive_sorted['close']),4)

        total_pos = len(positive_sorted)
        total_neg = len(negative_sorted)

        strong_neg = negative_sorted[negative_sorted<(neg_avg-neg_std)].dropna()
        strong_pos = positive_sorted[positive_sorted>(pos_avg+pos_std)].dropna()

        rest_neg = negative_sorted[negative_sorted>(neg_avg-neg_std)].dropna()
        rest_neg = rest_neg[rest_neg<(neg_avg+neg_std)].dropna()
        rest_pos = positive_sorted[positive_sorted<(pos_avg+pos_std)].dropna()
        rest_pos = rest_pos[rest_pos>(pos_avg-pos_std)].dropna()	
        
        table2.extend([pos_avg, pos_std, neg_avg, neg_std])

        results.extend([str(pos_avg)+'+-'+str(pos_std), str(neg_avg)+'+-'+str(neg_std)])

        if i==0:
            ratio = [str(round(total_pos/total_neg,4))]
            outliers =  [str(len(strong_neg))+'/'+str(len(strong_pos))]
            table3 = [total_pos,total_neg]
            i+=1

    results.extend(ratio)
    results.extend(outliers)

    table2.extend([float(ratio[0])])

    return ([table,results],[table2_header,table2],table3)

# def boxplot(all_data):
#     data_plot = []
#     visibility = True
#     for data in all_data:
    
#         values_sorted = data.sort_values('close')
#         positive_sorted = (values_sorted[values_sorted>0]).dropna()
#         negative_sorted = (values_sorted[values_sorted<0]).dropna()

#         trace1 = go.Box(y = positive_sorted['close'], boxpoints = 'all', name='Positive', visible= visibility)
#         trace2 = go.Box(y = negative_sorted['close'], boxpoints = 'all', name='Negative', visible= visibility)
#         trace3 = go.Box(y = values_sorted['close'], boxpoints = 'suspectedoutliers', name='AVG', visible= visibility)
#         if visibility==True:
#             visibility = False

#         data_plot.append(trace1)
#         data_plot.append(trace2)
#         data_plot.append(trace3)

#     updatemenus=list([
#         dict(
#             buttons=list([
#                 dict(
#                     args=[{'visible': [True,True,True,False,False,False,False,False,False,False,False,False]}],
#                     label='Hourly',
#                     method='update',
#                 ),   
#                 dict(
#                     args=[{'visible': [False,False,False,True,True,True,False,False,False,False,False,False]}],
#                     label='4Hours',
#                     method='update',
#                 ),
#                 dict(
#                     args=[{'visible': [False,False,False,False,False,False,True,True,True,False,False,False]}],
#                     label='8Hours',
#                     method='update',
#                 ),
#                 dict(
#                     args=[{'visible': [False,False,False,False,False,False,False,False,False,True,True,True,]}],
#                     label='12Hours',
#                     method='update',
#                 ),
#             ]),
#             direction = 'down',
#             pad = {'r': 10, 't': 10},
#             showactive = True,
#             x = 0.145,
#             xanchor = 'left',
#             y = 1.14,
#             yanchor = 'top' 
#         ),
#     ]) 

#     layout = go.Layout(yaxis= dict(title='Percentage Change'))
#     fig = go.Figure(data=data_plot,layout=layout)
#     fig['layout']['updatemenus'] = updatemenus
#     fig['layout']['showlegend'] = False
    
#     fname = '/var/www/ljb.solutions/html/graphs/'+'pre_backtestingGraph_boxplot.html' 
#     plotly.offline.plot(fig, filename = fname)

# def scatterplot(all_data,price_data, fname ='/var/www/ljb.solutions/html/graphs/'+'pre_backtestingGraph_scatter.html'):
#     data_plot = []
#     visibility = True
#     for data in all_data:
    
#         values_sorted = data.sort_values('close')
#         positive_sorted = (values_sorted[values_sorted>0]).dropna()
#         negative_sorted = (values_sorted[values_sorted<0]).dropna()

#         trace1 = go.Scatter(x = positive_sorted.index.values, y = positive_sorted['close'], mode = 'markers',
#         marker = dict(size=12, color='rgba(8, 246, 8, .8)',line=dict(width=2)), name = 'Positive', visible= visibility)

#         trace2 = go.Scatter(x = negative_sorted.index.values, y = negative_sorted['close'], mode = 'markers',
#         marker = dict(size=12, color='rgba(247, 19, 27, .8)',line=dict(width=2)), name = 'Negative', visible = visibility)
        
#         if visibility == True:
#             visibility = False
#         data_plot.append(trace1)
#         data_plot.append(trace2)

#     trace3 = go.Scatter(x = price_data.index.values, y = price_data['closing_price'], name = 'Stock Price', yaxis='y2')
#     data_plot.append(trace3)

#     updatemenus=list([
#         dict(
#             buttons=list([
#                 dict(
#                     args=[{'visible': [True,True,False,False,False,False,False,False,True]}],
#                     label='Hourly',
#                     method='update',
#                 ),   
#                 dict(
#                     args=[{'visible': [False,False,True,True,False,False,False,False,True]}],
#                     label='4Hours',
#                     method='update',
#                 ),
#                 dict(
#                     args=[{'visible': [False,False,False,False,True,True,False,False,True]}],
#                     label='8Hours',
#                     method='update',
#                 ),
#                 dict(
#                     args=[{'visible': [False,False,False,False,False,False,True,True,True]}],
#                     label='12Hours',
#                     method='update',
#                 ),
#             ]),
#             direction = 'down',
#             pad = {'r': 10, 't': 10},
#             showactive = True,
#             x = 0.145,
#             xanchor = 'left',
#             y = 1.14,
#             yanchor = 'top' 
#         ),
#     ]) 

#     layout = go.Layout(yaxis= dict(title='Percentage Change'), yaxis2=dict(title='Price', overlaying ='y',side ='right', showgrid=False))
#     fig = go.Figure(data=data_plot,layout=layout)
#     fig['layout']['updatemenus'] = updatemenus
#     fig['layout']['showlegend'] = True
    
#     plotly.offline.plot(fig, filename = fname)

# def piechart(data):

#     labels = ['Positive','Negative']
#     values = data

#     trace = go.Pie(labels=labels, values=values,hoverinfo='label+percent', textinfo='value', textfont=dict(size=20),
#     marker=dict(line=dict(color='#000000', width=2)))

#     layout = go.Layout(title = 'Days Distribution')
#     fig = go.Figure(data=[trace],layout=layout)

#     fname = '/var/www/ljb.solutions/html/graphs/'+'pre_backtestingGraph_days.html'
#     plotly.offline.plot(fig, filename = fname)

def clean_outliers(all_data,price_data):
    data_plot = []
    visibility = True
    i = 1

    table2_header =[['Hourly POS AVG Change', 'Hourly POS Variance', 'Hourly NEG AVG Change', 'Hourly NEG Variance',
    '8Hours POS AVG Change', '8Hours POS Variance','12Hours NEG AVG Change', '12Hours NEG Variance','Last 30 POS/NEG Hours'],[]]
    
    results = []

    for data in all_data:
# We will never be able to predict huge daily movements, so we want to filter them out 
        if i==1:
            temp = data[-30:]
            pos = len(temp[temp['close']>0])
            neg = len(temp[temp['close']<0])
            days = str(pos)+':'+str(neg)


        values_sorted = data.sort_values('close')
        values_sorted = values_sorted[values_sorted.notnull()]

        positive_sorted = (values_sorted[values_sorted>0])
        avg_pos = np.mean(positive_sorted)
        var_pos = np.var(positive_sorted)
        positive_sorted = positive_sorted[positive_sorted<=avg_pos*1.15]
        avg_pos_real = np.mean(positive_sorted['close'])
        var_pos_real = np.var(positive_sorted['close'])
        positive_outliers = values_sorted[values_sorted>avg_pos*1.15]

        negative_sorted = (values_sorted[values_sorted<0])
        avg_neg = np.mean(negative_sorted)
        var_neg = np.var(negative_sorted)
        negative_sorted = negative_sorted[negative_sorted>=avg_neg*1.15]
        avg_neg_real = np.mean(negative_sorted['close'])
        var_neg_real = np.var(negative_sorted['close'])
        negative_outliers = values_sorted[values_sorted<avg_neg*1.15]
        
#         if i==2 or i==4:
#         	i+=1
#         else:
#         	results.extend([str(round(avg_pos_real,2)),str(round(var_pos_real,2)),str(round(avg_neg_real,2)),str(round(var_neg_real,2))])
#         	i+=1
        
#         trace1 = go.Scatter(x = positive_sorted.index.values, y = positive_sorted['close'], mode = 'markers',
#         marker = dict(size=12, color='rgba(8, 246, 8, .8)',line=dict(width=2)), name = 'Positive', visible= visibility)

#         trace2 = go.Scatter(x = negative_sorted.index.values, y = negative_sorted['close'], mode = 'markers',
#         marker = dict(size=12, color='rgba(247, 19, 27, .8)',line=dict(width=2)), name = 'Negative', visible = visibility)

#         trace3 = go.Scatter(x = positive_outliers.index.values, y = positive_outliers['close'], mode = 'markers',
#         marker = dict(size=12, color='rgba(240, 255, 0, .8)',line=dict(width=2)), name = 'Positive Outlier', visible= visibility)

#         trace4 = go.Scatter(x = negative_outliers.index.values, y = negative_outliers['close'], mode = 'markers',
#         marker = dict(size=12, color='rgba(0,0,0, .8)',line=dict(width=2)), name = 'Negative Outlier', visible = visibility)

#         trace5 = go.Scatter(x = positive_sorted.index.values, y = np.full(len(positive_sorted),avg_pos_real),
#         marker = dict(size=12, color='rgba(8, 246, 8, .8)',line=dict(width=2)), name = 'Positive Avg', visible= visibility)

#         trace6 = go.Scatter(x = negative_sorted.index.values, y = np.full(len(negative_sorted),avg_neg_real),
#         marker = dict(size=12, color='rgba(247, 19, 27, .8)',line=dict(width=2)), name = 'Negative Avg', visible = visibility)

        
#         if visibility == True:
#             visibility = False
#         data_plot.append(trace1)
#         data_plot.append(trace2)
#         data_plot.append(trace3)
#         data_plot.append(trace4)
#         data_plot.append(trace5)
#         data_plot.append(trace6)

#     trace7 = go.Scatter(x = price_data.index.values, y = price_data['closing_price'], name = 'Stock Price', yaxis='y2')
#     data_plot.append(trace7)

#     updatemenus=list([
#     dict(
#         buttons=list([
#             dict(
#                 args=[{'visible': [True,True,True,True,True,True,False,False,False,False,False,False,False,False,False,False,False,False,False,False,False,False,False,False,True]}],
#                 label='Hourly',
#                 method='update',
#             ),   
#             dict(
#                 args=[{'visible': [False,False,False,False,False,False,True,True,True,True,True,True,False,False,False,False,False,False,False,False,False,False,False,False,True]}],
#                 label='4Hours',
#                 method='update',
#             ),
#             dict(
#                 args=[{'visible': [False,False,False,False,False,False,False,False,False,False,False,False,True,True,True,True,True,True,False,False,False,False,False,False,True]}],
#                 label='8Hours',
#                 method='update',
#             ),
#             dict(
#                 args=[{'visible': [False,False,False,False,False,False,False,False,False,False,False,False,False,False,False,False,False,False,True,True,True,True,True,True,True]}],
#                 label='12Hours',
#                 method='update',
#             ),
#         ]),
#         direction = 'down',
#         pad = {'r': 10, 't': 10},
#         showactive = True,
#         x = 0.145,
#         xanchor = 'left',
#         y = 1.14,
#         yanchor = 'top' 
#     ),
# ]) 

#     layout = go.Layout(yaxis= dict(title='Percentage Change'), yaxis2=dict(title='Price', overlaying ='y',side ='right', showgrid=False))
#     fig = go.Figure(data=data_plot,layout=layout)
#     fig['layout']['updatemenus'] = updatemenus
#     fig['layout']['showlegend'] = True
    
#     fname = '/var/www/ljb.solutions/html/graphs/'+'pre_backtestingGraph_scatter2.html'
#     plotly.offline.plot(fig, filename = fname)
    results.append(days)
    table2_header[1]=results

    return table2_header

def main():
	# user inputs
    stock_symbol = sys.argv[1]
    finaldate = sys.argv[2]

    if len(finaldate)<14:
    	finaldate = finaldate + ' 00:00:00'
    try:
        if len(sys.argv[3])!=0:
            initdate = sys.argv[3]
            dt = datetime.timedelta(hours=24*7)
    except:
        dt = datetime.timedelta(hours=24*7)

    initdate = datetime.datetime.strptime(finaldate,'%Y-%m-%d %H:%M:%S') - dt
    initdate = datetime.datetime.strftime(initdate,'%Y-%m-%d %H:%M:%S')

# list with daily,weekly,monthly,yearly changes
    all_data = historical_changes_hourly(stock_symbol,initdate,finaldate)
    price_data = getData_fromdb(stock_symbol,finaldate,initdate, hourly = True)
# period info = [median, avg, std, pos_med, pos_avg, pos_std, neg_med, neg_avg, neg_std, pos_days, neg_days]
    dailystats, weeklystats, monthlystats, yearlystats = statistics(all_data)

# processing all data
    all_data = list_todf(all_data)

    table,table2,days = process_data(all_data)
    
    #boxplot(all_data)
    #piechart(days)
    #scatterplot(all_data,price_data)
    table3 = clean_outliers(all_data,price_data)



    dic ={}
    dic2={}
    dic['table'] = table
    dic2['table'] = table2
    dic['table2'] = table3
    info_json = json.dumps(dic)

    with open('/var/www/ljb.solutions/html/graphs/data.txt', 'w') as f:
        json.dump(dic2, f, ensure_ascii=False)

    print(info_json)
if __name__ == '__main__':
    main()