<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EnsureCropActivitiesTableExists
{
    /**
     * Handle an incoming request.
     *
     * This middleware automatically creates the crop_activities table if it doesn't exist.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only run once per request cycle
        static $checked = false;
        
        if (!$checked && $this->shouldCheck()) {
            try {
                $this->ensureTableExists();
                $checked = true;
            } catch (\Exception $e) {
                Log::warning('Could not ensure crop_activities table exists: ' . $e->getMessage());
                // Don't block the request, just log the warning
            }
        }

        return $next($request);
    }

    private function shouldCheck(): bool
    {
        // Only attempt to check if we're accessing crop-activities routes
        $path = request()->path();
        return strpos($path, 'crop-activities') !== false;
    }

    private function ensureTableExists(): void
    {
        if (!Schema::hasTable('crop_activities')) {
            Log::info('Creating crop_activities table...');
            
            Schema::create('crop_activities', function ($table) {
                $table->id();
                $table->foreignId('crop_id')->constrained('crops')->onDelete('cascade');
                $table->foreignId('farm_id')->constrained('farms')->onDelete('cascade');
                $table->foreignId('farmer_id')->constrained('farmers')->onDelete('cascade');
                $table->enum('activity_type', [
                    'planting',
                    'watering',
                    'fertilizing',
                    'weeding',
                    'pesticide',
                    'pruning',
                    'harvesting',
                    'other'
                ]);
                $table->date('activity_date');
                $table->time('activity_time')->nullable();
                $table->text('description')->nullable();
                $table->decimal('quantity', 10, 2)->nullable();
                $table->string('unit', 50)->nullable();
                $table->decimal('cost', 12, 2)->nullable();
                $table->string('weather', 50)->nullable();
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();

                // Indexes for better query performance
                $table->index(['crop_id', 'activity_date']);
                $table->index(['farm_id', 'activity_date']);
                $table->index(['farmer_id', 'activity_date']);
                $table->index('activity_type');
            });

            Log::info('crop_activities table created successfully');
        }
    }
}
