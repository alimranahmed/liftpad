@props(['stream' => null, 'rounded' => 'bottom'])

@php
    $rounded = [
        'bottom' => 'rounded-b-xl',
        'all' => 'rounded-xl',
    ][$rounded];
    $terminalClass = "bg-gray-800 text-white px-3 {$rounded} overflow-hidden font-mono text-sm whitespace-pre-line h-auto max-h-80 overflow-y-scroll overflow-x-scroll scrollbar-none terminal-screen scroll-m-0";
@endphp
<div {{$attributes->merge([])}}>
    @if($stream)
        <div class="cursor-pointer hover:text-indigo-600 text-indigo-300">
            <span x-show="!showTerminal" @click="showTerminal = true" x-transition>
                <x-icon name="chevron-right" class="h-5 w-5"/>
            </span>
            <span x-show="showTerminal" @click="showTerminal = false" x-transition>
                <x-icon name="chevron-down" class="h-5 w-5"/>
            </span>
        </div>
        <div x-show="showTerminal" x-transition x-transition.duration.500ms x-transition.scale.origin.top>
            <div class="{{$terminalClass}}">
                    <div wire:stream="{{$stream}}">{{$slot}}</div>
            </div>
        </div>
    @else
        <div class="{{$terminalClass}}">
            <div>{{$slot}}</div>
        </div>
    @endif
</div>

