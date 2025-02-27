@props(['stream' => null])

<div {{$attributes->merge([])}}>
    <div class="cursor-pointer hover:text-indigo-600 text-indigo-300">
        <span x-show="!showTerminal" @click="showTerminal = true" x-transition>
            <x-icon name="chevron-right" class="h-5 w-5"/>
        </span>
        <span x-show="showTerminal" @click="showTerminal = false" x-transition>
            <x-icon name="chevron-down" class="h-5 w-5"/>
        </span>
    </div>
    <div x-show="showTerminal" x-transition>
        <div class="bg-gray-800 text-white p-6 rounded-b-xl overflow-hidden font-mono text-sm whitespace-pre-wrap h-80 overflow-y-scroll scrollbar-none terminal-screen scroll-m-0">
            @if($stream)
                <div wire:stream="{{$stream}}">{{$slot}}</div>
            @else
                <div>{{$slot}}</div>
            @endif
        </div>
    </div>
</div>

