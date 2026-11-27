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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id()->comment('Subscription identifier');

            $table->foreignId('provider_id')
                ->constrained('providers')
                ->cascadeOnDelete()
                ->comment('Suscribed provider');

            $table->foreignId('subscription_plan_id')
                ->constrained('subscription_plans')
                ->restrictOnDelete()
                ->comment('Selected plan');

            $table->string('status', 20)
                ->default('pending')
                ->comment('Status: pending, active, expired, cancelled, or failed');

            $table->timestamp('started_at')
                ->nullable()
                ->comment('Start timestamp for the plan');

            $table->timestamp('ends_at')
                ->nullable()
                ->comment('End timestamp for the plan');

            $table->string('provider_payment_id', 191)
                ->nullable()
                ->comment('Subscription identifier on the payment platform');

            $table->timestamps();

            $table->index(['provider_id', 'status']);

            $table->comment('Provider subscription history');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
