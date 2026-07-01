<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_kpis', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_kpis', 'deadline')) {
                $table->date('deadline')->nullable()->after('comment');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_kpis', function (Blueprint $table) {
            if (Schema::hasColumn('employee_kpis', 'deadline')) {
                $table->dropColumn('deadline');
            }
        });
    }
};