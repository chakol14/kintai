<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('status')->default('off')->after('work_date');
            $table->integer('break_minutes')->default(0)->after('break_end');
            $table->dateTime('break_started_at')->nullable()->after('break_minutes');
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['status', 'break_minutes', 'break_started_at']);
        });
    }
};
