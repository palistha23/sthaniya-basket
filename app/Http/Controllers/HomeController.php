<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index() {

        $products = Product::latest()->paginate(20); 

        return view('home', [
            'products' => $products,
        ]);
    }
}
