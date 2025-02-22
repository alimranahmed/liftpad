<?php

namespace App\Livewire;

use App\Supports\TerminalTheme\DraculaTheme;
use Illuminate\Process\InvokedProcess;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

trait CanStreamProcess
{
    private function streamProcess(InvokedProcess $process, string $streamTo): string
    {
        $ansiConverter = new AnsiToHtmlConverter(new DraculaTheme());
        $originalOutput = '';
        $this->output = '';

        while ($process->running()) {
            $latest = $process->latestOutput();
            if ($latest !== '') {
                $originalOutput .= $latest;
                $this->output .= '<br>'.nl2br($ansiConverter->convert($latest));
                $this->stream(
                    to: $streamTo,
                    content: $this->output,
                    replace: true,
                );
            }
            $latest = $process->latestErrorOutput();
            if ($latest !== '') {
                $originalOutput .= $latest;
                $this->output .= '<br>'.nl2br($ansiConverter->convert($latest));
                $this->stream(
                    to: $streamTo,
                    content: $this->output,
                    replace: true,
                );
            }
        }
        return $originalOutput;
    }
}
