<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use app\model\DoctorSchedule;

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
