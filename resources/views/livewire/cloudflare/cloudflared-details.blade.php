<div class="rounded-xl border p-3 grid grid-cols-1 gap-3" x-data="{showTerminal: false}">
    <div class="flex justify-between "><div>
            <img src="{{asset('images/cloudflared_logo.svg')}}" alt="Cloudflare logo">
            <div class="mt-2">
                <span class="text-gray-600">Version: {{$cloudflaredVersion}}{{$isLatest ? '(latest)' : ''}}</span>
                @if(!$isLatest)
                    <x-ui.button wire:loading.remove wire:target="updateCloudflared" wire:click="updateCloudflared" @click="showTerminal = true">Update</x-ui.button>
                    <x-ui.button class="cursor-not-allowed" wire:loading wire:target="updateCloudflared">Updating...</x-ui.button>
                @endif
            </div>
        </div>
        <div class="font-semibold">{{$osVersion}}</div>
    </div>
    <div x-show="showTerminal" x-transition>
        <x-terminal.screen stream="{{$streamTo}}">{!! $output !!}</x-terminal.screen>
    </div>
</div>
