<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RemoveUiNameOfReactionTypesAndRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('tag', 'name');
        });

        Schema::table('reactions_types', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('tag', 'name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // roles table
        // ---------------------------------------------------------------------
        Schema::table('roles', function (Blueprint $table) {
            $table->renameColumn('name', 'tag');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->string('name', 20);
        });

        DB::table('roles')->where('tag', 'member')->update(['name' => 'Membre']);
        DB::table('roles')->where('tag', 'admin')->update(['name' => 'Administrateur']);

        Schema::table('roles', function (Blueprint $table) {
            $table->unique('name');
        });

        // reactions_types table
        // ---------------------------------------------------------------------
        Schema::table('reactions_types', function (Blueprint $table) {
            $table->renameColumn('name', 'tag');
        });

        Schema::table('reactions_types', function (Blueprint $table) {
            $table->string('name', 10);
        });

        DB::table('reactions_types')->where('tag', 'like')->update(['name' => "J'aime"]);
        DB::table('reactions_types')->where('tag', 'love')->update(['name' => "J'adore"]);
        DB::table('reactions_types')->where('tag', 'haha')->update(['name' => "Ha ha"]);
        DB::table('reactions_types')->where('tag', 'wow')->update(['name' => "Wouah"]);
        DB::table('reactions_types')->where('tag', 'sad')->update(['name' => "Triste"]);
        DB::table('reactions_types')->where('tag', 'angry')->update(['name' => "En colère"]);

        Schema::table('reactions_types', function (Blueprint $table) {
            $table->unique('name');
        });
    }
}
