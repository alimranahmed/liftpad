<?php

namespace App\Livewire\Cloudflare;

use App\Livewire\CanStreamProcess;
use App\Models\Server;
use App\Supports\Cloudflare\CloudFlareCli;
use Illuminate\Contracts\View\View;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Component;

class AddDomain extends Component
{
    use CanStreamProcess;

    public array $steps = [
        'create_tunnel' => 'Create a tunnel similar as domain name',
        'configure_tunnel' => 'Configure tunnel to serve application on defined port',
        'route_traffic' => 'Route request on Cloudflare via crated tunnel',
        'add_service_entry' => 'Add the Cloudflared service entry for the domain',
        'start_domain_service' => 'Start the domain service',
    ];

    #[Validate('required|string|max:200')]
    public string $domain = '';

    #[Validate('required|numeric|min:0')]
    public int $port;

    public string $streamName = 'add-domain';

    public string $output = '';

    public Server $server;

    public function mount(string $serverId): void
    {
        $this->server = Server::query()->where('uuid', $serverId)->firstOrFail();
    }

    public function addDomain(): void
    {
        $this->validate();


        $tunnelUuid = $this->createTunnel($this->domain);

        $this->createDomainConfigFile($tunnelUuid, $this->domain, $this->port);

        $this->createDnsRecordViaTunnel($tunnelUuid, $this->domain);

        $this->createAndStartTunnelRunnerService($this->domain);
    }

    private function createTunnel(string $domain): string
    {
        $process = $this->cloudflared()->startCreatingTunnel($domain);
        $originalOutput = $this->streamProcess($process, $this->streamName);
        Log::channel('command')->info($originalOutput);

        preg_match('/([\w-]{36})/', $originalOutput, $matches);

        $tunnelUuid = $matches[0] ?? null;

        Log::channel('command')->info("Tunnel: $tunnelUuid created");

        return $tunnelUuid;
    }

    private function createDomainConfigFile(string $tunnelUuid, string $domain, int $port): void
    {
        $stubConfig = new File(resource_path('/stubs/cloudflare/tunnel_config.yml'));
        $content = $stubConfig->getContent();
        $replacements = [
            "{TUNNEL_UUID}" => $tunnelUuid,
            "{HOST_NAME}" => $domain,
            "{SERVICE_PORT}" => $port,
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        $output = $this->cloudflared()->createDomainConfigFile($domain, $content);

        $this->output .= $output;
    }

    private function createDnsRecordViaTunnel(string $tunnelUuid, string $domain): void
    {
        $this->cloudflared()->createDnsRecordViaTunnel($tunnelUuid, $domain);
    }

    public function createAndStartTunnelRunnerService(string $domain): void
    {
        $stubService = new File(resource_path('/stubs/cloudflare/domain.service'));
        $content = $stubService->getContent();
        $replacements = ["{TUNNEL_CONFIG}" => $domain];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        $output = $this->cloudflared()->createAndStartTunnelRunnerService($domain, $content);
        $this->output .= $output;
    }

    public function cloudflared(): CloudFlareCli {
        return CloudFlareCli::create($this->server->toCredentials());
    }

    public function render(): View
    {
        return view('livewire.cloudflare.add-domain');
    }
}
