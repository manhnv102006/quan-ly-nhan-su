<?php

namespace App\Rules;

use App\Models\Contract;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoContractOverlap implements ValidationRule
{
    public function __construct(
        protected int $employeeId,
        protected string $startDate,
        protected ?string $endDate = null,
        protected ?int $ignoreContractId = null
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $start = Carbon::parse($this->startDate);
        $end = $this->endDate ? Carbon::parse($this->endDate) : Contract::farFutureDate();

        $overlaps = Contract::query()
            ->forEmployee($this->employeeId)
            ->occupyingPeriod()
            ->whereNull('deleted_at')
            ->overlapping($start, $end, $this->ignoreContractId)
            ->exists();

        if ($overlaps) {
            $fail('Khoảng thời gian hợp đồng bị chồng chéo với hợp đồng khác của nhân viên.');
        }
    }
}
