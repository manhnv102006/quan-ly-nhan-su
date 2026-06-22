<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPost;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobPostController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->string('search')->trim();

        $jobPosts = JobPost::query()
            ->with('department')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('department', function ($departmentQuery) use ($search) {
                            $departmentQuery->where('department_name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => JobPost::count(),
            'open' => JobPost::where('status', 'open')->count(),
            'closed' => JobPost::where('status', 'closed')->count(),
        ];

        return view('admin.recruitment.job-posts.index', compact('jobPosts', 'search', 'stats'));
    }
}
