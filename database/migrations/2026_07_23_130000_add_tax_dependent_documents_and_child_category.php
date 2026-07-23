<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_dependents', function (Blueprint $table) {
            $table->string('child_category', 20)->nullable()->after('relationship')
                ->comment('minor|student — chỉ khi quan hệ là con');
        });

        Schema::create('tax_dependent_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_dependent_id')->constrained('tax_dependents')->cascadeOnDelete();
            $table->string('document_type', 64);
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type', 128)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->timestamps();

            $table->unique(['tax_dependent_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_dependent_documents');
        Schema::table('tax_dependents', function (Blueprint $table) {
            $table->dropColumn('child_category');
        });
    }
};
