<?php

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


Route::group(['middleware' => 'api',], function ($router) {

    Route::prefix('auth')->group(function () {
        Route::post('register', 'RegisterController@register');
        Route::post('login', 'AuthController@login');
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
        Route::post('me', 'AuthController@me');
    });

    Route::get('forum/tag/{tag}', 'ForumController@filterTag');
    Route::apiResource('forum', 'ForumController');
    Route::apiResource('forum.comment', 'CommentController');
});
