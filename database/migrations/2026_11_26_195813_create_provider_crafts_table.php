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
        Schema::create('provider_crafts', function (Blueprint $table)
        {
            $table->id()->comment('Association identifier');

            $table->foreignId('provider_id')
                ->constrained('providers')
                ->cascadeOnDelete()
                ->comment('Provider profile');

            $table->foreignId('craft_id')
                ->constrained('crafts')
                ->restrictOnDelete()
                ->comment('Craft offered by the provider');

            $table->decimal('price_from', 10, 2)
                ->nullable()
                ->comment('Indicative starting price; may be zero if negotiated');

            $table->text('experience')
                ->nullable()
                ->comment('Supplier_s specific experience in this craft');

            $table->timestamps();

            $table->unique(
                ['provider_id', 'craft_id'],
                'unique_provider_craft'
            );

            $table->comment('Services offered by each provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_crafts');
    }
};
