<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/**  @var $router \Laravel\Lumen\Routing\Router */
$router->group(['prefix' => 'providers', 'namespace' => 'App\Modules\HotelProviders\Controllers'], function () use ($router) {

    $router->post('/besthotels', ['uses' => 'ProviderController@getBestHotels']);

    $router->post('/crazyhotels', ['uses' => 'ProviderController@getCrazyHotels']);

});