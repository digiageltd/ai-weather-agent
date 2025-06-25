<?php

namespace App\Services;

use App\Enums\UserDataTypeEnum;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class LocationService
{
    public function getLastKnownLocation(User $user): ?array
    {
        $lastLocation = $user->lastKnownLocation()->first();
        return $lastLocation ? $lastLocation->data_value : null;
    }

    public function askForLocation(): string
    {
        return "I don't know your location yet. Please enter your city or town.";
    }

    /**
     * @throws ConnectionException
     */
    public function setUserLocation(User $user, string $location): ?array
    {
        $response = Http::get('https://geocoding-api.open-meteo.com/v1/search', [
            'name' => $location,
            'count' => 1,
        ]);

        $data = $response->json('results.0');

        if ($data) {
            $user->userData()->create([
                'data_type' => UserDataTypeEnum::LOCATION,
                'data_value' => [
                    'name' => $data['name'] ?? $location,
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                ],
            ]);

            return [
                'name' => $data['name'] ?? $location,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
            ];
        }

        return null;
    }
}
