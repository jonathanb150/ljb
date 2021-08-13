#!/usr/bin/env python
import pandas
import pandas_datareader as pdr 
import sys


def main():
    ticker = sys.argv[1]
    data = pdr.DataReader('ticker','stooq')
    print(data)

if __name__ == '__main__':
    main()