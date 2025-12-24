<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected function getCurrentUserCompany()
    {
        return Auth::user()->company();
    }
}
