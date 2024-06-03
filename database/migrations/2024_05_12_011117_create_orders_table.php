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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string("user_uuid");
            $table->string("parent_uuid");
            $table->string("product_uuid");
            $table->text("note")->nullable();
            $table->integer("total");
            $table->decimal("price", 14, 2);
            $table->dateTime("order_date");
            $table->char("status", 1)->default("O");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
