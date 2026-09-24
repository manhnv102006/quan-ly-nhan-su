<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

class StoreEmployeeEarlyLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->isEmployee() || $user?->isManager() || false;
    }

    public function rules(): array
    {
        return [
            'request_date' => ['required', 'date', 'after_or_equal:today'],
            'leave_time' => ['required', 'date_format:H:i'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'request_date.required' => 'Vui lòng chọn ngày xin về sớm.',
            'request_date.after_or_equal' => 'Ngày xin về sớm phải từ hôm nay trở đi.',
            'leave_time.required' => 'Vui lòng chọn giờ muốn về sớm.',
            'leave_time.date_format' => 'Giờ không hợp lệ.',
            'reason.required' => 'Vui lòng nhập lý do.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $employee = $this->user()?->employee;

            if (! $employee instanceof Employee) {
                return;
            }

            $requestDate = (string) $this->input('request_date');
            $leaveTime = (string) $this->input('leave_time');

            if ($requestDate === '' || $leaveTime === '') {
                return;
            }

            $shifts = $employee->shiftsOnDate($requestDate);

            if ($shifts->isEmpty()) {
                $validator->errors()->add(
                    'request_date',
                    $this->noShiftMessage($requestDate),
                );

                return;
            }

            if (! $this->leaveTimeWithinShifts($leaveTime, $shifts)) {
                $validator->errors()->add(
                    'leave_time',
                    $this->leaveTimeOutsideShiftMessage($shifts),
                );
            }
        });
    }

    private function noShiftMessage(string $requestDate): string
    {
        return 'Ngày '.Carbon::parse($requestDate)->format('d/m/Y').' không có ca làm. Vui lòng chọn ngày khác.';
    }

    /**
     * @param  Collection<int, \App\Models\EmployeeShift>  $shifts
     */
    private function leaveTimeOutsideShiftMessage(Collection $shifts): string
    {
        $ranges = $shifts
            ->map(function ($employeeShift) {
                $shift = $employeeShift->shift;

                return Carbon::parse($shift->start_time)->format('H:i')
                    .'–'
                    .Carbon::parse($shift->end_time)->format('H:i');
            })
            ->join(', ');

        return "Giờ về sớm phải nằm trong ca làm của ngày đó ({$ranges}).";
    }

    /**
     * @param  Collection<int, \App\Models\EmployeeShift>  $shifts
     */
    private function leaveTimeWithinShifts(string $leaveTime, Collection $shifts): bool
    {
        $leaveMinutes = $this->timeToMinutes($leaveTime);

        foreach ($shifts as $employeeShift) {
            $shift = $employeeShift->shift;
            $startMinutes = $this->timeToMinutes(Carbon::parse($shift->start_time)->format('H:i'));
            $endMinutes = $this->timeToMinutes(Carbon::parse($shift->end_time)->format('H:i'));

            if ($leaveMinutes >= $startMinutes && $leaveMinutes < $endMinutes) {
                return true;
            }
        }

        return false;
    }

    private function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return ($hours * 60) + $minutes;
    }
}
