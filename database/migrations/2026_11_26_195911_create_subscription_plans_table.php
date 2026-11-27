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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id()->comment('Plan id');

            $table->string('name', 100)
                ->comment('Plan name');

            $table->string('periodicity', 20)
                ->comment('How often the payment is charged');

            $table->decimal('price', 10, 2)
                ->comment('Plan price');

            $table->tinyInteger('discount')
                ->nullable()
                ->comment('Plan price discount for promotions as an integer percentage');

            $table->char('currency', 3)
                ->default('MXN')
                ->comment('Code currency, i.e. USD o MXN');

            $table->boolean('active')
                ->default(true)
                ->comment('Shows if the plan is eligible');

            $table->timestamps();

            $table->comment('Subscription plans available for providers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
