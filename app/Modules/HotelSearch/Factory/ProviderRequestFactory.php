<?php

namespace App\Modules\HotelSearch\Factory;


use App\Core\Constants;

class ProviderRequestFactory
{
    /**
     * Get request per provider
     *
     * @param string $provider
     * @param array  $params
     *
     * @return array
     */
    public static function getRequestByProvider(string $provider, array $params): array
    {
        $provider = strtolower($provider);
        switch ($provider) {
            case Constants::PROVIDER_BESTHOTELS :
                return self::getBestHotelsRequest($params);

            case Constants::PROVIDER_CRAZYHOTELS :
                return self::getCrazyHotelsRequest($params);

            default:
                return [];
        }

    }

    /**
     * Get request for best hotels
     *
     * @param array $params
     *
     * @return array
     */
    public static function getBestHotelsRequest(array $params): array
    {
        return [
            'method'  => 'POST',
            'url'     => 'http://hotels-api.tajawal.local/providers/besthotels',
            'request' => [
                'city'           => $params['city'] ?? '',
                'fromDate'       => $params['fromDate'] ?? '',
                'toDate'         => $params['toDate'] ?? '',
                'numberOfAdults' => $params['numberOfAdults'] ?? 0,
            ],
        ];
    }

    /**
     * Get request for best hotels
     *
     * @param array $params
     *
     * @return array
     */
    public static function getCrazyHotelsRequest(array $params): array
    {
        $from = $params['fromDate'] ?? '';
        if (!empty($from)) {
            $from = $from . ' 12:00:00';
        }

        $to = $params['toDate'] ?? '';
        if (!empty($to)) {
            $to = $to . ' 12:00:00';
        }

        return [
            'method'  => 'POST',
            'url'     => 'http://hotels-api.tajawal.local/providers/crazyhotels',
            'request' => [
                'city'        => $params['city'] ?? '',
                'from'        => $from,
                'to'          => $to,
                'adultsCount' => $params['numberOfAdults'] ?? 0,
            ],
        ];
    }
}