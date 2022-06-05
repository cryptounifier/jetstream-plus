<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIpAddressesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ip_addresses', function (Blueprint $table): void {
            $table->ipAddress('ip_address')->primary();
            $table->string('asn', 7);
            $table->string('continent');
            $table->string('country')->nullable();
            $table->string('country_code', 2);
            $table->string('region')->nullable();
            $table->string('region_code', 8);
            $table->string('city');
            $table->decimal('latitude', 11, 8);
            $table->decimal('longitude', 11, 8);
            $table->tinyInteger('risk');
            $table->boolean('proxy');
            $table->string('driver');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_addresses');
    }
}
