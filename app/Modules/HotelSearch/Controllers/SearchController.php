<?php

namespace App\Modules\HotelSearch\Controllers;


use App\Modules\HotelSearch\Request\AvailabilityRequest;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class SearchController extends Controller
{
    /** @var \Illuminate\Http\Request */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get available hotels from providers
     *
     * @throws \Exception
     */
    public function getAvailableHotels(): array
    {
        $request = new AvailabilityRequest();

        $response = $request->load($this->request->all())
                            ->validate()
                            ->process();

        return $response;
    }
}