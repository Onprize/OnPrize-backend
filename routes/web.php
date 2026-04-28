<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test-mail', function () {
    Mail::to('hoshangabadwalam@gmail.com')->send(new \App\Mail\SendOtpMail('123456'));
    return 'Mail sent';
});