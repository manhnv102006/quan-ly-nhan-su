<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CandidateController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->string('search')->trim();

        $candidates = Candidate::query()
            ->with('jobPost')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhereHas('jobPost', function ($jobPostQuery) use ($search) {
                            $jobPostQuery->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Candidate::count(),
            'new' => Candidate::where('status', 'new')->count(),
            'interview' => Candidate::where('status', 'interview')->count(),
            'passed' => Candidate::where('status', 'passed')->count(),
        ];

        return view('admin.recruitment.candidates.index', compact('candidates', 'stats', 'search'));
    }
}
