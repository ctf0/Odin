<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditsPivotTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('audits_pivot', function (Blueprint $table) {
            $table->increments('id');
            $table->string('event');
            $table->morphs('auditable');
            $table->morphs('relation');
            $table->timestamp('parent_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('audits_pivot');
    }
}
