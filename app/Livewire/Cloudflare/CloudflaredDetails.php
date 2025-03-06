<?php

namespace App\Livewire\Cloudflare;

use App\Livewire\CanStreamProcess;
use App\Models\Server;
use App\Supports\Cloudflare\CloudFlareCli;
use App\Supports\Cloudflare\Exceptions\CloudflaredNotInstalled;
use App\Supports\Cloudflare\Exceptions\CommandFailed;
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

    public ?bool $isCloudflaredMissing = null;


    public ?string $serverId;

    public string $output = '';

    public string $streamTo = 'cloudflared-details';

    /**
     * @throws \Exception
     */
    public function mount(string $serverId): void
    {
        $this->serverId = $serverId;
        $this->loadDetails();

    }

    /**
     * @throws \Exception
     */
    private function loadDetails(): void
    {
        $this->osVersion = $this->getOsVersion();
        try {
            $this->cloudflaredVersion = $this->cloudflared->getVersion();
            $this->isCloudflaredMissing = false;
        } catch (CloudflaredNotInstalled $e) {
            $this->isCloudflaredMissing = true;
            $this->logAndStreamMessage($e->getMessage());
        }
    }

    private function getOsVersion(): string
    {
        return $this->cloudflared->ssh->execute("lsb_release -d | cut -f2")->getOutput();
    }

    /**
     * @throws \Exception
     */
    public function installCloudflared(): void
    {
        try {
            $process = $this->cloudflared->installCloudflared();
            $this->streamProcess($process, $this->streamTo);
            $output = $this->cloudflared->loginCloudflared();
            dd($output);
            $this->logAndStreamMessage($output);
            $this->loadDetails();
        } catch (CommandFailed $e) {
            $this->logAndStreamMessage($e->getMessage());
        }
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

    public function placeholder(): View
    {
        return view('livewire.cloudflare.cloudflared-details-placeholder');
    }

    public function render(): View
    {
        return view('livewire.cloudflare.cloudflared-details');
    }
}
