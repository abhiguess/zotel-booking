<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function search(): View
    {
        return view('search.index');
    }

    public function inventory(): View
    {
        return view('inventory.index');
    }

    public function discounts(): View
    {
        return view('discounts.index');
    }
}
