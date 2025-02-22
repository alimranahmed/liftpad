<?php

namespace App\Supports\Cloudflare;

use App\Supports\Ssh\Credentials;
use App\Supports\Ssh\Ssh;
use Illuminate\Process\InvokedProcess;
use Illuminate\Support\Facades\Log;

class CloudFlareCli
{
    public function __construct(
        public readonly Credentials $credentials,
        public readonly Ssh $ssh,
    )
    {
    }

    public static function create(Credentials $credentials): self
    {
        $ssh = Ssh::withCredentials($credentials)
            ->setTimeout(5)
            ->useSshPassPath('/opt/homebrew/bin/sshpass')
            ->sudo();

        return new static($credentials, $ssh);
    }

    public function logCommand(string $command, $output): void
    {
        Log::channel('command')->info($command."\n".$output);
    }

    public function  getTunnels(): array
    {
        $domains = $this->getDomains();

        $command = "cd /root/.cloudflared && cloudflared tunnel list";
        $process = $this->ssh->executeAsync($command);

        $output = '';
        while ($process->running()) {
            $output .= $process->latestOutput();
        }

        preg_match_all('/([a-f0-9\-]{36})\s+([\w.-]+)/', $output, $matches, PREG_SET_ORDER);

        $tunnels = [];
        foreach ($matches as $match) {
            $tunnelUuid = $match[1];
            $tunnelName = $match[2];

            $domain = $domains[$tunnelUuid] ?? null;

            $tunnels[] = (new Tunnel(
                $tunnelUuid,
                $tunnelName,
                $domain['domain'] ?? '',
                $domain['configFile'] ?? null
            ))->toArray();
        }
        return $tunnels;
    }

    protected function getDomains(): array {
        $command = "ls /root/.cloudflared/";
        $output = $this->ssh->execute($command)->getOutput();
        preg_match_all('/([\w.-]+\.yml)/', $output, $tunnelConfigs);

        if (!isset($tunnelConfigs[0])) {
            return [];
        }

        $domains = [];
        foreach ($tunnelConfigs[0] as $tunnelConfig) {
            $command = "cat /root/.cloudflared/{$tunnelConfig}";
            $configContent =  $this->ssh->execute($command)->getOutput();

            preg_match('/tunnel:\s*([\w-]+)/', $configContent, $tunnelMatch);
            preg_match('/-\s*hostname:\s*([\w\.-]+)/', $configContent, $hostnameMatch);

            $tunnelId = $tunnelMatch[1] ?? null;
            $domain = $hostnameMatch[1] ?? null;
            $domains[$tunnelId] = ['domain' => $domain, 'configFile' => $tunnelConfig];
        }
        return $domains;
    }

    public function getTunnelConfig(string $configFile): string
    {
        $command = "cat /root/.cloudflared/{$configFile}";
        return $this->ssh->execute($command)->getOutput();
    }

    public function startCreatingTunnel(string $domain): InvokedProcess
    {
        $command = "cd /root/.cloudflared && cloudflared tunnel create {$domain}";
        return $this->ssh->executeAsync($command);
    }

    public function deleteTunnel(string $tunnelUuid): string
    {
        $allOutput = '';

        // Cleanup tunnel
        $command = "cd /root/.cloudflared && cloudflared tunnel cleanup {$tunnelUuid}";
        $output = $this->ssh->execute($command)->getOutput();
        $this->logCommand($command, $output);
        $allOutput .= $output;

        // Delete tunnel
        $command = "cd /root/.cloudflared && cloudflared tunnel delete {$tunnelUuid}";
        $output = $this->ssh->execute($command)->getOutput();
        $this->logCommand($command, $output);
        $allOutput .= $output;

        return $allOutput;
    }

    public function createDomainConfigFile(string $domain, string $content): string
    {
        $content = escapeshellarg($content);
        $fileName = "/root/.cloudflared/$domain.yml";
        $command = "echo $content > $fileName";
        $output = $this->ssh->execute($command)->getOutput();

        $this->logCommand($command, $output);

        return $output;
    }

    public function deleteTunnelConfig(?string $domain): string
    {
        if ($domain === null || $domain === '') {
            return "Cannot find tunnel domain config for unspecified domain";
        }
        $fileName = "/root/.cloudflared/$domain.yml";
        $command = "rm $fileName";
        $output = $this->ssh->execute($command)->getOutput();
        $this->logCommand($command, $output);
        return $output;
    }

    public function createDnsRecordViaTunnel(string $tunnelUuid, string $domain): string
    {
        $command = "cloudflared tunnel route dns $tunnelUuid $domain";
        $output = $this->ssh->execute($command)->getOutput();
        $this->logCommand($command, $output);
        return $output;
    }

    public function deleteDnsRecord(?string $tunnelUuid): string
    {
        if ($tunnelUuid === null || $tunnelUuid === '') {
            return "Cannot find dns record for unspecified domain";
        }
        $command = "cloudflared tunnel route dns delete {$tunnelUuid}";
        $output = $this->ssh->execute($command)->getOutput();
        $this->logCommand($command, $output);
        return $output;

    }

    public function createAndStartTunnelRunnerService(string $domain, string $content): string
    {
        $content = escapeshellarg($content);
        $fileName = "/etc/systemd/system/cloudflared.{$domain}.service";
        $command = "echo $content > $fileName";
        $output = $this->ssh->execute($command)->getOutput();

        $this->logCommand($command, $output);

        $serviceOutput = $this->changeServiceStatus($domain, 'start');

        return "{$output}\n{$serviceOutput}";
    }

    public function deleteServiceFile(?string $domain): string
    {
        if ($domain === null || $domain === '') {
            return "Cannot find service file for unspecified domain";
        }
        $fileName = "/etc/systemd/system/cloudflared.{$domain}.service";
        $command = "rm $fileName";
        $output = $this->ssh->execute($command)->getOutput();
        $this->logCommand($command, $output);
        return $output;

    }

    /**
     * @throws \Exception
     */
    public function changeServiceStatus(string $domain, string $action): string
    {
        if (!in_array($action, ['start', 'stop', 'restart', 'status'])) {
            throw new \Exception('Invalid service action: '.$action);
        }

        $command = "systemctl {$action} cloudflared.{$domain}";
        $output = $this->ssh->execute($command)->getOutput();
        $this->logCommand($command, $output);
        return $output;
    }

    public function getDomain(string $tunnelUuid): ?string
    {
        $domains = $this->getDomains();
        return $domains[$tunnelUuid]['domain'] ?? null;
    }
}
