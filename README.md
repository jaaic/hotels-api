## Hotels Api

A REST API to get hotel availability from different hotel providers.

## Pre-requisities
1. PHP >= 7.1.3
2. Install composer https://getcomposer.org/
3. composer global require "laravel/lumen-installer"

Check the laravel 5.6 installation https://lumen.laravel.com/docs/5.6

## How to setup?
1. clone the code to your machine
    ```
    git clone git@github.com:jaaic/hotels-api.git
    ```
2. Add correct settings to .env file

3. Install dependencies
    ```
    composer install
    ```
    
## Invoke api
The main hotels search availability api can be invoked as -
       
```
curl -X POST \
  http://localhost:8080/search/hotels \
  -H 'Cache-Control: no-cache' \
  -H 'Content-Type: application/json' \
  -d '{
	"city": "AUH",
	"fromDate": "2018-12-06",
	"toDate": "2018-12-07",
	"numberOfAdults": 1
}'
```

There are 2 apis created for BestHotels and CrazyHotels. These eventually would connect the besthotels and
crazyhotels provider endpoints to get the real time availability. However since we do not have access to provider 
endpoints, mocked responses are constructed according to the response formats for respective suppliers as mentioned
in the requirements. 

The BestHotels api can be individually invoked as -
```
curl -X POST \
  http://localhost:8080/providers/besthotels \
  -H 'Cache-Control: no-cache' \
  -H 'Content-Type: application/json' \
  -d '{
	"city": "DXB",
	"fromDate": "2018-12-01",
	"toDate": "2018-12-02",
	"numberOfAdults": 1
}'
```

The CrazyHotels api can be individually invoked as -
```
curl -X POST \
  http://localhost:8080/providers/crazyhotels \
  -H 'Cache-Control: no-cache' \
  -H 'Content-Type: application/json' \
  -d '{
	"city": "DXB",
	"from": "2018-12-01 12:00:00",
	"to": "2018-11-10 12:00:00",
	"adultsCount": 1
}'
```


## Assumptions
1. The currency of room rates is assumed to be same for parent 'search/hotels' api as well as the provider apis. 

2. The hotel rate in each api's response is the rate per room per night for the specified number of adults in request
   assuming all adults in request can be accommodated in the same room.

3. The hotels returned by the main api 'search/hotels' is ordered by hotel rates low to high

4. The same hotel could be returned by different suppliers. In this case the hotel appears twice in 'search/hotels' 
   response in ascending order of its fare. The solution could be changed to output unique hotels only if needed.

5. Validations are present on all api requests. The city is validated to be of 3 length chars only, checkin date to be 
   starting today, checkout date to be after checkin and guests to be atleast 1. To ensure the city codes are IATA codes,
   we can store all the IATA codes in a file/noSql db and validate the city code against these stored values.

6. User authentication/ authorization have not been addressed.

7. Since crazyhotels api needs a date time in the request (Y-m-d H:i:s), checkin/checkout are requested from/upto 
   '12:00:00' of the respective dates from 'search/hotels' 

8. Output fares from main 'search/hotels' api are rounded to 2 decimal places.

## Considerations
1. The main api 'search/hotels' calls the providers' apis asynchronously

2. If one of the providers returns empty response or exception, the output is still returned to be the response from
   the other supplier

3. To reduce calls to providers' apis, the response for particular request params is cached in redis for 10 mis. To 
   change the caching system and the caching time, change the config values in .env file for keys
   HOTELS_API.CACHE.DRIVER and REDIS_CACHE_VALID_MINUTES respectively.
   
4. The responses from the hotel providers can be mocked to be either list of hotels, empty response or exception. To get
   these different responses, call 'getMockHandler' method in 'App\Core\Base\HotelsBaseService' using arg -
   1: success response
   2: no response
   3: ClientException
   
## To add hotel providers
   The provider endpoints are configurable and the names can be changed or added to the key 'HOTEL_PROVIDERS' in .env
   file. For new providers, append the endpoint to 'HOTEL_PROVIDERS' value separated by '|'
   1. Add the wrapper api endpoint to App\Modules\HotelProviders\routes.php
   2. Add the controller method to 'App\Modules\HotelProviders\ProviderController.php'
   3. Add request load/validations by creating a new request in App\Modules\HotelProviders\Request
   4. Add main processing by creating a new service to call provider in App\Modules\HotelProviders\Services
   5. Add response formatting by creating a new response in App\Modules\HotelProviders\Response
   
   
## Running tests
   Run tests using codeception -
   ```
   bin/codecept build
   ```
   Execute tests -
   ```
   bin/codecept run api
   ```
## System specifications used for development/ testing
   Web server nginx:1.15.2-alpine
