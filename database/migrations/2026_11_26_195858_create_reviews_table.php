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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id()->comment('Review id');

            $table->foreignId('service_id')
                ->unique()
                ->constrained('services')
                ->cascadeOnDelete()
                ->comment('Completed job currently being evaluated');

            $table->foreignId('client_id')
                ->constrained('users')
                ->restrictOnDelete()
                ->comment('Client writing the review');

            $table->foreignId('provider_id')
                ->constrained('providers')
                ->restrictOnDelete()
                ->comment('Rated provider');

            $table->unsignedTinyInteger('stars')
                ->comment('Integer rating from 1 to 5 stars');

            $table->text('comment')
                ->nullable()
                ->comment('Optional comment about the service');

            $table->timestamps();

            $table->index('provider_id');

            $table->comment('Service ratings and comments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
