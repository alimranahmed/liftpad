<?php

use App\Livewire\CloudFlare;
use Illuminate\Support\Facades\Route;

Route::view('/','welcome');

Route::get('/cloudflare', CloudFlare::class);
