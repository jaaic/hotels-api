<?php

namespace App\Modules\HotelProviders\Controllers;

use App\Modules\HotelProviders\Request\BestHotelsRequest;
use App\Modules\HotelProviders\Request\CrazyHotelsRequest;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Http\Request;

/**
 * Class ProviderController
 *
 * @author Jaai Chandekar
 */
class ProviderController extends Controller
{
    /** @var \Illuminate\Http\Request */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Best hotels controller method
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getBestHotels(): array
    {
        $request = new BestHotelsRequest();

        $response = $request->load($this->request->all())
                            ->validate()
                            ->process();

        return $response;
    }

    /**
     * Crazy hotels controller method
     *
     * @return array
     * @throws \Exception
     */
    public function getCrazyHotels(): array
    {
        $request = new CrazyHotelsRequest();

        $response = $request->load($this->request->all())
                            ->validate()
                            ->process();

        return $response;

    }


}