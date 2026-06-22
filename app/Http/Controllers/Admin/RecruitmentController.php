<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class RecruitmentController extends Controller
{
    public function index(): View
    {
        return view('admin.recruitment.index');
    }
}
