<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TermsOfServiceController extends Controller
{
    /**
     * Отображение страницы условий использования
     */
    public function index()
    {
        return view('terms-of-service');
    }
}