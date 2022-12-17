<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MyInfoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/log-in', [UserController::class, 'Login']);
Route::post('/sign-up', [UserController::class, 'SignUp']);
Route::get('/homepage', [UserController::class, 'GetHomePage']);
Route::get('/view-profile', [UserController::class, 'ViewProfile']);
Route::post('/follow', [UserController::class, 'Follow']);
Route::post('/edit-profile', [UserController::class, 'EditProfile']);
Route::post('/create', [PostController::class, 'CreatePost']);
Route::get('/create', [PostController::class, 'GetCreatePost']);
Route::post('/comment', [PostController::class, 'PostComment']);
Route::post('/detail', [PostController::class, 'PostDetail']);
Route::post('/save-post', [PostController::class, 'SavePost']);
Route::post('/post-search', [PostController::class, 'PostSearch']);