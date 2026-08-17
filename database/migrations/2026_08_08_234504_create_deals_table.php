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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->integer('quantity')->default(1);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_value', 10, 2)->default(0);
            $table->string('status')->default('PENDING'); // PENDING, NEGOTIATING, WON, LOST, CANCELLED
            $table->integer('probability')->default(50);
            $table->text('notes')->nullable();
            $table->date('expected_close_date')->nullable();
            $table->timestamp('actual_close_date')->nullable();
            $table->timestamp('last_contact_date')->nullable();
            $table->text('loss_reason')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
