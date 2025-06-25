<?php

use App\Enums\UserDataTypeEnum;
use App\Models\Conversation;
use App\Models\User;
use App\Models\UserData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('user can have user data', function () {
    // Create a user
    $user = User::factory()->create();

    // Create user data
    $userData = $user->userData()->create([
        'data_type' => UserDataTypeEnum::LOCATION,
        'data_value' => [
            'name' => 'London',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
        ],
    ]);

    // Test the relationship
    expect($user->userData)->toHaveCount(1)
        ->and($user->userData->first())->toBeInstanceOf(UserData::class)
        ->and($user->userData->first()->data_type)->toBe(UserDataTypeEnum::LOCATION->value)
        ->and($user->userData->first()->data_value)->toBe([
            'name' => 'London',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
        ]);
});

it('user can have multiple user data entries', function () {
    // Create a user
    $user = User::factory()->create();

    // Create multiple user data entries
    $user->userData()->create([
        'data_type' => UserDataTypeEnum::LOCATION,
        'data_value' => [
            'name' => 'London',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
        ],
    ]);

    $user->userData()->create([
        'data_type' => UserDataTypeEnum::LOCATION,
        'data_value' => [
            'name' => 'Paris',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
        ],
    ]);

    // Test the relationship
    expect($user->userData)->toHaveCount(2)
        ->and($user->userData[0]->data_value['name'])->toBe('London')
        ->and($user->userData[1]->data_value['name'])->toBe('Paris');
});

it('user can have conversations', function () {
    // Create a user
    $user = User::factory()->create();

    // Create conversations for the user
    $conversation1 = Conversation::create([
        'user_id' => $user->id,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello 1'],
        ],
    ]);

    $conversation2 = Conversation::create([
        'user_id' => $user->id,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello 2'],
        ],
    ]);

    // Test the relationship
    expect($user->conversations)->toHaveCount(2)
        ->and($user->conversations[0])->toBeInstanceOf(Conversation::class)
        ->and($user->conversations[1])->toBeInstanceOf(Conversation::class)
        ->and($user->conversations[0]->messages[0]['content'])->toBe('Hello 1')
        ->and($user->conversations[1]->messages[0]['content'])->toBe('Hello 2');
});

it('lastKnownLocation returns the most recent location', function () {
    // Create a user
    $user = User::factory()->create();

    // Create older location data
    $user->userData()->create([
        'data_type' => UserDataTypeEnum::LOCATION,
        'data_value' => [
            'name' => 'London',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
        ],
        'created_at' => now()->subDays(2),
    ]);

    // Create newer location data
    $user->userData()->create([
        'data_type' => UserDataTypeEnum::LOCATION,
        'data_value' => [
            'name' => 'Paris',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
        ],
        'created_at' => now()->subDay(),
    ]);

    // Test the lastKnownLocation method
    $lastLocation = $user->lastKnownLocation()->first();
    expect($lastLocation)->toBeInstanceOf(UserData::class)
        ->and($lastLocation->data_value['name'])->toBe('Paris');
});

it('lastKnownLocation returns null when no location exists', function () {
    // Create a user without location data
    $user = User::factory()->create();

    // Test the lastKnownLocation method
    $lastLocation = $user->lastKnownLocation()->first();
    expect($lastLocation)->toBeNull();
});
