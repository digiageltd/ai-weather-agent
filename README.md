# AI Weather Chatbot – Laravel 12 (CLI)

---

## 1 · Quick-start

| Prerequisite   | Version |
|----------------|---------|
| **PHP**        | ≥ 8.2   |
| SQLite/MySQL   | any     |

```bash
git clone https://github.com/digiageltd/ai-weather-bot.git
cd ai-weather-bot

composer install

cp .env.example .env
# ── edit .env ─────────────────────────────────────────────
# PRISM_OPENAI_API_KEY=sk-xxxxxxxxxxxxxxxxxxxxxxxx
# (optional) PRISM_OPENAI_MODEL=gpt-4o
# ---------------------------------------------------------

php artisan key:generate
php artisan migrate
php artisan db:seed

php artisan weather:chat         # interactive CLI 🔮

php artisan weather:chat --user_id=1    # talk as user 1

./vendor/bin/pest
```

Optional:  
Run as a different user (default is 1).

---

## 2 · What ships in this MVP

- **Laravel 12**, CLI only.
- **Prism PHP** (OpenAI function-calling: `get_weather(city)`, `ask_location()`).
- **Open-Meteo API** for current weather (no key required).
- **User memory**: Locations in `user_data` table, survives new sessions.
- **Conversation memory**: Optional `Conversation` table, last 10 turns replayed.
- **Streaming**: `streamUserMessage()` (see code).
- **Unit tests**: Pest, all external calls mocked.

---

## 3 · Intentionally **not** implemented

| Omitted            | Reason                                           |
|--------------------|--------------------------------------------------|
| Web UI             | Spec called for CLI-only.                        |
| IP geolocation     | Needs 3rd-party API; out-of-scope for MVP.       |
| Docker & CI        | Repo kept focused and light for first iteration. |
| Caching/rate-limit | Open-Meteo/OpenAI generous for demo.             |
| Multi-LLM support  | Only OpenAI provider wired for now.              |

---

## 4 · What the next dev should build

1. **Web interface** (Vue, Livewire, Inertia, Blade, etc.)
2. **Caching layer** (Redis: cache same lat/lon requests).
3. **Docker & CI/CD** (Dockerfile, GitHub Actions for lint/test/deploy).
4. **Session store** (move `$waitingForLocation` to Redis or DB).
5. **Conversation memory** (DB, token-aware trimming).
6. **Robust geocoder** (fallback, fuzzy matching).
7. **Multi-provider** (env `PRISM_PROVIDER=`, wire other LLMs).

---

## 5 · Where to extend the code

| Area                | File/Class                              |
|---------------------|-----------------------------------------|
| Chat/AI logic       | `app/Services/ChatService.php`          |
| Weather API logic   | `app/Services/WeatherService.php`       |
| Location handling   | `app/Services/LocationService.php`      |
| User data model     | `app/Models/UserData.php`               |
| CLI entrypoint      | `app/Console/Commands/WeatherChat.php`  |
| Tests               | `tests/Helpers`, `tests/Unit`           |

---

## 6 · Design decisions & caveats

- **Function-calling tools** for LLM, easy to extend.
- **user_data** is flexible for any future attribute.
- **In-memory** session state (`$waitingForLocation`) is CLI-only.
- Only last 10 messages replayed (token limits).
- **Open-Meteo**: free, current conditions only.
- **All tests mock external calls** for speed/reliability.
- **Streaming** is optional; non-streaming fallback is always available.

---
