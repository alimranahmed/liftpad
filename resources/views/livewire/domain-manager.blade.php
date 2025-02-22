<div>

    <livewire:cloudflare.tunnels lazy serverId="{{$server->uuid}}"/>

    <livewire:cloudflare.add-domain serverId="{{$server->uuid}}"/>

    <form class="mt-5 flex border dark:border-gray-800 rounded-t-xl p-1 justify-between bg-white" wire:submit="execute">
        <input type="text"
               autofocus="autofocus"
               class="py-2 px-1 w-full text-center focus:outline-none border-xl text-gray-800"
               name="command"
               wire:model="command"
               placeholder="Command..."
               aria-label="Command">
        <div class="flex items-center px-2">
            <button type="submit" class="text-green-600" wire:loading.remove>Run</button>
            <span class="text-indigo-600 cursor-not-allowed" disabled wire:loading>Running...</span>
        </div>
    </form>
    <x-terminal.screen stream="display">{!! $output !!}</x-terminal.screen>
</div>
