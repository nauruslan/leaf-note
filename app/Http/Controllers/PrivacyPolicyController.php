<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrivacyPolicyController extends Controller
{
    /**
     * Отображение страницы политики конфиденциальности
     */
    public function index()
    {
        return view('privacy-policy');
    }
}