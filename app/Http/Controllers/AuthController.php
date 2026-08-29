<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login_signup.login');
    }

    public function showSignup()
    {
        return view('login_signup.signup');
    }
}
