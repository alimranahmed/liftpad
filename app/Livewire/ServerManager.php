<?php

namespace App\Livewire;

use App\Models\Server;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

class ServerManager extends Component
{
    #[Url]
    public ?string $serverId = null;

    public Collection $servers;

    public function mount(): void
    {
        $this->servers = Server::query()->get();
    }

    public function render(): View
    {
        return view('livewire.server-manager');
    }
}
