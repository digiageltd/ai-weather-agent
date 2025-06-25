<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\Facades\Tool;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;

class ChatService
{
    // CLI-session memory: user-id ➜ waiting
    protected array $waitingForLocation = [];

    protected WeatherService $weatherService;
    protected LocationService $locationService;

    public function __construct()
    {
        $this->weatherService = new WeatherService();
        $this->locationService = new LocationService();
    }

    /**
     * @throws ConnectionException
     */
    public function handleUserMessage(string $message, int $userId, ?Conversation $conversation = null): string
    {
        $user = User::findOrFail($userId);

        $this->waitingForLocation[$userId] ??= false;

        if ($this->waitingForLocation[$userId]) {
            $loc = $this->locationService->setUserLocation($user, $message);
            $this->waitingForLocation[$userId] = false;

            return $loc
                ? $this->weatherService->getWeatherForCoordinates(
                    (float) $loc['latitude'],
                    (float) $loc['longitude'],
                    $loc['name']
                )
                : "Sorry, I couldn't find that place. Try another city name?";
        }

        $contextMessages = [];

        if ($stored = $this->locationService->getLastKnownLocation($user)) {
            $memory = sprintf(
                'The user’s last known location is %s at %.4f, %.4f.',
                $stored['name'], $stored['latitude'], $stored['longitude']
            );
            $contextMessages[] = new AssistantMessage($memory);
        }

        if ($conversation) {
            foreach (array_slice($conversation->messages ?? [], -10) as $m) {
                $contextMessages[] = ($m['role'] ?? '') === 'user'
                    ? new UserMessage($m['content'])
                    : new AssistantMessage($m['content']);
            }
        }

        $response = Prism::text()
            ->using(Provider::OpenAI, config('prism.openai.model', 'gpt-4o'))
            ->withMaxSteps(3)
            ->withSystemPrompt(
                "You are a concise CLI weather assistant.\n".
                "• If the user provides a city, call get_weather.\n".
                "• If they say “my location” and no city is stored, call ask_location.\n".
                "• Otherwise, reply helpfully."
            )
            ->withMessages(array_merge(
                $contextMessages,
                [new UserMessage($message)])
            )
            ->withTools([
                $this->defineWeatherTool($user),
                $this->defineAskLocationTool($user),
            ])
            ->asText();

        return $response->text;
    }

    private function defineWeatherTool(User $user): \Prism\Prism\Tool
    {
        return Tool::as('get_weather')
            ->for('Return current weather for a city')
            ->withStringParameter('city', 'Name of the city or town')
            ->using(function (string $city) use ($user): string {
                if (strtolower($city) === 'my location') {
                    $stored = $this->locationService->getLastKnownLocation($user);
                    if (!$stored) {
                        $this->waitingForLocation[$user->id] = true;
                        return $this->locationService->askForLocation();
                    }

                    return $this->weatherService->getWeatherForCoordinates(
                        (float) $stored['latitude'],
                        (float) $stored['longitude'],
                        $stored['name']
                    );
                }

                // geocode → save → weather
                $loc = $this->locationService->setUserLocation($user, $city);

                return $loc
                    ? $this->weatherService->getWeatherForCoordinates(
                        (float) $loc['latitude'],
                        (float) $loc['longitude'],
                        $loc['name']
                    )
                    : "Sorry, I couldn't find weather data for {$city}.";
            });
    }


    private function defineAskLocationTool(User $user): \Prism\Prism\Tool
    {
        return Tool::as('ask_location')
            ->for('Ask user to provide their city or town')
            ->using(function () use ($user): string {
                $this->waitingForLocation[$user->id] = true;
                return $this->locationService->askForLocation();
            });
    }
}
