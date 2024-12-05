CREATE TABLE daily_rent_detail ( 
    ride_id VARCHAR(50) PRIMARY KEY,
    rideable_type VARCHAR(50),
    started_at DATETIME,
    ended_at DATETIME,
    start_station_name VARCHAR(255),
    start_station_id INT,
    end_station_name VARCHAR(255),
    end_station_id INT,
    start_lat DECIMAL(9,6),
    start_lng DECIMAL(9,6),
    end_lat DECIMAL(9,6),
    end_lng DECIMAL(9,6),
    member_casual ENUM('member', 'casual')
    );

CREATE TABLE station_list (
    station_id INT PRIMARY KEY,
    station_name VARCHAR(255)
    );

CREATE TABLE usage_frequency (
    date DATE,
    station_name VARCHAR(255),
    pickup_counts INT,
    dropoff_counts INT
    );

CREATE TABLE weather (
    name VARCHAR(255),
    datetime DATETIME,
    tempmax FLOAT,
    tempmin FLOAT,
    temp FLOAT,
    feelslikemax FLOAT,
    feelslikemin FLOAT,
    feelslike FLOAT,
    dew FLOAT,
    humidity FLOAT,
    precip FLOAT,
    precipprob FLOAT,
    precipcover FLOAT,
    preciptype VARCHAR(50),
    snow FLOAT,
    snowdepth FLOAT,
    windgust FLOAT,
    windspeed FLOAT,
    winddir FLOAT,
    sealevelpressure FLOAT,
    cloudcover FLOAT,
    visibility FLOAT,
    solarradiation FLOAT,
    solarenergy FLOAT,
    uvindex FLOAT,
    severerisk FLOAT,
    sunrise DATETIME,
    sunset DATETIME,
    moonphase FLOAT,
    conditions VARCHAR(255),
    description VARCHAR(255),
    icon VARCHAR(50),
    stations VARCHAR(255)
    );


load data local infile './daily_rent_detail.csv'
into table daily_rent_detail
fields terminated by ','
enclosed by '"'
lines terminated by '\n'
ignore 1 lines;

load data local infile './station_list.csv'
into table station_list
fields terminated by ','
enclosed by '"'
lines terminated by '\n'
ignore 1 lines;

load data local infile './usage_frequency.csv'
into table usage_frequency
fields terminated by ','
enclosed by '"'
lines terminated by '\n'
ignore 1 lines;

load data local infile './weather.csv'
into table weather
fields terminated by ','
enclosed by '"'
lines terminated by '\n'
ignore 1 lines;