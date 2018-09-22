<?php

namespace App\Modules\HotelSearch\Request;

use App\Modules\HotelSearch\Factory\ProviderResponseFactory;
use App\Modules\HotelSearch\Response\ErrorResponse;
use App\Modules\HotelSearch\Services\AvailabilityService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Core\Base\Request;
use App\Core\Constants;

/**
 * Class AvailabilityRequest
 *
 * @property string    city           IATA Code
 * @property \DateTime fromDate       Date starting today
 * @property \DateTime toDate         Date  at least 1 day after 'fromDate'
 * @property integer   numberOfAdults Total adults for stay
 * @property array     response       Results
 *
 * @package App\Modules\HotelSearch\Request
 * @author  Jaai Chandekar
 */
class AvailabilityRequest extends Request
{

    /**
     * Request attributes
     *
     * @return array
     */
    function attributes(): array
    {
        return [
            'city',
            'fromDate',
            'toDate',
            'numberOfAdults',
            'response',
        ];
    }

    /**
     * Request attribute validation rules.
     *
     * @return array
     */
    function rules(): array
    {
        $dateFormat = Constants::AVAIL_HOTELS_INPUT_DATE_FORMAT;

        return [
            'city'           => 'required|alpha|size:3',
            'fromDate'       => sprintf('required|date|date_format:%s|after:%s', $dateFormat, date($dateFormat, strtotime("-1 days"))),
            'toDate'         => sprintf('required|date|date_format:%s|after:fromDate', $dateFormat),
            'numberOfAdults' => 'required|integer|min:1',
        ];
    }

    /**
     * Process request
     *
     * @return array
     */
    function process(): array
    {
        // check validation errors
        if (!empty($this->getErrors())) {
            $errors = $this->getErrors();
            Log::error(json_encode($errors));

            return (new ErrorResponse($errors))->transform();
        }

        $hashAttributes = $this->getAttributes();
        $hash           = md5(json_encode($hashAttributes));


        if ($this->isCached($hash)) {
            return $this->getResponse();
        }
        $response = $this->makeNewSearch($hash);

        return $response ?? [];
    }

    /**
     * Check if response is present in cache
     *
     * @param string $hash
     *
     * @return bool
     */
    public function isCached(string $hash): bool
    {
        try {
            $cachedResponse = Cache::get($hash, []);
        } catch (\Exception $exception) {
            return false;
        }

        if (!empty($cachedResponse)) {
            $this->setResponse($cachedResponse);
            Log::info(sprintf('Get from cache hash= %s, value= %s', $hash, json_encode($cachedResponse)));


            return true;
        }

        return false;
    }

    /**
     * Make new search
     *
     * @param string $hash
     *
     * @return array
     */
    public function makeNewSearch(string $hash): array
    {
        $responseArray = (new AvailabilityService($this->getAttributes()))->callHotelProviders();

        $isValid        = true;
        $parsedResponse = [];
        foreach ($responseArray as $provider => $response) {
            ProviderResponseFactory::getResponseByProvider($provider, $response, $parsedResponse, $isValid);
        }

        usort($parsedResponse, function ($res1, $res2) {
            return ($res1['fare'] <= $res2['fare']) ? -1 : 1;
        });

        // Response is cached if all hotel providers have returned non empty results
        if ($isValid) {
            Log::info(sprintf('Store in cache hash= %s, value= %s', $hash, json_encode($parsedResponse)));
            Cache::put($hash, $parsedResponse, Constants::HOTELS_CACHE_VALID_MINUTES);
        }

        return $parsedResponse;
    }

    /**
     * Set cached response
     *
     * @param array $response
     */
    public function setResponse(array $response = []): void
    {
        $this->response = $response ?? [];
    }

    /**
     * Get cached response property
     *
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response ?? [];
    }
}