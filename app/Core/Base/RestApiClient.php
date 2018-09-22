<?php

namespace App\Core\Base;

use App\Core\Constants;
use App\Exceptions\BadRequestException;
use App\Exceptions\ServerException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Promise;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;

class RestApiClient
{
    const POST_METHOD   = 'POST';
    const GET_METHOD    = 'GET';
    const DELETE_METHOD = 'DELETE';
    const PUT_METHOD    = 'PUT';

    /** @var \GuzzleHttp\Client|null $client */
    protected $client = null;

    /**
     * ApiClient constructor.
     *
     * Sets up the configuration and token for the API calls
     *
     * @param array $options options
     */
    public function __construct(array $options = [])
    {
        $clientOptions = [
            'allow_redirects' => [
                'max'             => 10,        // allow at most 10 redirects.
                'strict'          => true,      // use "strict" RFC compliant redirects.
                'referer'         => true,      // add a Referer header
                'protocols'       => ['https', 'http'], // only allow https URLs
                'track_redirects' => true,
            ],
            'headers'         => [
                Constants::USER_AGENT        => 'hotels-api/1.0',
                Constants::CLIENT_IP         => $_SERVER['CLIENT_IP'] ?? '',
                Constants::X_REAL_IP         => $_SERVER['X_REAL_IP'] ?? '',
                Constants::X_FORWARDED_FOR   => $_SERVER['X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
                Constants::X_FORWARDED_PROTO => $_SERVER['X_FORWARDED_PROTO'] ?? $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '',
                Constants::HOST              => $_SERVER['HTTP_HOST'] ?? '',
                Constants::X_ELB_IP          => $_SERVER['X_ELB_IP'] ?? $_SERVER['HTTP_X_ELB_IP'] ?? '',
            ],
        ];
        $clientOptions = array_merge($clientOptions, $options);

        $this->client = new Client($clientOptions);
    }

    /**
     * Get client
     *
     * @return \GuzzleHttp\Client
     *
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param array $parameters
     *
     * @return array
     */
    public function callApiAsync(array $parameters = [])
    {
        try {
            $requests = [];
            foreach ($parameters as $provider => $params) {
                $query               = ['json' => $params['request'] ?? []];
                $url                 = $params['url'] ?? '';
                $method              = $params['method'] ?? '';
                $requests[$provider] = $this->callAsyncRequest($url, $method, $query);
            }

            $response = $this->asyncRun($requests);

            return $response;
        } catch (ClientException $e) {
            $response             = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            $message1             = '[Hotels-api]:' . \GuzzleHttp\Psr7\str($response);
            $message2             = '[Hotels-api]' . $e->getMessage() . '---' . $e->getTraceAsString();

            $error = sprintf('Guzzle call failed with client error %s, stack trace %s', $message1, $message2);
            Log::error($error);

            return (new BadRequestException($responseBodyAsString))->toArray();
        } catch (\Throwable $e) {
            $message = '[Hotels-api]:' . $e->getMessage() . '---' . $e->getTraceAsString();

            Log::error($message);

            return (new ServerException($message, 'Internal Error'))->toArray();
        }
    }

    /**
     * Make async guzzle request
     *
     * @param string $url
     * @param string $method
     * @param array  $parameters
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function callAsyncRequest(string $url, string $method, array $parameters = [])
    {
        $parameters['connect_timeout'] = env('HOTELS_API.GUZZLE.CONNECT_TIMEOUT_SEC', 10);

        return $this->client->requestAsync($method, $url, $parameters);
    }

    /**
     * Wait on all of the requests to complete. Throws a ConnectException if any of the requests fail
     *
     * @param $promises
     *
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function asyncRun($promises)
    {
        $return  = [];
        $results = Promise\settle($promises)->wait();


        /** @var  \GuzzleHttp\Psr7\Response $result */
        foreach ($results as $provider => $result) {

            if ($result['state'] == 'fulfilled') {
                $response = $result['value'];

                if ($response->getStatusCode() == 200) {
                    $response          = $response->getBody()->getContents();
                    $return[$provider] = $response;
                } else {
                    $return[$provider] = [];
                }
            } elseif ($result['state'] == 'rejected') {
                $return[$provider] = '';
            }
        }

        return $return;
    }
}