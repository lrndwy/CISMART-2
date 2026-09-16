{{-- Grup filter toko — dipakai sidebar desktop dan bottom sheet mobile. --}}
<div class="divide-y divide-stone-200">
    {{-- Lokasi --}}
    <div class="pb-5">
        <h4 class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-3">Lokasi</h4>
        <x-select-dropdown prop="location"
            :options="array_merge([['value' => 'all', 'label' => 'Semua Lokasi']], collect($locations)->map(fn ($loc) => ['value' => $loc, 'label' => $loc])->all())" />
    </div>
    <div class="py-5">
        <h4 class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-3">Kategori</h4>
        <div class="space-y-1">
            @foreach ($categories as $cat)
                <label class="flex items-center cursor-pointer rounded-md px-2 py-1.5 hover:bg-stone-50">
                    <input type="radio" name="shop-category" wire:model.live="category" value="{{ $cat['id'] }}"
                        class="accent-green-700" />
                    <span class="ml-2 text-sm text-stone-700 hover:text-green-700">{{ $cat['name'] }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Rating --}}
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
