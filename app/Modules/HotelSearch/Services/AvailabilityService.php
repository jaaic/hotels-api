<?php

namespace App\Modules\HotelSearch\Services;

use App\Core\Base\RestApiClient;
use App\Core\Constants;
use App\Modules\HotelProviders\Helpers\ProviderHelpers;
use App\Modules\HotelSearch\Factory\ProviderRequestFactory;

/**
 * Class AvailabilityService
 *
 * @package App\Modules\HotelSearch\Services
 * @author  Jaai Chandekar
 */
class AvailabilityService
{
    /** @var array */
    protected $attributes;

    /**
     * AvailabilityService constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Call providers
     *
     * @return array
     */
    public function callHotelProviders(): array
    {
        $requests  = [];
        $providers = ProviderHelpers::getHotelProviders();

        foreach ($providers as $provider) {
            $req = ProviderRequestFactory::getRequestByProvider($provider, $this->attributes);
            if (!empty($req)) {
                $requests[$provider] = $req;
            }
        }

        // get client
        $client = new RestApiClient();

        $response = $client->callApiAsync($requests);

        return $response ?? [];
    }

}