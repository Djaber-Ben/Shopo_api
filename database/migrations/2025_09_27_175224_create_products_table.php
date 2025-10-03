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
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key (int)
            $table->foreignId('store_id')->constrained()->onDelete('cascade'); // Foreign key to stores table
            $table->string('title'); // String column for product title
            $table->string('slug')->nullable()->unique(); // String column for slug, unique to avoid duplicates
            $table->string('image')->nullable(); // String column for primary image, nullable
            $table->text('description')->nullable(); // Text column for description, nullable
            $table->text('short_description')->nullable(); // Text column for short description, nullable
            $table->text('shipping_returns')->nullable(); // Text column for shipping and returns, nullable
            $table->text('related_products')->nullable(); // Text column for related products, nullable
            $table->double('price'); // Double column for price
            $table->double('compare_price')->nullable(); // Double column for compare price, nullable
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null'); // Foreign key to categories table, nullable
            // $table->foreignId('subcategory_id')->nullable()->constrained()->onDelete('set null'); // Foreign key to subcategories table, nullable
            $table->enum('is_featured', ['yes', 'no'])->default('no'); // ENUM column for featured status
            $table->integer('qty')->nullable(); // Integer column for quantity, nullable
            $table->enum('track_qty', ['yes', 'no'])->default('yes'); // ENUM column for tracking quantity
            $table->enum('status', ['active', 'inactive', 'out_of_stock'])->default('active'); // ENUM column for status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
