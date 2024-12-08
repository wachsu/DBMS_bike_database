import sys
import pandas as pd
import joblib

# 接收 PHP 傳來的輸入
stationName = sys.argv[1]
temp = sys.argv[2]
wind = sys.argv[3]
uv = sys.argv[4]
rain = sys.argv[5]

modelName = ['t.pkl', 'w.pkl', 'wt.pkl', 'u.pkl', 'ut.pkl', 'uw.pkl', 'uwt.pkl', 'r.pkl', 'rt.pkl', 'rw.pkl', 'rwt.pkl', 'ru.pkl', 'rut.pkl', 'ruw.pkl', 'ruwt.pkl'];

index = 0
input_data = {}

# 判斷是否為有效輸入並更新 index 和 input_data
if temp != "null":
    index |= 1  # temp 位
    input_data['temp'] = int(temp)
if wind != "null":
    index |= 2  # wind 位
    input_data['windspeed'] = int(wind)
if uv != "null":
    index |= 4  # uv 位
    input_data['uvindex'] = int(uv)
if rain != "null":
    index |= 8  # rain 位
    input_data['precipprob'] = int(rain)

try:
    pipeline = joblib.load(modelName[index])
except FileNotFoundError:
    print(f"Error: Model file {modelName[index]} not found.")
    sys.exit(1)

# 處理輸入數據
input_features = [pd.DataFrame([input_data])]

# 預處理 station_name
stations = pd.read_csv('trained_stations.csv')['station_name'].tolist()
if stationName in stations:
    input_data['station_name'] = stationName
else:
    input_data['station_name'] = "other"

# 預測
try:
    result = pipeline.predict(input_features)
    print(int(result[0]))
except Exception as e:
    print(f"Error during prediction: {e}")
    sys.exit(1)
