<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('provider_locations', function (Blueprint $table) {
            $table->id()->comment('Location identifier');

            $table->foreignId('provider_id')
                ->unique()
                ->constrained('providers')
                ->cascadeOnDelete()
                ->comment('Provider profile located');

            $table->decimal('latitude', 10, 7)
                ->comment('Shared location latitude');

            $table->decimal('longitude', 10, 7)
                ->comment('Shared location longitude');

            $table->timestamp('updated_at')
                ->useCurrent()
                ->comment('Last location date and time');

            $table->comment('Last location shared by each provider');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_locations');
    }
};
