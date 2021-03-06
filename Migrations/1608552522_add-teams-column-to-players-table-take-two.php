<?php

namespace EvoSC\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

class AddTeamsColumnToPlayersTableTakeTwo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(Builder $schemaBuilder)
    {
        $schemaBuilder->table('players', function (Blueprint $table) {
            $table->dropColumn('team');
        });
        $schemaBuilder->table('players', function (Blueprint $table) {
            $table->tinyInteger('team')->default(-1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(Builder $schemaBuilder)
    {
        $schemaBuilder->table('players', function (Blueprint $table) {
        });
    }
}