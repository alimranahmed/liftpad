<?php

namespace App\Livewire\Cloudflare;

use App\Livewire\CanStreamProcess;
use App\Models\Server;
use App\Supports\Cloudflare\CloudFlareCli;
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

    public string $msgForVersionCheck = '';

    public ?bool $isLatest = null;


    public ?string $serverId;

    public string $output = '';

    public string $streamTo = 'cloudflared-details';

    public function mount(string $serverId): void
    {
        $this->serverId = $serverId;
        $this->osVersion = $this->getOsVersion();
        $this->cloudflaredVersion = $this->cloudflared->getVersion();
    }

    public function placeholder(): View
    {
        return view('livewire.cloudflare.cloudflared-details-placeholder');
    }

    private function getOsVersion(): string
    {
        return $this->cloudflared->ssh->execute("lsb_release -d | cut -f2")->getOutput();
    }

    public function checkForUpdate(): void
    {
        $latestVersion = $this->cloudflared->latestVersion();
        if ($latestVersion === null) {
            $this->msgForVersionCheck = 'check failed';
        } else {
            $this->isLatest = $this->cloudflaredVersion !== $latestVersion;
            $this->msgForVersionCheck = $this->isLatest ? 'latest' : "latest: $latestVersion";
        }
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
