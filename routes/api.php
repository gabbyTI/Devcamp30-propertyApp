<?php

use App\Http\Controllers\PropertyController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('register',[AuthController::class, 'register']);
Route::post('login',[AuthController::class, 'login']);

Route::get('properties',[PropertyController::class, 'getAllProperties']);

Route::get('properties/search',[PropertyController::class, 'search']);

// For Logged in users
Route::group(['middleware' =>'auth:sanctum'],function(){
    Route::post('logout',[AuthController::class, 'logout']);

    Route::post('users/password/update',[AuthController::class, 'updateMyPassword']);

    Route::get('properties/{propertyId}',[PropertyController::class, 'getProperty']);
    Route::put('properties/{propertyId}',[PropertyController::class, 'updateProperty']);
    Route::post('properties',[PropertyController::class, 'createProperty']);
    Route::delete('properties/{propertyId}',[PropertyController::class, 'deleteProperty']);
});


