<?php

namespace App\Services;

use App\Models\OvertimeRequest;
use App\Support\TimeInput;
use Carbon\Carbon;

class OvertimeRequestService
{
    public function normalizePayload(array $payload): array
    {
        if (isset($payload['start_time'])) {
            $payload['start_time'] = TimeInput::normalize($payload['start_time']);
        }

        if (isset($payload['end_time'])) {
            $payload['end_time'] = TimeInput::normalize($payload['end_time']);
        }

        if (! isset($payload['total_hours']) || $payload['total_hours'] === null || $payload['total_hours'] === '') {
            $start = Carbon::createFromFormat('H:i', $payload['start_time']);
            $end = Carbon::createFromFormat('H:i', $payload['end_time']);
            $payload['total_hours'] = round($end->diffInMinutes($start) / 60, 2);
        }

        return $payload;
    }

    public function create(array $data): OvertimeRequest
    {
        $data = $this->normalizePayload($data);
        $data['status'] = OvertimeRequest::STATUS_PENDING;

        return OvertimeRequest::create($data);
    }

    /**
     * @param  list<int>  $employeeIds
     * @return list<OvertimeRequest>
     */
    public function createMany(array $employeeIds, array $data): array
    {
        $payload = $this->normalizePayload($data);
        unset($payload['employee_id'], $payload['assignment_scope'], $payload['department_id']);
        $payload['status'] = OvertimeRequest::STATUS_PENDING;

        $created = [];

        foreach ($employeeIds as $employeeId) {
            $created[] = OvertimeRequest::create([
                ...$payload,
                'employee_id' => $employeeId,
            ]);
        }

        return $created;
    }

    public function update(OvertimeRequest $overtimeRequest, array $data): OvertimeRequest
    {
        $payload = $this->normalizePayload($data);
        $overtimeRequest->update($payload);

        return $overtimeRequest;
    }
}
