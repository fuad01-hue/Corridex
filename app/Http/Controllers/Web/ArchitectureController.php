<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ArchitectureController extends Controller
{
    /**
     * Display the System Architecture and Database ERD portfolio page.
     */
    public function index()
    {
        return view('architecture.index');
    }
}
