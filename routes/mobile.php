<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::group([
    'prefix' => 'auth',
    'namespace' => 'App\Http\Controllers\Mobile\v1'
], function () {
    // Option 1: Redirect-based flow (web apps)
    Route::get('{provider}/redirect', 'Auth\SocialAuthController@redirectToProvider');
    Route::get('{provider}/callback', 'Auth\SocialAuthController@handleProviderCallback');

    // Option 2: Token exchange flow (mobile apps, SPAs)
    Route::post('{provider}/token', 'Auth\SocialAuthController@exchangeSocialToken');
});
Route::group([
    'prefix' => 'mobile/v1',
    'namespace' => 'App\Http\Controllers\Mobile\v1'
], function ($router) {

    Route::post('login', 'Auth\LoginController@login');
    Route::post('send-otp', 'Auth\SendOtpController@sendOtp');
    Route::post('check-otp', 'Auth\CheckOtpController@checkOtp');
    Route::post('forget-password', 'Auth\ForgetPasswordController@forgetPassword');
    Route::post('reset-password', 'Auth\ResetPasswordController@resetPassword');
    Route::post('register', 'Auth\RegisterController@register');
    Route::post('social-login', 'Auth\SocialLoginController@login');
    Route::get('questions', 'Home\QuestionController@index');

    Route::post('brand-names', 'Home\BrandNameController@generate');
    Route::group([
        'middleware' => ['auth:api']
    ], function ($router) {
         Route::get('plans', 'Home\PlanController@index');
         Route::get('subscription', 'Home\SubscriptionController@status');
         Route::post('subscribe', 'Home\SubscriptionController@subscribe');


        Route::post('brand-names/edit', 'Home\BrandNameController@edit');
        Route::get('brand-names/favorites', 'Home\BrandNameFavoriteController@index');
        Route::post('brand-names/favorites', 'Home\BrandNameFavoriteController@store');
        Route::delete('brand-names/favorites/{id}', 'Home\BrandNameFavoriteController@destroy');
        Route::post('brand-names/share', 'Home\BrandNameShareController@share');
        Route::get('invites', 'Home\InviteController@index');
        Route::post('invites', 'Home\InviteController@store');
        Route::post('brand-text', 'Home\BrandTextController@generate');
         Route::get('brand-text/history', 'Home\BrandTextController@history');
         Route::post('brand-text/edit', 'Home\BrandTextController@edit');
         Route::post('brand-text/domains', 'Home\BrandTextController@checkDomains');
         Route::post('brand-text/reserve-domain', 'Home\BrandTextController@reserveDomain');
         // User projects (BrandChat)
         Route::get('projects', 'Home\ProjectController@index');
         Route::get('meetings', 'Home\MeetingController@index');
         Route::post('meetings', 'Home\MeetingController@store');
         Route::get('profile', 'Home\ProfileController@show');
         Route::post('profile', 'Home\ProfileController@update');
         Route::patch('profile/password', 'Home\ProfileController@updatePassword');
    });

     // Stripe webhook (public)
     Route::post('webhooks/stripe', 'Home\StripeWebhookController@handle');

});

Route::group([
    'prefix' => 'admin',
    'namespace' => 'App\Http\Controllers\Admin'
], function ($router) {
    // auth
    Route::post('user/login', 'AuthController@login');
    Route::group(['middleware' => ['auth:admin']], function ($router) {
        // dashboard
        Route::get('dashboard', 'DashboardController@overview');
        Route::get('user/profile', 'AuthController@profile');
        // users export
        Route::get('users/export/download', 'UserController@exportDownload');
        // users
        Route::get('users', 'UserController@index');
        Route::get('users/export', 'UserController@export');
        Route::get('users/{id}', 'UserController@show');
        Route::put('users/{id}', 'UserController@update');
        Route::delete('users/{id}', 'UserController@destroy');
        Route::get('users/{id}/projects', 'UserController@projects');
        // projects (brand)
        Route::get('brands', 'ProjectController@index');
        Route::get('projects/{id}', 'ProjectController@show');
        Route::get('brands/{id}', 'ProjectController@show');
        // questions CRUD
        Route::get('questions', 'QuestionController@index');
        Route::post('questions', 'QuestionController@store');
        Route::get('questions/{id}', 'QuestionController@show');
        Route::put('questions/{id}', 'QuestionController@update');
        Route::delete('questions/{id}', 'QuestionController@destroy');
        // plans CRUD
        Route::get('plans', 'PlanController@index');
        Route::post('plans', 'PlanController@store');
        Route::get('plans/{id}', 'PlanController@show');
        Route::put('plans/{id}', 'PlanController@update');
        Route::delete('plans/{id}', 'PlanController@destroy');
        // meetings admin
        Route::get('meetings', 'MeetingController@index');
        Route::put('meetings/{id}', 'MeetingController@update');
    });
});


