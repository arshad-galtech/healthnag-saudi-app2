<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\OrderWebhookController;

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

Route::get('/', function () {
    return "Hello API";
});

// Route for uploading ID images from Shopify theme
Route::post('/upload-id', [ImageUploadController::class, 'uploadId']);
// Order creation webhook for Saudi ID processing
Route::post('/webhooks/orders/create', [OrderWebhookController::class, 'handleOrderCreated']);