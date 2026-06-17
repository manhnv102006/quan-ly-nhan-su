<?php

namespace App\Http\Controllers;

use App\Models\Department;

class DepartmentController extends Controller
{
    public function departments()
    {
        $departments = Department::all();
    
        return view(
            'admin.departments.index',
            compact('departments')
        );
    }

    public function departmentDetail($id)
    {
        $department = Department::findOrFail($id);
    
        return view(
            'admin.departments.detail',
            compact('department')
        );
    }

    public function departmentDelete($id)
    {
        $department = Department::findOrFail($id);
    
        $department->delete();
    
        return redirect()
            ->route('admin.departments')
            ->with(
                'success',
                'Xóa phòng ban thành công'
            );
    }
}