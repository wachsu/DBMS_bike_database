import sys
import pandas as pd
import numpy as np
import joblib
import os

def map_temperature(temp):
    conditions = [
        (temp >= -9.0) & (temp <= -2.0),
        (temp >= -1.0) & (temp <= 5.0),
        (temp >= 6.0) & (temp <= 12.0),
        (temp >= 13.0) & (temp <= 19.0),
        (temp >= 20.0) & (temp <= 26.0),
        (temp >= 27.0) & (temp <= 33.0),
    ]
    choices = [1, 2, 3, 4, 5, 6]
    return np.select(conditions, choices, default=0)

def map_wind_speed(wind):
    conditions = [
        (wind >= 8.0) & (wind < 14.0),
        (wind >= 15.0) & (wind < 21.0),
        (wind >= 22.0) & (wind <= 28.0),
        (wind >= 29.0) & (wind <= 35.0),
        (wind >= 36.0) & (wind <= 42.0),
        (wind >= 43.0) & (wind < 59.0),
    ]
    choices = [1, 2, 3, 4, 5, 6]
    return np.select(conditions, choices, default=0)

# 接收 PHP 傳來的輸入
stationName = sys.argv[1]
temp = sys.argv[2]
wind = sys.argv[3]
uv = sys.argv[4]
rain = sys.argv[5]


modelName = ['', 't.pkl', 'w.pkl', 'wt.pkl', 'u.pkl', 'ut.pkl', 'uw.pkl', 'uwt.pkl', 'r.pkl', 'rt.pkl', 'rw.pkl', 'rwt.pkl', 'ru.pkl', 'rut.pkl', 'ruw.pkl', 'ruwt.pkl'];

index = 0
input_data = {}

# 判斷是否為有效輸入並更新 index 和 input_data
if temp != "null":
    index |= 1  # temp 位
    input_data['temp'] = int(temp)
    input_data['temp'] = input_data['temp']*7 - 12
if wind != "null":
    index |= 2  # wind 位
    input_data['windspeed'] = int(wind)
    input_data['temp'] = input_data['temp']*7 + 4
if uv != "null":
    index |= 4  # uv 位
    input_data['uvindex'] = int(uv)
if rain != "null":
    index |= 8  # rain 位
    input_data['precipprob'] = int(rain)

try:
    model_dir = os.path.dirname(__file__)
    pipeline = joblib.load(os.path.join(model_dir, modelName[index]))
except FileNotFoundError:
    print(f"Error: Model file {modelName[index]} not found.")
    sys.exit(1)

# 預處理 station_name
stations = pd.read_csv('trained_stations.csv')['station_name'].tolist()
if stationName in stations:
    input_data['station_name'] = stationName
else:
    input_data['station_name'] = "other"

# 處理輸入數據
input_features = pd.DataFrame([input_data])

# 預測
try:
    result = pipeline.predict(input_features)
    print(int(result[0]))
except Exception as e:
    print(f"Error during prediction: {e}")
    sys.exit(1)
