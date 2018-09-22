<?php

namespace App\Modules\HotelProviders\Services;

use App\Core\Base\HotelsBaseService;
use App\Core\Base\RestApiClient;

class CrazyHotelsService extends HotelsBaseService
{
    /** @var string */
    protected $configUriKey = 'CRAZY_HOTELS_URI';

    /** @var string */
    protected $filePath = __DIR__ . '/../Response/Mocks/CrazyHotelsResponse.json';
}