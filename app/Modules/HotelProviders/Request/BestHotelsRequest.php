<?php

namespace App\Modules\HotelProviders\Request;

use App\Modules\HotelProviders\Response\ErrorResponse;
use App\Modules\HotelProviders\Services\BestHotelsService;
use Illuminate\Support\Facades\Log;
use App\Core\Base\Request;
use App\Core\Constants;

/**
 * Class BestHotelsRequest
 *
 * @property string    city           IATA Code
 * @property \DateTime fromDate       Date starting today
 * @property \DateTime toDate         Date  at least 1 day after 'fromDate'
 * @property integer   numberOfAdults Total adults for stay
 *
 *
 * @author Jaai Chandekar
 */
class BestHotelsRequest extends Request
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
        ];
    }

    /**
     * Request attribute validation rules.
     *
     * @return array
     */
    function rules(): array
    {
        $dateFormat = Constants::BEST_HOTELS_INPUT_DATE_FORMAT;

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

        $response = (new BestHotelsService($this->getAttributes()))->getHotels();

        return (empty($response)) ? [] : ($response['hotels'] ?? []);
    }
}