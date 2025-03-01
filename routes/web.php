<?php

use App\Livewire\Server\Create;
use App\Livewire\Server\Index;
use App\Livewire\Website\WebsiteManager;
use Illuminate\Support\Facades\Route;

Route::view('/','welcome');

Route::get('/servers', Index::class)->name('server.index');
Route::get('/servers/create', Create::class)->name('server.create');
Route::get('/websites', WebsiteManager::class)->name('website.index');
