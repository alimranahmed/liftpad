<?php

namespace App\Livewire;

use App\Supports\Enums\LogChannel;
use App\Supports\TerminalTheme\DraculaTheme;
use Illuminate\Process\InvokedProcess;
use Illuminate\Support\Facades\Log;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

trait CanStreamProcess
{
    protected function ansiToHtmlConverter(): AnsiToHtmlConverter
    {
        return new AnsiToHtmlConverter(new DraculaTheme());
    }

    /**
     * @throws \Exception
     */
    protected function streamProcess(InvokedProcess $process, string $streamTo): string
    {
        $originalOutput = '';

        while ($process->running()) {
            $latest = $process->latestOutput();
            if ($latest !== '') {
                $originalOutput .= $latest;
                $this->logAndStreamMessage($latest, $streamTo);
            }
            $latest = $process->latestErrorOutput();
            if ($latest !== '') {
                $originalOutput .= $latest;
                $this->logAndStreamMessage($latest, $streamTo);
            }
        }
        return $originalOutput;
    }

    /**
     * @throws \Exception
     */
    protected function logAndStreamMessage(string $message, string $streamTo = null): void
    {
        $this->log($message);

        if ($streamTo == null) {
            if (property_exists($this, 'streamTo')) {
                $streamTo = $this->streamTo;
            } else {
                throw new \Exception('Stream name not defined');
            }
        }

        $this->output .= '<br>'.nl2br($this->ansiToHtmlConverter()->convert($message));

        $this->stream(
            to: $streamTo,
            content: $this->output,
            replace: true,
        );
    }

    private function log($message): void
    {
        Log::channel(LogChannel::COMMAND->value)->info($message);
    }
}
