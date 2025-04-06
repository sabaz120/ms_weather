<?php

namespace Modules\Weather\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Exception, Log;

class WeatherService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('weather.api_key');
        $this->client = new Client([
            'base_uri' => config('weather.api_url') . '/',
        ]);
    }

    public function getCurrentWeather($city)
    {
        try {
            $lang = \App::getLocale();
            $response = $this->client->get('current.json', [
                'query' => [
                    'key' => $this->apiKey,
                    'q' => $city,
                    'lang' => $lang,
                ],
            ]);

            $weatherData = json_decode($response->getBody(), true);

            $filteredData = [
                'location' => [
                    'name' => $weatherData['location']['name'],
                    'region' => $weatherData['location']['region'],
                    'country' => $weatherData['location']['country'],
                    'localtime' => $weatherData['location']['localtime'],
                ],
                'current' => [
                    'temp_c' => $weatherData['current']['temp_c'],
                    'temp_f' => $weatherData['current']['temp_f'],
                    'condition' => $weatherData['current']['condition'],
                    'wind_kph' => $weatherData['current']['wind_kph'],
                    'humidity' => $weatherData['current']['humidity'],
                ],
            ];

            return [
                'success' => true,
                'data' => $filteredData,
            ];
        } catch (RequestException $e) {
            return [
                'success' => false,
                'data' => [
                    'message' => $e->getMessage()?? trans('messages.weather_module.weather.error'),
                    'code' => $e->getCode(),
                    'response' => $e->getResponse() ? json_decode($e->getResponse()->getBody(), true) : null,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => [
                    'message' => $e->getMessage()?? trans('messages.weather_module.weather.error'),
                    'code' => $e->getCode(),
                    'response' => null,
                ],
            ];
        }
    }
}
