<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Interview;
use Illuminate\View\View;

class InterviewController extends Controller
{
    public function index(): View
    {
        $interviews = Interview::query()
            ->with(['candidate.jobPost', 'interviewer'])
            ->orderByDesc('interview_date')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Interview::count(),
            'pending' => Interview::where('result', 'pending')->count(),
            'passed' => Interview::where('result', 'passed')->count(),
            'failed' => Interview::where('result', 'failed')->count(),
        ];

        return view('admin.recruitment.interviews.index', compact('interviews', 'stats'));
    }
}
