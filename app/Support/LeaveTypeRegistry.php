<?php

namespace App\Support;

use App\Models\LeaveType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Điểm truy cập duy nhất tới danh mục loại nghỉ phép do Admin cấu hình.
 *
 * Kết quả được nhớ trong bộ nhớ của request và bị xóa mỗi khi một LeaveType
 * được lưu hoặc xóa (xem LeaveType::booted).
 */
class LeaveTypeRegistry
{
    /** @var Collection<string, LeaveType>|null */
    private static ?Collection $cache = null;

    public static function flush(): void
    {
        self::$cache = null;
    }

    /**
     * Toàn bộ loại nghỉ phép, kể cả loại đã tắt — đơn cũ vẫn phải tra cứu được.
     *
     * @return Collection<string, LeaveType>
     */
    public static function all(): Collection
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $types = self::loadFromDatabase() ?? self::fallback();

        return self::$cache = $types;
    }

    /**
     * @return Collection<string, LeaveType>
     */
    public static function active(): Collection
    {
        return self::all()->filter(fn (LeaveType $type) => $type->is_active);
    }

    public static function find(?string $code): ?LeaveType
    {
        if ($code === null) {
            return null;
        }

        return self::all()->get($code);
    }

    /** @return list<string> */
    public static function codes(): array
    {
        return self::active()->keys()->all();
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return self::all()->map(fn (LeaveType $type) => $type->name)->all();
    }

    /** @return array<string, string> */
    public static function activeLabels(): array
    {
        return self::active()->map(fn (LeaveType $type) => $type->name)->all();
    }

    /**
     * Loại nghỉ nhân viên được chọn, đã lọc theo giới tính.
     *
     * @return array<string, string>
     */
    public static function labelsForGender(?string $gender): array
    {
        return self::active()
            ->filter(fn (LeaveType $type) => $type->availableForGender($gender))
            ->filter(fn (LeaveType $type) => ! $type->auto_generated)
            ->map(fn (LeaveType $type) => $type->name)
            ->all();
    }

    /**
     * Loại nghỉ có người chi trả (công ty hoặc BHXH). Bao gồm cả loại đã tắt để
     * số liệu lương của đơn cũ không đổi khi Admin ngừng dùng một loại.
     *
     * @return list<string>
     */
    public static function paidCodes(): array
    {
        return self::all()
            ->filter(fn (LeaveType $type) => $type->isPaidLeave())
            ->keys()
            ->all();
    }

    /** @return list<string> */
    public static function annualDeductingCodes(): array
    {
        return self::all()
            ->filter(fn (LeaveType $type) => $type->deductsAnnualLeave())
            ->keys()
            ->all();
    }

    /**
     * Loại nghỉ chiếm quỹ 1 ngày hưởng lương/tháng do công ty chi trả.
     *
     * @return list<string>
     */
    public static function monthlyPaidQuotaCodes(): array
    {
        return self::all()
            ->filter(fn (LeaveType $type) => $type->countsTowardMonthlyPaidQuota())
            ->keys()
            ->all();
    }

    /** @return list<string> */
    public static function documentRequiredCodes(): array
    {
        return self::active()
            ->filter(fn (LeaveType $type) => $type->requiresDocument())
            ->keys()
            ->all();
    }

    /** @return array<string, string> */
    public static function documentHints(): array
    {
        return self::active()
            ->filter(fn (LeaveType $type) => $type->requiresDocument())
            ->map(fn (LeaveType $type) => $type->document_hint ?: 'Vui lòng đính kèm giấy tờ minh chứng.')
            ->all();
    }

    /** @return array<string, array{label: string, class: string}> */
    public static function badgeMap(): array
    {
        return self::all()
            ->map(fn (LeaveType $type) => [
                'label' => $type->name,
                'class' => $type->badgeClass(),
            ])
            ->all();
    }

    /**
     * @return Collection<string, LeaveType>|null null khi bảng chưa sẵn sàng.
     */
    private static function loadFromDatabase(): ?Collection
    {
        try {
            if (! Schema::hasTable('leave_types')) {
                return null;
            }

            $types = LeaveType::query()->ordered()->get()->keyBy('code');
        } catch (Throwable) {
            return null;
        }

        return $types->isEmpty() ? null : $types;
    }

    /**
     * Dự phòng khi chưa chạy migration (ví dụ lúc cài đặt lần đầu).
     *
     * @return Collection<string, LeaveType>
     */
    private static function fallback(): Collection
    {
        return collect(LeaveTypeDefaults::rows())
            ->map(fn (array $row) => (new LeaveType)->forceFill($row))
            ->keyBy('code');
    }
}
