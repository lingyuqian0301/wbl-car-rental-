<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

class BaseAdminController extends Controller
{
    /**
     * Get the view name based on current route (admin or staff)
     */
    protected function getViewName(string $adminView): string
    {
        $routeName = Route::currentRouteName();
        
        // If route starts with 'staff.', return staff view
        if (str_starts_with($routeName, 'staff.')) {
            return str_replace('admin.', 'staff.', $adminView);
        }
        
        return $adminView;
    }
}






