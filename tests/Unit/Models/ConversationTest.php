<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('conversation belongs to a user', function () {
    // Create a user
    $user = User::factory()->create();

    // Create a conversation for the user
    $conversation = Conversation::create([
        'user_id' => $user->id,
        'messages' => [],
    ]);

    // Test the relationship
    expect($conversation->user)->toBeInstanceOf(User::class)
        ->and($conversation->user->id)->toBe($user->id);
});

it('conversation messages are stored as an array', function () {
    // Create a user
    $user = User::factory()->create();

    // Create a conversation with messages
    $messages = [
        ['role' => 'user', 'content' => 'Hello'],
        ['role' => 'assistant', 'content' => 'Hi there!'],
    ];

    $conversation = Conversation::create([
        'user_id' => $user->id,
        'messages' => $messages,
    ]);

    // Reload the conversation from the database
    $conversation = Conversation::find($conversation->id);

    // Test that messages are stored and retrieved as an array
    expect($conversation->messages)->toBe($messages)
        ->and($conversation->messages)->toBeArray()
        ->and($conversation->messages[0]['role'])->toBe('user')
        ->and($conversation->messages[0]['content'])->toBe('Hello')
        ->and($conversation->messages[1]['role'])->toBe('assistant')
        ->and($conversation->messages[1]['content'])->toBe('Hi there!');
});

it('conversation can be created with empty messages array', function () {
    // Create a user
    $user = User::factory()->create();

    // Create a conversation with empty messages
    $conversation = Conversation::create([
        'user_id' => $user->id,
        'messages' => [],
    ]);

    // Reload the conversation from the database
    $conversation = Conversation::find($conversation->id);

    // Test that messages are an empty array
    expect($conversation->messages)->toBe([])
        ->and($conversation->messages)->toBeArray()
        ->and($conversation->messages)->toBeEmpty();
});

it('conversation messages can be updated', function () {
    // Create a user
    $user = User::factory()->create();

    // Create a conversation with initial messages
    $initialMessages = [
        ['role' => 'user', 'content' => 'Hello'],
    ];

    $conversation = Conversation::create([
        'user_id' => $user->id,
        'messages' => $initialMessages,
    ]);

    // Update the messages
    $newMessages = [
        ['role' => 'user', 'content' => 'Hello'],
        ['role' => 'assistant', 'content' => 'Hi there!'],
        ['role' => 'user', 'content' => 'How are you?'],
    ];

    $conversation->messages = $newMessages;
    $conversation->save();

    // Reload the conversation from the database
    $conversation = Conversation::find($conversation->id);

    // Test that messages were updated
    expect($conversation->messages)->toBe($newMessages)
        ->and(count($conversation->messages))->toBe(3);
});
