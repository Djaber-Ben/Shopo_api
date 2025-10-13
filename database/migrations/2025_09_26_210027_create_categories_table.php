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
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key (int)
            $table->string('name'); // String column for category name
            $table->string('slug')->nullable()->unique(); // String column for slug, unique to avoid duplicates
            $table->string('image'); // String column for image path or URL
            $table->enum('status', ['active', 'inactive'])->default('active'); // ENUM column for status
            $table->boolean('show')->default(true); // Boolean column for visibility
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
