<?php

namespace App\Support;

/**
 * Yêu cầu giấy tờ minh chứng được lấy từ danh mục loại nghỉ phép do Admin cấu hình.
 */
class LeaveDocumentRules
{
    /** @return list<string> */
    public static function typesRequiringDocument(): array
    {
        return LeaveTypeRegistry::documentRequiredCodes();
    }

    public static function requiresDocument(string $leaveType): bool
    {
        return LeaveTypeRegistry::find($leaveType)?->requiresDocument() ?? false;
    }

    /** @return array<string, string> */
    public static function documentHints(): array
    {
        return LeaveTypeRegistry::documentHints();
    }

    public static function hintFor(string $leaveType): ?string
    {
        $type = LeaveTypeRegistry::find($leaveType);

        if ($type === null || ! $type->requiresDocument()) {
            return null;
        }

        return $type->document_hint ?: 'Vui lòng đính kèm giấy tờ minh chứng.';
    }
}
