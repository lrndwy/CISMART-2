{{-- Filter groups dipakai sidebar desktop dan bottom sheet mobile (include dua kali). --}}
<div class="divide-y divide-stone-200">
    {{-- Kategori --}}
    <div class="pb-5">
        <h4 class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-3">Kategori</h4>
        <div class="space-y-1 max-h-64 overflow-y-auto">
            <label class="flex items-center cursor-pointer rounded-md px-2 py-1.5 hover:bg-stone-50">
                <input name="category" type="radio" wire:model.live="category" value="all"
                    class="accent-green-700" />
                <span class="ml-2 text-sm text-stone-700 hover:text-green-700">Semua Kategori</span>
            </label>
            @foreach ($categories as $cat)
                <label class="flex items-center cursor-pointer rounded-md px-2 py-1.5 hover:bg-stone-50">
                    <input name="category" type="radio" wire:model.live="category" value="{{ $cat->id }}"
                        class="accent-green-700" />
                    <span class="ml-2 text-sm text-stone-700 hover:text-green-700">
                        {{ $cat->name }}
                        <span class="text-stone-400 text-xs">({{ $cat->products_count }})</span>
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Rentang Harga --}}
    <div class="py-5">
        <h4 class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-3">Rentang Harga</h4>
        <div class="space-y-1">
            @php
                $ranges = [
                    ['', '', 'Semua Harga'],
                    ['', '50000', 'Di bawah Rp 50.000'],
                    ['50000', '100000', 'Rp 50.000 - Rp 100.000'],
                    ['100000', '500000', 'Rp 100.000 - Rp 500.000'],
                    ['500000', '', 'Di atas Rp 500.000'],
                ];
            @endphp
            @foreach ($ranges as [$rMin, $rMax, $rLabel])
                <label class="flex items-center cursor-pointer rounded-md px-2 py-1.5 hover:bg-stone-50">
                    <input type="radio" wire:click="setPriceRange('{{ $rMin }}', '{{ $rMax }}')"
                        {{ $minPrice === $rMin && $maxPrice === $rMax ? 'checked' : '' }} class="accent-green-700" />
                    <span class="ml-2 text-sm text-stone-700 hover:text-green-700">{{ $rLabel }}</span>
                </label>
            @endforeach
        </div>
        <div class="mt-4 grid grid-cols-2 gap-2 px-2">
            <input type="number" min="0" wire:model.live.debounce.500ms="minPrice" placeholder="Min"
                class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-transparent focus:ring-2 focus:ring-green-700" />
            <input type="number" min="0" wire:model.live.debounce.500ms="maxPrice" placeholder="Max"
                class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-transparent focus:ring-2 focus:ring-green-700" />
        </div>
    </div>

    {{-- Lokasi --}}
    <div class="py-5">
        <h4 class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-3">Lokasi UMKM</h4>
        <x-select-dropdown prop="location"
            :options="array_merge([['value' => 'all', 'label' => 'Semua Lokasi']], collect($locations)->map(fn ($loc) => ['value' => $loc, 'label' => $loc])->all())" />
    </div>
    <div class="py-5">
        <h4 class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-3">Rating Minimum</h4>
        <div class="space-y-1">
            <label class="flex items-center cursor-pointer rounded-md px-2 py-1.5 hover:bg-stone-50">
                <input type="radio" wire:model.live="minRating" value="" class="accent-green-700" />
                <span class="ml-2 text-sm text-stone-700 hover:text-green-700">Semua Rating</span>
            </label>
            @foreach ([4.5, 4.0, 3.5, 3.0] as $rating)
                <label class="flex items-center cursor-pointer rounded-md px-2 py-1.5 hover:bg-stone-50">
                    <input type="radio" wire:model.live="minRating" value="{{ $rating }}" class="accent-green-700" />
                    <span class="ml-2 flex items-center gap-1.5">
                        <span class="text-sm text-stone-700">{{ $rating }}</span>
                        <span class="flex">
                            @for ($i = 0; $i < 5; $i++)
                                <svg class="h-3.5 w-3.5 {{ $i < $rating ? 'fill-amber-500 text-amber-500' : 'fill-none text-stone-300' }}"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                </svg>
                            @endfor
                        </span>
                        <span class="text-sm text-stone-500">ke atas</span>
                    </span>
                </label>
            @endforeach
        </div>
    </div>
</div>
