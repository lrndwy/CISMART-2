{{-- Dropdown bergaya (Alpine) yang tersinkron ke properti Livewire via $wire. --}}
@props(['prop', 'options' => [], 'label' => null])

@php
    $optsJson = json_encode(array_values($options), JSON_THROW_ON_ERROR);
    $prefix = $label ? $label.': ' : '';
@endphp

<div x-data="{
        open: false,
        options: {{ $optsJson }},
        prefix: @js($prefix),
        get value() { return $wire.{{ $prop }} },
        get currentLabel() {
            const o = this.options.find(o => String(o.value) === String(this.value));
            return o ? o.label : '';
        },
        get display() { return this.prefix + this.currentLabel },
        pick(v) {
            this.open = false;
            this.$wire.set('{{ $prop }}', v);
        }
    }" {{ $attributes->merge(['class' => 'relative']) }}>
    <button type="button" @click="open = !open" :aria-expanded="open" aria-haspopup="listbox"
        @keydown.escape.window="open = false"
        class="w-full flex items-center justify-between gap-2 rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 hover:border-stone-400 focus:outline-none focus:border-transparent focus:ring-2 focus:ring-green-700 transition">
        <span class="truncate text-left" x-text="display"></span>
        <svg class="h-4 w-4 text-stone-400 shrink-0 transition-transform duration-200" :class="open && 'rotate-180'"
            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div x-show="open" x-transition.origin.top.left
        @click.outside="open = false" @keydown.escape.window="open = false"
        style="display: none;"
        class="absolute z-30 mt-1.5 w-full max-h-64 overflow-y-auto rounded-xl border border-stone-200 bg-white py-1 shadow-[0_12px_28px_-14px_rgba(28,25,23,0.18)]"
        role="listbox">
        <template x-for="o in options" :key="String(o.value)">
            <button type="button" @click="pick(o.value)" role="option"
                :aria-selected="String(o.value) === String(value)"
                :class="String(o.value) === String(value) ? 'bg-green-50 text-green-800 font-medium' : 'text-stone-700 hover:bg-stone-50'"
                class="w-full text-left px-3 py-2 text-sm flex items-center justify-between gap-2"
                x-text="o.label"></button>
        </template>
    </div>
</div>
