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
        Schema::create('providers', function (Blueprint $table) {
            $table->id()->comment('Provider profile id');

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('Profile user owner');

            $table->text('description')
                ->nullable()
                ->comment('Provider presentation and experience');

            $table->string('photo_url', 2048)
                ->nullable()
                ->comment('Image profile URL');

            $table->string('ine_url', 2048)
                ->nullable()
                ->comment('Image INE URL');

            $table->string('no_crime_url', 2048)
                ->nullable()
                ->comment('Image no crime document URL');

            $table->boolean('verified')
                ->default(false)
                ->comment('Shows if the provider identity was verified');

            $table->boolean('available')
                ->default(false)
                ->comment('Shows if the provider wants to be found');

            $table->unsignedSmallInteger('km_radius')
                ->default(10)
                ->comment('Max radius the provider is willing to attend a service');

            $table->decimal('avg_rating', 3, 2)
                ->default(0)
                ->comment('Stars average from reviews (0-5)');

            $table->unsignedInteger('reviews_count')
                ->default(0)
                ->comment('Number of reviews received. This value is calculated');

            $table->timestamps();

            $table->comment('Provider public information and preferences');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
