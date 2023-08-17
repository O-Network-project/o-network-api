<?php

use Database\Seeders\ReactionTypeSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReactionsTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reactions_types', function (Blueprint $table) {
            $table->id();
            $table->string('tag', 5)->unique();
            $table->string('name', 10)->unique();
        });

        // As reactions types are not example data, this seeder is not run from
        // the DatabaseSeeder but directly here, after the creation of the table
        $seeder = new ReactionTypeSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reactions_types');
    }
}
