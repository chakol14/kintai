<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('work_date');
            $table->dateTime('requested_clock_in')->nullable();
            $table->dateTime('requested_clock_out')->nullable();
            $table->dateTime('requested_break_start')->nullable();
            $table->dateTime('requested_break_end')->nullable();
            $table->string('reason');
            $table->string('status')->default('pending');
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'work_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_requests');
    }
};
