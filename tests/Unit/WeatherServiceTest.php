<?php

use App\Services\WeatherService;
use Illuminate\Support\Facades\Http;

it('getWeatherForCoordinates returns formatted weather data when API call is successful', function () {
    // Mock the HTTP client
    Http::fake([
        'api.open-meteo.com/v1/forecast*' => Http::response([
            'current_weather' => [
                'temperature' => 20.5,
                'windspeed' => 15.2,
            ]
        ], 200)
    ]);

    $weatherService = new WeatherService();
    $result = $weatherService->getWeatherForCoordinates(51.5074, -0.1278, 'London');

    // Assert the result contains the expected formatted string
    expect($result)->toBe('Current weather for London: 20.5°C, wind 15.2 km/h.');
});

it('getWeatherForCoordinates returns error message when API call fails', function () {
    // Mock the HTTP client to return an error
    Http::fake([
        'api.open-meteo.com/v1/forecast*' => Http::response([], 500)
    ]);

    $weatherService = new WeatherService();
    $result = $weatherService->getWeatherForCoordinates(51.5074, -0.1278, 'London');

    // Assert the result contains the error message
    expect($result)->toBe("Sorry, I couldn't fetch the weather right now.");
});

it('getWeatherForCoordinates uses default location name when none provided', function () {
    // Mock the HTTP client
    Http::fake([
        'api.open-meteo.com/v1/forecast*' => Http::response([
            'current_weather' => [
                'temperature' => 20.5,
                'windspeed' => 15.2,
            ]
        ], 200)
    ]);

    $weatherService = new WeatherService();
    $result = $weatherService->getWeatherForCoordinates(51.5074, -0.1278);

    // Assert the result uses the default location name
    expect($result)->toBe('Current weather for your location: 20.5°C, wind 15.2 km/h.');
});
