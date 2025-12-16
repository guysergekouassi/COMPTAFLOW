<?php

namespace App\Http\Controllers;
use App\Models\JournalSaisi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FluxTresorerieController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();


        return view('flux_tresorerie');
    }

}
