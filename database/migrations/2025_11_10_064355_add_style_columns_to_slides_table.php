<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStyleColumnsToSlidesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->boolean('heading_bold')->default(false)->after('heading');
            $table->boolean('heading_italic')->default(false)->after('heading_bold');
            $table->boolean('sub_heading_bold')->default(false)->after('sub_heading');
            $table->boolean('sub_heading_italic')->default(false)->after('sub_heading_bold');
            $table->boolean('cto_bold')->default(false)->after('cto');
            $table->boolean('cto_italic')->default(false)->after('cto_bold');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->dropColumn(['heading_bold', 'heading_italic', 'sub_heading_bold', 'sub_heading_italic', 'cto_bold', 'cto_italic']);
        });
    }
};
