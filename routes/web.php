<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/debug-dd', function () {
//     $media = DB::table('media')->get();
//     $mediaHasModels = DB::table('media_has_models')->get();
//     $folders = DB::table('folders')->get();
//     $folderHasModels = DB::table('folder_has_models')->get();

//     dd([
//         'media' => $media,
//         'media_has_models' => $mediaHasModels,
//         'folders' => $folders,
//         'folder_has_models' => $folderHasModels,
//     ]);
// });

use Illuminate\Support\Facades\Auth;

Route::get('/debug-permissions', function () {
    dd(Auth::user()->getAllPermissions()->pluck('name'));
});