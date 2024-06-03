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
        Schema::create('shippings', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string("user_uuid");
            $table->text("address");
            $table->string("number");
            $table->string("rt");
            $table->string("rw");
            $table->string("village");
            $table->string("district");
            $table->string("city");
            $table->string("province");
            $table->string("postalCode");
            $table->char("active", 1)->default("Y");
            $table->char("status", 1)->default("N");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shippings');
    }
};
