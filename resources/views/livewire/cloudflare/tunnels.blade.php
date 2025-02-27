<div class="border rounded-xl grid grid-cols-1 gap-3 p-3" x-data="{ showTerminal: false }">
    <div class="flex justify-between border-b w-full">
        <h3 class="font-semibold">Tunnels</h3>
        <div>
            <x-icon name="arrow-path"
                    class="h-4 w-4 cursor-pointer hover:text-indigo-600"
                    wire:loading.class.remove="cursor-pointer"
                    wire:loading.class="animate-spin cursor-not-allowed"
                    wire:loading.attr="disabled"
                    wire:target="getTunnels"
                    wire:click="getTunnels"/>
        </div>
    </div>
    <div>
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
                                <x-ui.table.td>
                                    <a href="https://{{$tunnel['domain']}}" class="text-indigo-600 hover:underline" target="_blank">{{$tunnel['domain']}}</a>
                                </x-ui.table.td>
                                <x-ui.table.td class="flex gap-2 items-center">
                                    @if($tunnel['configFile'])
                                        <span class="cursor-pointer hover:text-indigo-500"
                                              wire:click="showTunnelConfig('{{$tunnel['configFile']}}')"
                                              @click="showTerminal = true">
                                            <x-icon name="arrows-pointing-out" class="w-4 h-4"/>
                                        </span>
                                    @endif
                                    <span class="cursor-pointer text-red-500 hover:text-red-800"
                                          wire:loading.remove
                                          wire:target="deleteTunnel('{{$tunnel['uuid']}}')"
                                          wire:click="deleteTunnel('{{$tunnel['uuid']}}')"
                                          @click="showTerminal = true"
                                    ><x-icon name="x-circle" class="w-5 h-5"/></span>
                                    <span class="" wire:loading wire:target="deleteTunnel('{{$tunnel['uuid']}}')">Deleting...</span>
                                </x-ui.table.td>
                            </tr>
                        @endforeach
                    </x-ui.table.tbody>
                </x-ui.table>
            @endif
        </div>
    </div>
    <x-terminal.screen stream="{{$streamTo}}">
        <span wire:loading.remove wire:target="showTunnelConfig">{!! $output !!}</span>
        <span wire:loading wire:target="showTunnelConfig">
            Loading...
        </span>
    </x-terminal.screen>
</div>
