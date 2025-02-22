@props(['stream' => null])

@if($stream)
    <div class="bg-gray-800 text-white p-6 rounded-b-xl overflow-hidden font-mono text-sm whitespace-pre-wrap h-80 overflow-y-scroll"
         wire:stream="{{$stream}}" >{{$slot}}</div>
@else
    <div class="bg-gray-800 text-white p-6 rounded-b-xl overflow-hidden font-mono text-sm whitespace-pre-wrap h-80 overflow-y-scroll"
         >{{$slot}}</div>
@endif

