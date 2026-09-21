<?php

namespace Tests;

use App\Support\LeaveTypeRegistry;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Danh mục loại nghỉ phép được nhớ trong bộ nhớ tĩnh nên phải xóa giữa các test.
        LeaveTypeRegistry::flush();
    }
}
