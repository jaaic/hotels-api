<?php

namespace App\Core;

/**
 * Class Constants
 *
 * @package App\Core
 * @author  Jaai Chandekar
 */
class Constants
{
    const ERROR_STATE   = 'error';
    const SUCCESS_STATE = 'success';

    // hotel api constants
    const  BEST_HOTELS_INPUT_DATE_FORMAT  = 'Y-m-d';
    const  AVAIL_HOTELS_INPUT_DATE_FORMAT = 'Y-m-d';
    const  CRAZY_HOTELS_INPUT_DATE_FORMAT = 'Y-m-d H:i:s';
    const  HOTEL_PROVIDERS_CONFIG_KEY     = 'HOTEL_PROVIDERS';
    const  PROVIDER_BESTHOTELS            = 'besthotels';
    const  PROVIDER_CRAZYHOTELS           = 'crazyhotels';
    const  HOTELS_CACHE_VALID_MINUTES     = 10;


    // Rest Client constants
    const USER_AGENT        = 'User-Agent';
    const CLIENT_IP         = 'Client_Ip';
    const HOST              = 'host';
    const X_REAL_IP         = 'X-Real-Ip';
    const X_FORWARDED_FOR   = 'X-Forwarded-For';
    const X_FORWARDED_PROTO = 'X-Forwarded-Proto';
    const X_ELB_IP          = 'X-Elb-Ip';
}