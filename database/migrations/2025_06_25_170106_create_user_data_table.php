<?php

use App\Enums\UserDataTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained(
                    table: 'users',
                )
                ->cascadeOnDelete();
            $table->enum('data_type', array_map(
                callback:  fn(UserDataTypeEnum $enum) => $enum->value,
                array: UserDataTypeEnum::cases()
            ));
            $table->json('data_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_data');
    }
};
