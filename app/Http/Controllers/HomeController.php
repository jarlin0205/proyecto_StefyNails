<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Service;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::with(['services' => function($query) {
            $query->where('is_active', true)->with('images');
        }])->get();

        return view('welcome', compact('categories'));
    }
}
