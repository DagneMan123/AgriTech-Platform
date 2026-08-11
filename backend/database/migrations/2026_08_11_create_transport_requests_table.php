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
        Schema::create('transport_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('farmers')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->string('product_name');
            $table->decimal('quantity', 10, 2);
            $table->string('unit'); // kg, tonnes, bags, bundles, pieces
            $table->text('pickup_location');
            $table->text('delivery_location');
            $table->date('pickup_date');
            $table->date('delivery_date');
            $table->string('vehicle_type')->default('other'); // bike, car, truck, van, cart, other
            $table->boolean('special_handling')->default(false);
            $table->text('handling_instructions')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('status')->default('pending'); // pending, assigned, in_transit, delivered, cancelled
            $table->foreignId('assigned_transporter_id')->nullable()->constrained('transporters')->onDelete('set null');
            $table->foreignId('assigned_vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            $table->timestamps();

            $table->index('farmer_id');
            $table->index('status');
            $table->index('product_id');
            $table->index('assigned_transporter_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_requests');
    }
};
