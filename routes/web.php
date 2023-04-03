<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('lichess', function () {
    $fen = 'rnbqkb1r/pppp1ppp/5n2/4p3/2B1P3/5N2/PPPP1PPP/RNBQK2R b KQkq - 3 3';
    $fen = urlencode($fen);
    $response = \Illuminate\Support\Facades\Http::get('https://explorer.lichess.ovh/lichess?variant=standard&speeds=blitz,rapid,classical&ratings=1000,2500&fen=' . $fen);
    dd($response->json());
});

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/training', function () {
        return view('training');
    })->name('training');
});
