#!/usr/bin/env python
#FILE NAME: portfolioChart.py

## Makes and Output Portfolio Chart
import sys, os
sys.path.append("/var/www/ljb.solutions/html/algorithms/lib")
import plotly.plotly as py
import plotly
import plotly.graph_objs as go
import json
import sys

def main():

 ## ------------------------ PLOTTING ------------------------------------------------------------   
    total_cap = sys.argv[1].split(',')
    cash= sys.argv[2].split(',')
    investedcap = sys.argv[3].split(',')
    dates = sys.argv[4].split(',')


    plot = makingPlot(dates,total_cap,investedcap,cash)

    dic = {}
    dic['graph'] = plot

    #Printing graph
    info_json = json.dumps(dic)
    print(info_json)
    
    
    
## DONE   
    
    
    
def makingPlot(x,y1,y2,y3):       

    
    trace1 = go.Scattergl(x = x , y = y1, name = 'Total Capital',fill = 'tonextx', opacity = 0.8)
    
    trace2 = go.Scattergl(x = x , y = y2, name = 'Invested Capital',fill = 'tonextx', opacity = 0.8)
    
    trace3 = go.Scattergl(x= x,y= y3, name ='Cash',fill = 'tonextx', opacity = 0.8)

    
    data = [trace1,trace2,trace3]
    
    layout = dict(
        xaxis=dict(
            rangeselector=dict(
                buttons=list([
                    dict(count=30,
                         label='1m',
                         step='day',
                         stepmode='backward'),
                    dict(count=180,
                         label='6m',
                         step='day',
                         stepmode='backward'),
                    dict(count=365,
                         label='1y',
                         step='day',
                         stepmode='backward'),
                    dict(count=1095,
                         label='3y',
                         step='day',
                         stepmode='backward'),
                    dict(label='max',
                         step='all',
                         stepmode='backward'),

                    ]),

                         ),
            rangeslider = dict(visible = False),
            type='date',domain=[0, 1]
        ),
        yaxis=dict(
            domain=[0, 1],
            anchor = 'y'
        ))
                  
    updatemenus=list([
        dict(
            buttons=list([   
                dict(
                    args=[{'visible': [True,False,False]}],
                    label='Total Capital',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,True,False]}],
                    label='Invested Capital',
                    method='update',
                ),
                dict(
                    args=[{'visible': [False,False,True]}],
                    label='Cash',
                    method='update',
                ),
                dict(
                    args=[{'visible': [True,True,True]}],
                    label='Reset',
                    method='update',
                )            

            ]),
            direction = 'left',
            pad = {'r': 10, 't': 10},
            showactive = True,
            type = 'buttons',
            x = 0.05,
            xanchor = 'left',
            y = 1.2,
            yanchor = 'top' 
        ),
    ])

    fig = dict(layout=layout, data=data)
    fig['layout']['title'] = 'Portfolio Performance'
    fig['layout']['showlegend'] = True
    fig['layout']['updatemenus'] = updatemenus
    fig['layout'].update(height=800)
    
    fname = '/var/www/ljb.solutions/html/graphs/portfolioChart.html'

    plotly.offline.plot(fig, filename=fname)
    with open(fname, 'r') as content_file:
        content = content_file.read()

    return content
if __name__ == '__main__':
    main()

