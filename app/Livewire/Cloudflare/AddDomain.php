<?php

namespace App\Livewire\Cloudflare;

use App\Livewire\CanStreamProcess;
use App\Models\Server;
use App\Supports\Cloudflare\CloudFlareCli;
use App\Supports\Enums\LogChannel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Component;

class AddDomain extends Component
{
    use CanStreamProcess;

    #[Validate('required|string|max:200')]
    public string $domain = '';

    #[Validate('required|numeric|min:0')]
    public int $port;

    public string $streamTo = 'add-domain';

    public string $output = '';

    public Server $server;

    public function mount(string $serverId): void
    {
        $this->server = Server::query()->where('uuid', $serverId)->firstOrFail();
    }

    /**
     * @throws \Exception
     */
    public function addDomain(): void
    {
        $this->validate();

        $tunnelUuid = $this->createTunnel($this->domain);

        $this->createDomainConfigFile($tunnelUuid, $this->domain, $this->port);

        $this->createDnsRecordViaTunnel($tunnelUuid, $this->domain);

        $this->createAndStartTunnelRunnerService($this->domain);

        $this->logAndStreamMessage('Domain setup via tunnel completed!');
        $this->dispatch('tunnelUpdated')->to(Tunnels::class);
    }

    /**
     * @throws \Exception
     */
    private function createTunnel(string $domain): string
    {
        $this->logAndStreamMessage("Creating tunnel for domain: {$domain}");

        $process = $this->cloudflared()->startCreatingTunnel($domain);
        $originalOutput = $this->streamProcess($process, $this->streamTo);

        Log::channel(LogChannel::COMMAND->value)->info($originalOutput);

        preg_match('/([\w-]{36})/', $originalOutput, $matches);

        $tunnelUuid = $matches[0] ?? null;

        $this->logAndStreamMessage("Created tunnel: $tunnelUuid");

        return $tunnelUuid;
    }

    /**
     * @throws \Exception
     */
    private function createDomainConfigFile(string $tunnelUuid, string $domain, int $port): void
    {
        $this->logAndStreamMessage("Creating domain config using tunnel($tunnelUuid): {$domain}:{$port}");

        $stubConfig = new File(resource_path('/stubs/cloudflare/tunnel_config.yml'));
        $content = $stubConfig->getContent();
        $replacements = [
            "{TUNNEL_UUID}" => $tunnelUuid,
            "{HOST_NAME}" => $domain,
            "{SERVICE_PORT}" => $port,
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        $output = $this->cloudflared()->createDomainConfigFile($domain, $content);

        $this->logAndStreamMessage($output);
        $this->logAndStreamMessage("Created domain config file for: {$domain}:{$port}");
    }

    /**
     * @throws \Exception
     */
    private function createDnsRecordViaTunnel(string $tunnelUuid, string $domain): void
    {
        $this->logAndStreamMessage("Creating DNS record in cloudflare via tunnel: {$tunnelUuid}, domain: {$domain}");

        $output = $this->cloudflared()->createDnsRecordViaTunnel($tunnelUuid, $domain);

        $this->logAndStreamMessage($output);
        $this->logAndStreamMessage("Created DNS record in cloudflare via tunnel: {$tunnelUuid}, domain: {$domain}");
    }

    /**
     * @throws \Exception
     */
    public function createAndStartTunnelRunnerService(string $domain): void
    {

        $stubService = new File(resource_path('/stubs/cloudflare/domain.service'));
        $content = $stubService->getContent();
        $replacements = ["{TUNNEL_CONFIG}" => $domain];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        $this->logAndStreamMessage("Creating service entry to run tunnel for domain: {$domain}");

        $output = $this->cloudflared()->createAndStartTunnelRunnerService($domain, $content);

        $this->logAndStreamMessage($output);
        $this->logAndStreamMessage("Created service entry to run tunnel for domain: {$domain}");
    }

    public function cloudflared(): CloudFlareCli {
        return CloudFlareCli::create($this->server->toCredentials());
    }

    public function render(): View
    {
        return view('livewire.cloudflare.add-domain');
    }
}
