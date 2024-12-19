<?php

use App\Models\ReactionType;
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

        ReactionType::insert([
            ['tag' => 'like', 'name' => "J'aime"],
            ['tag' => 'love', 'name' => "J'adore"],
            ['tag' => 'haha', 'name' => "Ha ha"],
            ['tag' => 'wow', 'name' => "Wouah"],
            ['tag' => 'sad', 'name' => "Triste"],
            ['tag' => 'angry', 'name' => "En colère"]
        ]);
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
