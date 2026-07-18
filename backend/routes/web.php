<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['product' => '火建AI企业知识问答免费版']));
