<?php

namespace App\Modules\HotelProviders\Request;

use App\Core\Base\Request;
use App\Core\Constants;
use App\Modules\HotelProviders\Response\ErrorResponse;
use App\Modules\HotelProviders\Services\CrazyHotelsService;
use Illuminate\Support\Facades\Log;

/**
 * Class CrazyHotelsRequest
 *
 * @property string    city        IATA Code
 * @property \DateTime from        Date with time starting today
 * @property \DateTime to          Date with time atleast 1 day after 'from'
 * @property integer   adultsCount Total adults for stay
 *
 * @package App\Modules\HotelProviders\Request
 * @author  Jaai Chandekar
 */
class CrazyHotelsRequest extends Request
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
            'from',
            'to',
            'adultsCount',
        ];
    }

    /**
     * Request attribute validation rules.
     *
     * @return array
     */
    function rules(): array
    {
        $dateFormat = Constants::CRAZY_HOTELS_INPUT_DATE_FORMAT;

        return [
            'city'        => 'required|alpha|size:3',
            'from'        => sprintf('required|date|date_format:%s|after:%s', $dateFormat, date($dateFormat, strtotime("-1 days"))),
            'to'          => sprintf('required|date|date_format:%s|after:from', $dateFormat),
            'adultsCount' => 'required|integer|min:1',
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

        $response = (new CrazyHotelsService($this->getAttributes()))->getHotels();

        return (empty($response)) ? [] : ($response['hotels'] ?? []);
    }
}