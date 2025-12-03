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
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("code");
            $table->boolean("enabled")->default(true);
            $table->timestamps();
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId("station_id")->constrained()->onDelete('cascade');
            $table->string("name");
            $table->string("doctor")->nullable();
            $table->string("now")->default('-');
            $table->boolean("english")->default(false);
            $table->boolean("enabled")->default(true);
            $table->timestamps();
        });

        Schema::create('number_dates', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedInteger('U')->default(0);
            $table->unsignedInteger('A')->default(0);
            $table->unsignedInteger('B')->default(0);
            $table->unsignedInteger('C')->default(0);
            $table->unsignedInteger('D')->default(0);
            $table->unsignedInteger('E')->default(0);
            $table->unsignedInteger('H')->default(0);
            $table->unsignedInteger('V')->default(0);
            $table->unsignedInteger('M')->default(0);
            $table->unsignedInteger('Addtional_1')->default(0);
            $table->unsignedInteger('Addtional_2')->default(0);
            $table->unsignedInteger('Addtional_3')->default(0);
            $table->unsignedInteger('Addtional_4')->default(0);
            $table->unsignedInteger('Addtional_5')->default(0);
            $table->timestamps();
        });

        Schema::create('patient_masters', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('vn')->nullable();
            $table->string('hn');
            $table->string('name');
            $table->boolean('english')->default(false);
            $table->string('line')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_prevns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient')->constrained('patient_masters');
            $table->dateTime('date');
            $table->string('type');
            $table->string('number')->nullable();
            $table->string('status')->default('wait');
            $table->dateTime('checkin')->nullable();
            $table->dateTime('call')->nullable();
            $table->dateTime('success')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient')->constrained('patient_masters');
            $table->string('status')->default('wait');
            $table->string('code');
            $table->dateTime('assign')->nullable();
            $table->dateTime('call')->nullable();
            $table->dateTime('success')->nullable();
            $table->text('memo1')->nullable();
            $table->text('memo2')->nullable();
            $table->text('memo3')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient')->constrained('patient_masters');
            $table->text('detail');
            $table->string('user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stations');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('number_dates');
        Schema::dropIfExists('number_masters');
        Schema::dropIfExists('patient_masters');
        Schema::dropIfExists('patient_prevns');
        Schema::dropIfExists('patient_tasks');
        Schema::dropIfExists('patient_logs');
    }
};
