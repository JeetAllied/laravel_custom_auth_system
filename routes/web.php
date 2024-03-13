<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*Route::get('/', function () {
    return view('welcome');
});*/

Route::post('/subscribe',[HomeController::class,'subscribe'])->name('subscribe');
Route::group(['middleware'=>['revalidate_back_history']], function(){
    Route::get('/', [HomeController::class,'home'])->name('home');
    Route::group(['prefix'=> 'auth', 'middleware'=> ['custom_guest']], function(){
        Route::get('/registration',[AuthController::class,'getRegister'])->name('getRegister');
        Route::post('/registration',[AuthController::class, 'postRegister'])->name('postRegister');
        Route::post('/check_email_unique',[AuthController::class,'checkEmailUnique'])->name('checkEmailUnique');
        Route::get('/verify_email/{verificationCode}',[AuthController::class,'verifyEmail'])->name('verifyEmail');
        Route::get('/login',[AuthController::class,'getLogin'])->name('getLogin');
        Route::post('/login',[AuthController::class, 'postLogin'])->name('postLogin');
        //login and register using ajax
        Route::post('/ajax_login',[AuthController::class,'ajaxLogin'])->name('ajaxLogin');
        Route::post('/ajax_register',[AuthController::class,'ajaxRegister'])->name('ajaxRegister');

        Route::get('/forgetPassword',[AuthController::class,'getForgetPassword'])->name('getForgetPassword');
        Route::post('/forgetPassword',[AuthController::class,'postForgetPassword'])->name('postForgetPassword');

        Route::get('/resetPassword/{resetCode}',[AuthController::class,'getResetPassword'])->name('getResetPassword');
        Route::post('/resetPassword/{resetCode}',[AuthController::class,'postResetPassword'])->name('postResetPassword');
    });

    Route::get('auth/logout',[AuthController::class,'logout'])->name('logout')->middleware('custom_auth');

    Route::group(['prefix'=> 'profile', 'middleware'=> ['custom_auth']], function(){
        Route::get('/dashboard',[ProfileController::class,'dashboard'])->name('dashboard');
        Route::get('/editProfile',[ProfileController::class,'editProfile'])->name('editProfile');
        Route::put('/updateProfile',[ProfileController::class,'updateProfile'])->name('updateProfile');

        Route::get('/changePassword',[ProfileController::class, 'changePassword'])->name('changePassword');
        Route::post('/updatePassword',[ProfileController::class, 'updatePassword'])->name('updatePassword');
    });
});



