<?php

use App\Models\PayrollPeriod;
use App\Models\PayrollPeriodBankDocument;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');

    $this->accountantRole = Role::create([
        'name' => Role::ACCOUNTANT,
        'description' => 'Kế toán',
    ]);

    $this->accountant = User::factory()->create([
        'role_id' => $this->accountantRole->id,
        'status' => 'active',
    ]);

    $this->period = PayrollPeriod::create([
        'name' => 'Kỳ lương tháng 07/2026',
        'month' => 7,
        'year' => 2026,
        'start_date' => '2026-07-01',
        'end_date' => '2026-07-31',
        'status' => 'calculated',
        'is_active' => true,
    ]);
});

test('accountant can upload a stamped bank file for a payroll period', function () {
    $file = UploadedFile::fake()->create('bang-luong-nh-07-2026.pdf', 240, 'application/pdf');

    $this->actingAs($this->accountant)
        ->post(route('accountant.payroll-periods.bank-documents.store', $this->period), [
            'file' => $file,
            'note' => 'Bản đóng dấu chuyển khoản',
        ])
        ->assertRedirect(route('accountant.payroll-periods.show', $this->period));

    $document = PayrollPeriodBankDocument::query()->first();

    expect($document)->not->toBeNull()
        ->and($document->original_name)->toBe('bang-luong-nh-07-2026.pdf')
        ->and($document->note)->toBe('Bản đóng dấu chuyển khoản')
        ->and($document->uploaded_by)->toBe($this->accountant->id);

    Storage::disk('local')->assertExists($document->file_path);
});

test('accountant can download and delete a stored bank file', function () {
    $file = UploadedFile::fake()->create('chung-tu.pdf', 120, 'application/pdf');

    $this->actingAs($this->accountant)
        ->post(route('accountant.payroll-periods.bank-documents.store', $this->period), [
            'file' => $file,
        ]);

    $document = PayrollPeriodBankDocument::query()->first();

    $this->actingAs($this->accountant)
        ->get(route('accountant.payroll-periods.bank-documents.download', [$this->period, $document]))
        ->assertOk();

    $this->actingAs($this->accountant)
        ->delete(route('accountant.payroll-periods.bank-documents.destroy', [$this->period, $document]))
        ->assertRedirect(route('accountant.payroll-periods.show', $this->period));

    expect(PayrollPeriodBankDocument::query()->count())->toBe(0);
    Storage::disk('local')->assertMissing($document->file_path);
});
