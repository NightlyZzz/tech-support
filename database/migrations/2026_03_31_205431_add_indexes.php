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
        Schema::table('tickets', function (Blueprint $table) {
            $table->index('sender_id');
            $table->index('employee_id');
            $table->index('ticket_status_id');
            $table->index('ticket_type_id');
            $table->index(['employee_id', 'ticket_status_id']);
        });

        Schema::table('ticket_logs', function (Blueprint $table) {
            $table->index('ticket_id');
            $table->index('sender_id');
            $table->index('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
