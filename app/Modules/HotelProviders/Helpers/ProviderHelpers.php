<?php

namespace App\Modules\HotelProviders\Helpers;

use App\Core\Constants;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * Class ProviderHelpers to mock responses from hotel providers in local
 *
 * @package App\Modules\HotelProviders\Helpers
 * @author  Jaai Chandekar
 */
class ProviderHelpers
{
    /**
     * Handler for success response
     *
     * @param string $response
     *
     * @param string $uri
     *
     * @return \GuzzleHttp\Handler\MockHandler
     */
    public static function getMockHandlerWithSuccess(string $response, string $uri): MockHandler
    {
        $mockHandler = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], $response),
            new RequestException("Error Communicating with Server", new Request('GET', $uri)),
        ]);

        return $mockHandler;
    }

    /**
     * Handler for empty response
     *
     * @param string $uri
     *
     * @return \GuzzleHttp\Handler\MockHandler
     */
    public static function getMockHandlerForNoResponse(string $uri): MockHandler
    {
        $mockHandler = new MockHandler([
            new Response(202, ['Content-Length' => 0]),
            new RequestException("Error Communicating with Server", new Request('GET', $uri)),
        ]);

        return $mockHandler;
    }

    /**
     * Handler for failure response
     *
     * @param string $uri
     *
     * @return \GuzzleHttp\Handler\MockHandler
     */
    public static function getMockHandlerForFailure(string $uri): MockHandler
    {
        $mockHandler = new MockHandler([
            new RequestException("Error Communicating with Server", new Request('GET', $uri)),
        ]);

        return $mockHandler;
    }

    /**
     * Get Hotel providers from config
     *
     * @return array
     */
    public static function getHotelProviders(): array
    {
        $providerString = env(Constants::HOTEL_PROVIDERS_CONFIG_KEY, '');
        $providers      = explode('|', $providerString);

        return $providers;
    }
}