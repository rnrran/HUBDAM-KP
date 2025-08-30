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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('gaji_bersih', 15, 2);
            $table->date('tanggal_dibayarkan')->default(now());
            
            // Potongan fields
            $table->decimal('mdr_bws_bri', 15, 2)->nullable();
            $table->decimal('btn', 15, 2)->nullable();
            $table->decimal('twp', 15, 2)->nullable();
            $table->decimal('persit', 15, 2)->nullable();
            $table->decimal('ikka_persit', 15, 2)->nullable();
            $table->decimal('koperasi', 15, 2)->nullable();
            $table->decimal('barak', 15, 2)->nullable();
            $table->decimal('kowad', 15, 2)->nullable();
            $table->decimal('titipan', 15, 2)->nullable();
            $table->decimal('tenes', 15, 2)->nullable();
            $table->decimal('remaja', 15, 2)->nullable();
            $table->decimal('galon', 15, 2)->nullable();
            $table->decimal('sosial', 15, 2)->nullable();
            $table->decimal('pns', 15, 2)->nullable();
            $table->decimal('bel_wajib_kop', 15, 2)->nullable();
            
            // 5 customizable fields
            $table->string('custom_1_name')->nullable();
            $table->decimal('custom_1_value', 15, 2)->nullable();
            $table->string('custom_2_name')->nullable();
            $table->decimal('custom_2_value', 15, 2)->nullable();
            $table->string('custom_3_name')->nullable();
            $table->decimal('custom_3_value', 15, 2)->nullable();
            $table->string('custom_4_name')->nullable();
            $table->decimal('custom_4_value', 15, 2)->nullable();
            $table->string('custom_5_name')->nullable();
            $table->decimal('custom_5_value', 15, 2)->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'tanggal_dibayarkan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};