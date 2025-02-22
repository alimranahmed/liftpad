<div>
    <div class="border rounded-md">
        <div class="flex justify-between border-b w-full px-3 py-2">
            <h3 class="font-semibold">Tunnels</h3>
            <div>
                <button wire:click="getTunnels" wire:loading.remove wire:target="getTunnels" class="hover:underline">Load</button>
            </div>
        </div>
        <div class="px-3 py-2">
            <div wire:loading wire:target="getTunnels" class="text-center w-full">Loading...</div>
            <div wire:loading.remove wire:target="getTunnels">
                @if(count($tunnels) <= 0)
                    <div class="text-center text-gray-400 text-xl">No tunnels loaded</div>
                @else
                    <x-ui.table>
                        <thead>
                            <tr>
                                <x-ui.table.th>Name</x-ui.table.th>
                                <x-ui.table.th>Tunnel UUID</x-ui.table.th>
                                <x-ui.table.th>Domain</x-ui.table.th>
                                <x-ui.table.th></x-ui.table.th>
                            </tr>
                        </thead>
                        <x-ui.table.tbody>
                            @foreach($tunnels as $tunnel)
                                <tr>
                                    <x-ui.table.td>{{$tunnel['name']}}</x-ui.table.td>
                                    <x-ui.table.td>{{$tunnel['uuid']}}</x-ui.table.td>
                                    <x-ui.table.td>{{$tunnel['domain']}}</x-ui.table.td>
                                    <x-ui.table.td>
                                        @if($tunnel['configFile'])
                                            <span class="cursor-pointer hover:underline" wire:click="showTunnelConfig('{{$tunnel['configFile']}}')">Show</span>
                                        @endif
                                        <span class="cursor-pointer hover:underline" wire:click="deleteTunnel('{{$tunnel['uuid']}}')">Delete</span>
                                    </x-ui.table.td>
                                </tr>
                            @endforeach
                        </x-ui.table.tbody>
                    </x-ui.table>
                @endif
            </div>
        </div>
    </div>
    @if($output)
        <div class="mt-3">
            <div class="border dark:border-gray-800 rounded-t-xl p-1 flex justify-between bg-white">
                <div class="font-semibold">Tunnel Config</div>
                <div><span wire:click="clearOutput" class="hover:underline cursor-pointer">Close</span></div>
            </div>
            <x-terminal.screen>
                <span wire:loading.remove wire:target="showTunnelConfig">{!! $output !!}</span>
                <span wire:loading wire:target="showTunnelConfig">
                    Loading...
                </span>
            </x-terminal.screen>
        </div>
    @endif
</div>
