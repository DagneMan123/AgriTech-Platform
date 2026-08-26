<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class EnsureNotificationsTableExists
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Check if notifications table exists
            if (!Schema::hasTable('notifications')) {
                // Create the table
                Schema::create('notifications', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                    $table->string('type')->default('in-app'); // email, sms, push, in-app
                    $table->string('title');
                    $table->text('message');
                    $table->string('subject')->nullable();
                    $table->text('data')->nullable(); // JSON data for dynamic content
                    $table->string('action_url')->nullable();
                    $table->timestamp('read_at')->nullable();
                    $table->timestamp('sent_at')->nullable();
                    $table->timestamps();
                    
                    // Indexes for common queries
                    $table->index(['user_id', 'read_at']);
                    $table->index(['user_id', 'created_at']);
                    $table->index('type');
                });

                \Illuminate\Support\Facades\Log::info('✓ Notifications table created automatically');
            }
        } catch (\Exception $e) {
            // Log the error but don't fail the request
            \Illuminate\Support\Facades\Log::warning('Could not ensure notifications table exists: ' . $e->getMessage());
        }

        return $next($request);
    }
}
