@props([
    'show',
    'zIndex' => 40,
    'click' => null,
])

<div
    x-show="{{ $show }}"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @if($click) @click="{{ $click }}" @endif
    aria-hidden="true"
    {{ $attributes->merge(['class' => 'fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity ' . ($zIndex == 90 ? 'z-[90]' : 'z-40')]) }}
></div>
