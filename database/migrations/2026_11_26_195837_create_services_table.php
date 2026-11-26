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
        Schema::create('services', function (Blueprint $table) {
            $table->id()->comment('Service indicator');

            $table->foreignId('client_id')
                ->constrained('users')
                ->restrictOnDelete()
                ->comment('User asking for the service');

            $table->foreignId('provider_id')
                ->nullable()
                ->constrained('providers')
                ->nullOnDelete()
                ->comment('Provider to whom the request is sent or assigned');

            $table->foreignId('craft_id')
                ->constrained('crafts')
                ->restrictOnDelete()
                ->comment('Solicited service');

            $table->text('description')
                ->comment('Required job description');

            $table->string('address', 500)
                ->nullable()
                ->comment('Address or location reference written by client');

            $table->decimal('latitude', 10, 7)
                ->nullable()
                ->comment('Required service location latitude');

            $table->decimal('longitude', 10, 7)
                ->nullable()
                ->comment('Required service location longitude');

            $table->string('status', 30)
                ->default('pending')
                ->comment('Status: pending, accepted, rejected, under_negotiation, confirmed, in_progress, completed, or cancelled');

            $table->decimal('agreed_price', 10, 2)
                ->nullable()
                ->comment('Client and provider agreed price');

            $table->timestamp('service_datetime')
                ->nullable()
                ->comment('Agreed time and day to do the service');

            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['provider_id', 'status']);
            $table->index(['craft_id', 'status']);

            $table->comment('Service requests created by clients');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
