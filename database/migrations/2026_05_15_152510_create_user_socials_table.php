<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_socials', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('social_id');
            $table->string('service');
            $table->string('token')->nullable();
            $table->string('token_secret')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamps();
        });
    }
};
