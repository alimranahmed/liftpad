<div class="rounded-xl border p-3 grid grid-cols-1 gap-3" x-data="{showTerminal: false}">
    <div class="flex justify-between"><div>
            <img src="{{asset('images/cloudflared_logo.svg')}}" alt="Cloudflare logo">
            <div class="mt-2 flex justify-start gap-x-2 items-center">
                @if($isCloudflaredMissing === true)
                    <span class="text-red-600">Cloudflared missing</span>
                    <x-icon name="cloud-arrow-down"
                            class="h-5 w-5 cursor-pointer hover:text-indigo-600"
                            wire:loading.class.remove="cursor-pointer"
                            wire:loading.class="animate-pulse cursor-not-allowed"
                            wire:loading.attr="disabled"
                            wire:target="installCloudflared"
                            wire:click="installCloudflared"
                            @click="showTerminal = true"/>
                @elseif($isCloudflaredMissing === false)
                    <span class="text-gray-600">Version: {{$cloudflaredVersion}}{{$msgForVersionCheck ? "($msgForVersionCheck)" : ''}}</span>
                @endif
                @if($isLatest === null && in_array($isCloudflaredMissing, [null, false]))
                    <x-icon name="arrow-path"
                            class="h-4 w-4 cursor-pointer hover:text-indigo-600"
                            wire:loading.class.remove="cursor-pointer"
                            wire:loading.class="animate-spin cursor-not-allowed"
                            wire:loading.attr="disabled"
                            wire:target="checkForUpdate"
                            wire:click="checkForUpdate"/>
                @elseif($isLatest === false)
                    <x-ui.button wire:loading.remove wire:target="updateCloudflared" wire:click="updateCloudflared" @click="showTerminal = true">Update</x-ui.button>
                    <x-ui.button class="cursor-not-allowed" wire:loading wire:target="updateCloudflared">Updating...</x-ui.button>
                @endif
            </div>
        </div>
        <div class="font-semibold">{{$osVersion}}</div>
    </div>
    <x-terminal.screen stream="{{$streamTo}}">{!! $output !!}</x-terminal.screen>
</div>
