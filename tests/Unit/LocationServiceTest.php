<?php

use App\Enums\UserDataTypeEnum;
use App\Models\User;
use App\Models\UserData;
use App\Services\LocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('getLastKnownLocation returns null when user has no location', function () {
    // Create a user without location data
    $user = User::factory()->create();

    $locationService = new LocationService();
    $result = $locationService->getLastKnownLocation($user);

    expect($result)->toBeNull();
});

it('getLastKnownLocation returns location data when user has location', function () {
    // Create a user
    $user = User::factory()->create();

    // Create location data for the user
    $locationData = [
        'name' => 'London',
        'latitude' => 51.5074,
        'longitude' => -0.1278,
    ];

    $user->userData()->create([
        'data_type' => UserDataTypeEnum::LOCATION,
        'data_value' => $locationData,
    ]);

    $locationService = new LocationService();
    $result = $locationService->getLastKnownLocation($user);

    expect($result)->toBe($locationData);
});

it('askForLocation returns the expected message', function () {
    $locationService = new LocationService();
    $result = $locationService->askForLocation();

    expect($result)->toBe("I don't know your location yet. Please enter your city or town.");
});

it('setUserLocation saves and returns location data when geocoding is successful', function () {
    // Create a user
    $user = User::factory()->create();

    // Mock the HTTP client for geocoding
    Http::fake([
        'geocoding-api.open-meteo.com/v1/search*' => Http::response([
            'results' => [
                [
                    'name' => 'London',
                    'latitude' => 51.5074,
                    'longitude' => -0.1278,
                ]
            ]
        ], 200)
    ]);

    $locationService = new LocationService();
    $result = $locationService->setUserLocation($user, 'London');

    // Check the result
    expect($result)->toBeArray()
        ->and($result['name'])->toBe('London')
        ->and($result['latitude'])->toBe(51.5074)
        ->and($result['longitude'])->toBe(-0.1278);

    // Check that the data was saved to the database
    $this->assertDatabaseHas('user_data', [
        'user_id' => $user->id,
        'data_type' => UserDataTypeEnum::LOCATION->value,
    ]);

    // Get the saved data and check its value
    $userData = UserData::where('user_id', $user->id)
        ->where('data_type', UserDataTypeEnum::LOCATION)
        ->first();

    expect($userData->data_value)->toBe([
        'name' => 'London',
        'latitude' => 51.5074,
        'longitude' => -0.1278,
    ]);
});

it('setUserLocation returns null when geocoding fails', function () {
    // Create a user
    $user = User::factory()->create();

    // Mock the HTTP client to return no results
    Http::fake([
        'geocoding-api.open-meteo.com/v1/search*' => Http::response([
            'results' => []
        ], 200)
    ]);

    $locationService = new LocationService();
    $result = $locationService->setUserLocation($user, 'NonExistentPlace');

    expect($result)->toBeNull();

    // Check that no data was saved to the database
    $this->assertDatabaseMissing('user_data', [
        'user_id' => $user->id,
        'data_type' => UserDataTypeEnum::LOCATION->value,
    ]);
});
