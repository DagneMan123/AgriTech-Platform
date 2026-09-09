<?php
// app/Http/Controllers/Controller.php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Farmer;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Get or create farmer record for the authenticated user
     * 
     * @return \App\Models\Farmer|null
     */
    protected function getOrCreateFarmer()
    {
        $user = auth()->user();
        
        if (!$user) {
            return null;
        }

        $farmer = Farmer::where('user_id', $user->id)->first();
        
        if (!$farmer) {
            // Auto-create farmer profile for users with farmer role
            $farmer = Farmer::create([
                'user_id' => $user->id,
                'region' => 'Unknown',
                'zone' => 'Unknown',
                'woreda' => 'Unknown',
            ]);
        }
        
        return $farmer;
    }
}

