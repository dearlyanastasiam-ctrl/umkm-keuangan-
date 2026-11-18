<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role', 20)->default('owner')->after('email');      // admin | owner
            $t->string('business_name')->nullable()->after('role');
            $t->unsignedBigInteger('profit_target')->nullable()->after('business_name'); // target laba bulanan
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'business_name', 'profit_target']));
    }
};
