<?php

namespace App\Livewire\Cloudflare;

use App\Livewire\CanStreamProcess;
use App\Models\Server;
use App\Supports\Cloudflare\CloudFlareCli;
use App\Supports\Ssh\Ssh;
use Exception;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property CloudFlareCli $cloudflared
 */
class CloudflaredDetails extends Component
{
    use CanStreamProcess;

    public string $osVersion;

    public string $cloudflaredVersion;

    public bool $isLatest = true;

    public ?string $serverId;

    public string $output = '';

    public string $streamTo = 'cloudflared-details';

    public function mount(string $serverId): void
    {
        $this->serverId = $serverId;
        $this->osVersion = $this->getOsVersion();
        $this->cloudflaredVersion = $this->getCloudflaredVersion();
    }

    public function placeholder(): View
    {
        return view('livewire.cloudflare.cloudflared-details-placeholder');
    }

    private function getOsVersion(): string
    {
        return $this->cloudflared->ssh->execute("lsb_release -d | cut -f2")->getOutput();
    }

    private function getCloudflaredVersion(): string
    {
        $currentVersion = $this->cloudflared->getVersion();
        $latestVersion = $this->cloudflared->latestVersion();
        if (!empty($latestVersion)) {
            $this->isLatest = $currentVersion !== $latestVersion;
        }
        return $currentVersion;
    }

    /**
     * @throws \Exception
     */
    public function updateCloudflared(): void
    {
        if ($this->isLatest) {
            return;
        }
        $updateProcess = $this->cloudflared->updateCloudflared();

        $this->streamProcess($updateProcess, $this->streamTo);
    }

    #[Computed]
    public function cloudflared(): CloudFlareCli
    {
        $server = Server::query()->where('uuid', $this->serverId)->firstOrFail();
        return CloudFlareCli::create($server->toCredentials());
    }

    public function render(): View
    {
        return view('livewire.cloudflare.cloudflared-details');
    }
}
