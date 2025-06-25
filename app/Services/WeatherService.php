<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    /**
     * @throws ConnectionException
     */
    public function getWeatherForCoordinates(float $lat, float $lon, ?string $location = null): string
    {
        $response = Http::get('https://api.open-meteo.com/v1/forecast', [
            'latitude' => $lat,
            'longitude' => $lon,
            'current_weather' => true,
        ]);

        if($response->ok() && $response->json('current_weather')) {
            $weather = $response->json('current_weather');

             return sprintf(
                 "Current weather for %s: %.1f°C, wind %.1f km/h.",
                 $location ?? 'your location',
                 $weather['temperature'],
                 $weather['windspeed']
             );
        }

        return "Sorry, I couldn't fetch the weather right now.";
    }
}
