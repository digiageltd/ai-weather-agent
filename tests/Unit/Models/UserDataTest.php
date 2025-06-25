<?php

use App\Enums\UserDataTypeEnum;
use App\Models\User;
use App\Models\UserData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('user data belongs to a user', function () {
    // Create a user
    $user = User::factory()->create();

    // Create user data
    $userData = UserData::create([
        'user_id' => $user->id,
        'data_type' => UserDataTypeEnum::LOCATION,
        'data_value' => [
            'name' => 'London',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
        ],
    ]);

    // Test the relationship
    expect($userData->user)->toBeInstanceOf(User::class)
        ->and($userData->user->id)->toBe($user->id);
});

it('user data stores data_value as an array', function () {
    // Create a user
    $user = User::factory()->create();

    // Create user data with an array value
    $dataValue = [
        'name' => 'London',
        'latitude' => 51.5074,
        'longitude' => -0.1278,
    ];

    $userData = UserData::create([
        'user_id' => $user->id,
        'data_type' => UserDataTypeEnum::LOCATION,
        'data_value' => $dataValue,
    ]);

    // Reload the user data from the database
    $userData = UserData::find($userData->id);

    // Test that data_value is stored and retrieved as an array
    expect($userData->data_value)->toBe($dataValue)
        ->and($userData->data_value)->toBeArray()
        ->and($userData->data_value['name'])->toBe('London')
        ->and($userData->data_value['latitude'])->toBe(51.5074)
        ->and($userData->data_value['longitude'])->toBe(-0.1278);
});

it('user data can be created with different data types', function () {
    // Create a user
    $user = User::factory()->create();

    // Create user data with a location type
    $userData = UserData::create([
        'user_id' => $user->id,
        'data_type' => UserDataTypeEnum::LOCATION,
        'data_value' => [
            'name' => 'London',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
        ],
    ]);

    // Test the data type
    expect($userData->data_type)->toBe(UserDataTypeEnum::LOCATION)
        ->and($userData->data_type)->toBeInstanceOf(UserDataTypeEnum::class);
});

it('user data can be updated', function () {
    // Create a user
    $user = User::factory()->create();

    // Create user data with initial values
    $userData = UserData::create([
        'user_id' => $user->id,
        'data_type' => UserDataTypeEnum::LOCATION,
        'data_value' => [
            'name' => 'London',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
        ],
    ]);

    // Update the data value
    $newDataValue = [
        'name' => 'Paris',
        'latitude' => 48.8566,
        'longitude' => 2.3522,
    ];

    $userData->data_value = $newDataValue;
    $userData->save();

    // Reload the user data from the database
    $userData = UserData::find($userData->id);

    // Test that data_value was updated
    expect($userData->data_value)->toBe($newDataValue)
        ->and($userData->data_value['name'])->toBe('Paris')
        ->and($userData->data_value['latitude'])->toBe(48.8566)
        ->and($userData->data_value['longitude'])->toBe(2.3522);
});
