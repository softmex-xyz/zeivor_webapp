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
        Schema::create('crafts', function (Blueprint $table) {
            $table->id()->comment('Craft identifier');

            $table->string('name', 100)
                ->unique()
                ->comment('Craft name, i.e. plumbing');

            $table->text('description')
                ->nullable()
                ->comment('Service type description');

            $table->string('map_color', 9)
                ->nullable()
                ->comment('Hex color to identify the craft in map');

            $table->boolean('active')
                ->default(true)
                ->comment('Shows if the craft is selectable');

            $table->timestamps();

            $table->comment('Crafts catalogue provided by the app');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crafts');
    }
};
