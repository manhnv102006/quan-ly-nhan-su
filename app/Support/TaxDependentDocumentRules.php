<?php

namespace App\Support;

class TaxDependentDocumentRules
{
    public const TYPE_BIRTH_CERTIFICATE = 'birth_certificate';

    public const TYPE_STUDENT_CARD = 'student_card';

    public const TYPE_MARRIAGE_CERTIFICATE = 'marriage_certificate';

    public const TYPE_ID_CARD = 'id_card';

    public const TYPE_NO_INCOME = 'no_income_confirmation';

    public const TYPE_OTHER = 'other';

    public const TYPE_LABELS = [
        self::TYPE_BIRTH_CERTIFICATE => 'Giấy khai sinh',
        self::TYPE_STUDENT_CARD => 'Thẻ sinh viên',
        self::TYPE_MARRIAGE_CERTIFICATE => 'Giấy đăng ký kết hôn',
        self::TYPE_ID_CARD => 'CCCD/CMND (bản scan)',
        self::TYPE_NO_INCOME => 'Giấy xác nhận không có thu nhập',
        self::TYPE_OTHER => 'Giấy tờ liên quan',
    ];

    /**
     * @return list<array{relationship: string, child_category: ?string, documents: list<string>}>
     */
    public static function requirementGuide(): array
    {
        return [
            [
                'relationship' => 'child',
                'child_category' => self::childMinor(),
                'documents' => [self::TYPE_BIRTH_CERTIFICATE],
                'summary' => 'Con dưới 18 tuổi: Giấy khai sinh',
            ],
            [
                'relationship' => 'child',
                'child_category' => self::childStudent(),
                'documents' => [self::TYPE_BIRTH_CERTIFICATE, self::TYPE_STUDENT_CARD],
                'summary' => 'Con trên 18 tuổi (học ĐH): Giấy khai sinh + Thẻ sinh viên',
            ],
            [
                'relationship' => 'spouse',
                'child_category' => null,
                'documents' => [self::TYPE_MARRIAGE_CERTIFICATE, self::TYPE_ID_CARD],
                'summary' => 'Vợ/Chồng: Đăng ký kết hôn + CCCD',
            ],
            [
                'relationship' => 'parent',
                'child_category' => null,
                'documents' => [self::TYPE_ID_CARD, self::TYPE_NO_INCOME],
                'summary' => 'Bố/Mẹ: CCCD + Giấy xác nhận không có thu nhập',
            ],
        ];
    }

    public static function childMinor(): string
    {
        return 'minor';
    }

    public static function childStudent(): string
    {
        return 'student';
    }

    /**
     * @return list<string>
     */
    public static function requiredTypes(string $relationship, ?string $childCategory): array
    {
        return match ($relationship) {
            'child' => match ($childCategory) {
                self::childStudent() => [self::TYPE_BIRTH_CERTIFICATE, self::TYPE_STUDENT_CARD],
                default => [self::TYPE_BIRTH_CERTIFICATE],
            },
            'spouse' => [self::TYPE_MARRIAGE_CERTIFICATE, self::TYPE_ID_CARD],
            'parent' => [self::TYPE_ID_CARD, self::TYPE_NO_INCOME],
            default => [self::TYPE_OTHER],
        };
    }

    public static function label(string $type): string
    {
        return self::TYPE_LABELS[$type] ?? $type;
    }
}
