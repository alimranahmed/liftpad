<?php

use App\Livewire\DomainManager;
use App\Livewire\ServerManager;
use App\Livewire\Website\WebsiteManager;
use Illuminate\Support\Facades\Route;

Route::view('/','welcome');

Route::get('/servers', ServerManager::class)->name('server.index');
Route::get('/websites', WebsiteManager::class)->name('website.index');
