<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class UserSessionController extends Controller
{
    /**
     * Bascule la visibilité d'une section de la sidebar
     */
    public function toggleSidebarSection(Request $request)
    {
        $section = $request->input('section');
        $currentState = Session::get("sidebar_{$section}_hidden", false);
        
        Session::put("sidebar_{$section}_hidden", !$currentState);
        
        // Si on bascule la section 'admin', on s'assure que la config suit (legacy support)
        if ($section === 'admin') {
            Session::put("sidebar_config_hidden", !$currentState);
        }
        
        return response()->json([
            'success' => true,
            'hidden' => !$currentState,
            'section' => $section
        ]);
    }
}
