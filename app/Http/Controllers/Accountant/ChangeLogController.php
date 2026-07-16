<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\ModuleChangeLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChangeLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ModuleChangeLog::query()
            ->with(['employee.department', 'user.role'])
            ->orderByDesc('created_at');

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->integer('employee_id'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('field_label', 'like', "%{$search}%")
                    ->orWhere('old_value', 'like', "%{$search}%")
                    ->orWhere('new_value', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhereHas('employee', fn ($e) => $e
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->paginate(25)->withQueryString();

        $employees = Employee::query()
            ->whereIn('status', ['active', 'inactive', 'resigned'])
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code']);

        $stats = [
            'total' => ModuleChangeLog::count(),
            'insurance' => ModuleChangeLog::where('module', ModuleChangeLog::MODULE_INSURANCE)->count(),
            'tax' => ModuleChangeLog::where('module', ModuleChangeLog::MODULE_TAX)->count(),
            'payroll' => ModuleChangeLog::where('module', ModuleChangeLog::MODULE_PAYROLL)->count(),
            'advance' => ModuleChangeLog::where('module', ModuleChangeLog::MODULE_ADVANCE)->count(),
            'contract' => ModuleChangeLog::where('module', ModuleChangeLog::MODULE_CONTRACT)->count(),
        ];

        return view('accountant.change-logs.index', [
            'logs' => $logs,
            'employees' => $employees,
            'stats' => $stats,
            'filters' => $request->only(['module', 'employee_id', 'search', 'from_date', 'to_date']),
        ]);
    }
}
