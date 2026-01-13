<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function logActivity($action, $description)
    {
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}
