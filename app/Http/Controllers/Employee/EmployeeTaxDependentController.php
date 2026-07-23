<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Concerns\ResolvesLinkedEmployee;
use App\Http\Controllers\Controller;
use App\Models\TaxDependent;
use App\Models\TaxDependentDocument;
use App\Rules\CitizenIdNumber;
use App\Services\TaxDependentDocumentService;
use App\Services\TaxDependentRegistrationService;
use App\Support\TaxDependentDocumentRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeTaxDependentController extends Controller
{
    use ResolvesLinkedEmployee;

    public function __construct(
        private readonly TaxDependentRegistrationService $registrations,
        private readonly TaxDependentDocumentService $documents,
    ) {}

    public function index(): View
    {
        $employee = $this->linkedEmployee();

        $dependents = TaxDependent::query()
            ->where('employee_id', $employee->id)
            ->with(['approver', 'rejecter', 'documents'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('employee.tax-dependents.index', [
            'dependents' => $dependents,
            'summary' => $this->registrations->employeeSummary($employee),
            'documentGuide' => TaxDependentDocumentRules::requirementGuide(),
            'canRegister' => $this->registrations->canEmployeeRegister($employee),
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $employee = $this->linkedEmployee();

        if (! $this->registrations->canEmployeeRegister($employee)) {
            return redirect()
                ->route('employee.tax-dependents.index')
                ->with('error', 'Mỗi nhân viên chỉ được đăng ký 1 người phụ thuộc (NPT). Bạn đã có NPT chờ duyệt hoặc đã được duyệt.');
        }

        return view('employee.tax-dependents.create', [
            'relationshipLabels' => TaxDependent::RELATIONSHIP_LABELS,
            'childCategoryLabels' => TaxDependent::CHILD_CATEGORY_LABELS,
            'defaultDeduction' => TaxDependent::DEFAULT_MONTHLY_DEDUCTION,
            'documentGuide' => TaxDependentDocumentRules::requirementGuide(),
            'documentTypeLabels' => TaxDependentDocumentRules::TYPE_LABELS,
            'requiredTypesMap' => [
                'child|minor' => TaxDependentDocumentRules::requiredTypes('child', 'minor'),
                'child|student' => TaxDependentDocumentRules::requiredTypes('child', 'student'),
                'spouse' => TaxDependentDocumentRules::requiredTypes('spouse', null),
                'parent' => TaxDependentDocumentRules::requiredTypes('parent', null),
                'other' => TaxDependentDocumentRules::requiredTypes('other', null),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = $this->linkedEmployee();

        $relationship = (string) $request->input('relationship');
        $childCategory = $relationship === 'child' ? $request->input('child_category') : null;

        $request->merge([
            'id_number' => CitizenIdNumber::normalize($request->input('id_number')),
        ]);

        $validated = $request->validate(array_merge([
            'full_name' => 'required|string|max:255',
            'relationship' => 'required|in:child,spouse,parent,other',
            'date_of_birth' => 'nullable|date',
            'id_number' => ['required', 'string', new CitizenIdNumber],
            'start_date' => 'required|date',
            'note' => 'nullable|string|max:1000',
        ], $this->documents->validationRules($relationship, $childCategory)), array_merge([
            'full_name.required' => 'Vui lòng nhập họ tên người phụ thuộc.',
            'relationship.required' => 'Vui lòng chọn quan hệ.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu giảm trừ.',
            'id_number.required' => 'Vui lòng nhập số CCCD/CMND của người phụ thuộc.',
        ], $this->documents->validationMessages()));

        $this->documents->assertChildAgeMatchesCategory(
            $validated['date_of_birth'] ?? null,
            $childCategory,
            $validated['start_date'] ?? null,
        );

        $validated['monthly_deduction'] = TaxDependent::DEFAULT_MONTHLY_DEDUCTION;
        $validated['child_category'] = $childCategory;

        try {
            $dependent = $this->registrations->submitRequest($employee, $validated, (int) auth()->id());
            $this->documents->attachFromRequest($dependent, $employee, $request->all());
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $field = str_contains($e->getMessage(), 'CCCD') ? 'id_number' : 'full_name';

            return back()->withErrors([$field => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('employee.tax-dependents.index')
            ->with('success', 'Đã gửi đăng ký NPT và giấy tờ tới kế toán. Sau khi duyệt, người phụ thuộc sẽ được áp dụng giảm trừ thuế (GT phụ thuộc).');
    }

    public function show(TaxDependent $taxDependent): View
    {
        $employee = $this->linkedEmployee();
        abort_unless($taxDependent->employee_id === $employee->id, 403);

        $taxDependent->load(['approver', 'rejecter', 'requester', 'documents']);

        return view('employee.tax-dependents.show', [
            'dependent' => $taxDependent,
        ]);
    }

    public function downloadDocument(TaxDependent $taxDependent, TaxDependentDocument $document): StreamedResponse
    {
        $employee = $this->linkedEmployee();
        abort_unless($taxDependent->employee_id === $employee->id, 403);
        abort_unless((int) $document->tax_dependent_id === (int) $taxDependent->id, 404);

        $role = auth()->user()?->role?->name;
        abort_unless($this->documents->userCanDownload($document, (int) auth()->id(), $role), 403);

        return $this->documents->downloadResponse($document);
    }
}
