<div>
    <div class="border rounded-xl" x-data="{ showTerminal: false }">
        <div class="flex justify-between border-b w-full px-3 py-2">
            <h3 class="font-semibold">Tunnels</h3>
            <div>
                <button wire:click="getTunnels" wire:loading.remove wire:target="getTunnels" class="hover:text-indigo-500">
                    Reload
                </button>
                <span wire:loading wire:target="getTunnels">
                    Loading...
                </span>
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
                                            <span class="cursor-pointer hover:text-indigo-500"
                                                  wire:click="showTunnelConfig('{{$tunnel['configFile']}}')"
                                                  @click="showTerminal = true">Show</span>
                                        @endif
                                        <span class="cursor-pointer hover:text-indigo-500"
                                              wire:loading.remove
                                              wire:target="deleteTunnel('{{$tunnel['uuid']}}')"
                                              wire:click="deleteTunnel('{{$tunnel['uuid']}}')"
                                              @click="showTerminal = true"
                                        >Delete</span>
                                        <span class="" wire:loading wire:target="deleteTunnel('{{$tunnel['uuid']}}')">Deleting...</span>
                                    </x-ui.table.td>
                                </tr>
                            @endforeach
                        </x-ui.table.tbody>
                    </x-ui.table>
                @endif
            </div>
        </div>
        <div x-show="showTerminal" x-transition>
            <x-terminal.screen stream="{{$streamTo}}">
                <span wire:loading.remove wire:target="showTunnelConfig">{!! $output !!}</span>
                <span wire:loading wire:target="showTunnelConfig">
                    Loading...
                </span>
            </x-terminal.screen>
        </div>
    </div>
</div>
