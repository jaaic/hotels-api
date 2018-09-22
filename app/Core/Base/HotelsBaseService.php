<?php

namespace App\Core\Base;

use App\Modules\HotelProviders\Helpers\ProviderHelpers;
use GuzzleHttp\HandlerStack;

/**
 * Class HotelsBaseService
 *
 * @package App\Core\Base
 * @author  Jaai Chandekar
 */
class HotelsBaseService
{
    /** @var string */
    protected $configUriKey;

    /** @var string */
    protected $filePath;

    /** @var string */
    protected $uri;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @type RestApiClient
     */
    protected $client = null;

    /**
     * @var object
     */
    protected $request;

    /**
     * HotelsBaseService constructor.
     *
     * @param array  $attributes
     * @param string $uri
     */
    public function __construct(array $attributes, string $uri = '')
    {
        $this->request = $attributes;
        $this->uri     = empty($uri) ? env($this->configUriKey, '/hotels') : $uri;
    }

    /**
     * return request attributes array
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->data;
    }

    /**
     * Create client
     *
     * @param array $options extra options
     *
     * @return \App\Core\Base\RestApiClient
     */
    protected function getClient(array $options = []): RestApiClient
    {
        if (is_null($this->client)) {
            $this->client = new RestApiClient($options);
        }

        return $this->client;
    }

    /**
     * To be overridden by inherited provider services if needed.
     * Input the handler number for specific handler to test different scenarios
     * 1: success response
     * 2: no response
     * 3: ClientException
     *
     * @return array
     */
    public function getHotels(): array
    {
        $handlerStack = [];
        if (env('APP_ENV', 'local') == 'local') {
            $handlerStack = $this->getMockHandler(1);
        }

        $restClient = new RestApiClient(['handler' => $handlerStack]);

        $hotels = $restClient->getClient()->request(RestApiClient::POST_METHOD, $this->uri, [$this->request])->getBody()->getContents();

        if (empty($hotels)) {
            return [];
        }

        return json_decode($hotels, true);
    }

    /**
     * Get mock handler for local api call
     * 1: success response
     * 2: No Response
     * 3: Failure
     *
     * @param $rand
     *
     * @return HandlerStack
     */
    public function getMockHandler(int $rand = 1): HandlerStack
    {
        if (empty($rand)) {
            $rand = rand(1, 3);
        }

        switch ($rand) {
            case 1:
                if (file_exists($this->filePath)) {
                    $response = file_get_contents($this->filePath);
                } else {
                    $response = [];
                }

                $mockHandler = ProviderHelpers::getMockHandlerWithSuccess($response, $this->uri);
                break;

            case 2:
                $mockHandler = ProviderHelpers::getMockHandlerForNoResponse($this->uri);
                break;

            case 3:
                $mockHandler = ProviderHelpers::getMockHandlerForFailure($this->uri);
                break;

            default:
                $mockHandler = ProviderHelpers::getMockHandlerForNoResponse($this->uri);
        }

        $handler = HandlerStack::create($mockHandler);

        return $handler;
    }
}