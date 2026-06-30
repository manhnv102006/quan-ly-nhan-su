<?php

namespace App\Services;

use App\Models\OvertimeRequest;
use Carbon\Carbon;

class OvertimeRequestService
{
    public function normalizePayload(array $payload): array
    {
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

    public function update(OvertimeRequest $overtimeRequest, array $data): OvertimeRequest
    {
        $overtimeRequest->update($this->normalizePayload($data));

        return $overtimeRequest;
    }
}
