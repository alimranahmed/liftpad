<?php

use App\Livewire\DomainManager;
use App\Livewire\ServerManager;
use Illuminate\Support\Facades\Route;

Route::view('/','welcome');

Route::get('/web-domain', ServerManager::class);
Route::get('/cloudflare', DomainManager::class);
