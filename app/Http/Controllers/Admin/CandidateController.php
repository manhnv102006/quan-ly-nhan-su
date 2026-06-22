<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use Illuminate\View\View;

class CandidateController extends Controller
{
    public function index(): View
    {
        $candidates = Candidate::query()
            ->with('jobPost')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Candidate::count(),
            'new' => Candidate::where('status', 'new')->count(),
            'interview' => Candidate::where('status', 'interview')->count(),
            'passed' => Candidate::where('status', 'passed')->count(),
        ];

        return view('admin.recruitment.candidates.index', compact('candidates', 'stats'));
    }
}
