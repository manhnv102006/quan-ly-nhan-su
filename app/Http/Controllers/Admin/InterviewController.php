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
            ->with(['candidate.jobPost.department', 'interviewer'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Interview::count(),
            'pending' => Interview::where('result', 'pending')->count(),
            'passed' => Interview::where('result', 'passed')->count(),
            'failed' => Interview::where('result', 'failed')->count(),
            'scheduled' => Interview::where('status', 'scheduled')->count(),
            'completed' => Interview::where('status', 'completed')->count(),
            'cancelled' => Interview::where('status', 'cancelled')->count(),
            'no_show' => Interview::where('status', 'no_show')->count(),
        ];

        return view('admin.recruitment.interviews.index', compact('interviews', 'stats'));
    }
}
