<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CreateNotificationsTable extends Command
{
    protected $signature = 'notifications:create-table';
    protected $description = 'Create the notifications table if it does not exist';

    public function handle()
    {
        try {
            if (!Schema::hasTable('notifications')) {
                Schema::create('notifications', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                    $table->string('type'); // email, sms, push, in-app
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

                $this->info('✓ Notifications table created successfully');
            } else {
                $this->info('✓ Notifications table already exists');
            }
        } catch (\Exception $e) {
            $this->error('✗ Error creating notifications table: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
