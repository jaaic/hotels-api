<?php

namespace App\Modules\HotelSearch\Factory;

use App\Core\Constants;
use Illuminate\Support\Facades\Log;

class ProviderResponseFactory
{
    /**
     * Parse response by provider
     *
     * @param string $provider
     * @param string $response
     * @param array  $parsedResponse
     * @param bool   $isValid
     *
     * @return  void
     */
    public static function getResponseByProvider(string $provider, string $response, array &$parsedResponse, &$isValid)
    {
        $provider = strtolower($provider);

        switch ($provider) {
            case Constants::PROVIDER_BESTHOTELS :
                self::parseBestHotelsResponse($response, $parsedResponse, $isValid);
                break;

            case Constants::PROVIDER_CRAZYHOTELS :
                self::parseCrazyHotelsResponse($response, $parsedResponse, $isValid);
                break;

            default:
                Log::warning(sprintf('Provider %s not implemented', $provider));
        }
    }

    /**
     * Parse best hotels response
     *
     * @param string $response
     * @param array  $parsedResponse
     * @param bool   $isValid
     *
     * @return  void
     */
    public static function parseBestHotelsResponse(string $response, array &$parsedResponse, &$isValid)
    {
        $responseArr = json_decode($response, true);
        if (empty($responseArr)) {
            $isValid = false;

            return;
        }
        foreach ($responseArr as $response) {
            if (empty($response['hotel']) || empty($response['hotelFare'])) {
                continue;
            }
            $res['provider']  = Constants::PROVIDER_BESTHOTELS;
            $res['hotelName'] = $response['hotel'];
            $res['fare']      = round($response['hotelFare'], 2);
            $res['amenities'] = explode(',', $response['roomAmenities'] ?? '');

            array_push($parsedResponse, $res);
        }

    }


    /**
     * Parse crazy hotels response
     *
     * @param string $response
     * @param array  $parsedResponse
     * @param bool   $isValid
     *
     * @return  void
     */
    public static function parseCrazyHotelsResponse(string $response, &$parsedResponse, &$isValid)
    {
        $responseArr = json_decode($response, true);

        if (empty($responseArr)) {
            $isValid = false;

            return;
        }

        foreach ($responseArr as $response) {
            if (empty($response['hotelName']) || empty($response['price'])) {
                continue;
            }
            $res['provider']  = Constants::PROVIDER_CRAZYHOTELS;
            $res['hotelName'] = $response['hotelName'];
            $res['fare']      = round($response['price'] - ($response['discount'] ?? 0), 2);
            $res['amenities'] = $response['amenities'] ?? [];

            array_push($parsedResponse, $res);
        }
    }
}