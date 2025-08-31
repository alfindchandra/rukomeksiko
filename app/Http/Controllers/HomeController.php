<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('welcome');
    }
    
    public function login()
    {
        return view('login');
    }
    public function postLogin()
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
    }
}
