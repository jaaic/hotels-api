<?php

namespace App\Modules\HotelProviders\Services;

use App\Core\Base\HotelsBaseService;
use App\Core\Base\RestApiClient;

/**
 * Class BestHotelsService
 *
 * @package App\Modules\HotelProviders\Services
 * @author  Jaai Chandekar
 */
class BestHotelsService extends HotelsBaseService
{
    /** @var string */
    protected $configUriKey = 'BEST_HOTELS_URI';

    /** @var string */
    protected $filePath = __DIR__ . '/../Response/Mocks/BestHotelsResponse.json';
}