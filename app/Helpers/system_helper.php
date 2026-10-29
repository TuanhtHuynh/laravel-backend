<?php

use Illuminate\Database\Schema\Blueprint;

if (! function_exists('addseoFields')) {
    function addseoFields(Blueprint $table)
    {
        $table->string('slug')->unique()->index();
        $table->string('meta_title')->nullable();
        $table->string('meta_description')->nullable();
        $table->string('meta_keyword')->nullable();
    }
}
