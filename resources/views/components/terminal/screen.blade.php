@props(['stream' => null])

<div class="bg-gray-800 text-white p-6 rounded-b-xl overflow-hidden font-mono text-sm whitespace-pre-wrap h-80 overflow-y-scroll terminal-screen">
    @if($stream)
        <div wire:stream="{{$stream}}">{{$slot}}</div>
    @else
        <div>{{$slot}}</div>
    @endif
</div>

