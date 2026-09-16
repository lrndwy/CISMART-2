@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Navigasi halaman"
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            {{-- Ringkasan --}}
            <p class="text-sm text-stone-500">
                Menampilkan
                <span class="font-semibold text-stone-900">{{ $paginator->firstItem() }}</span>
                <span class="text-stone-400">–</span>
                <span class="font-semibold text-stone-900">{{ $paginator->lastItem() }}</span>
                dari <span class="font-semibold text-stone-900">{{ $paginator->total() }}</span> hasil
            </p>

            {{-- Kontrol halaman --}}
            <div class="flex flex-wrap items-center gap-1.5">
                {{-- Sebelumnya --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="Sebelumnya"
                        class="inline-flex h-9 cursor-not-allowed items-center gap-1.5 rounded-lg border border-stone-200/70 bg-white pl-2 pr-3 text-sm font-medium text-stone-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                        Sebelumnya
                    </span>
                @else
                    <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" aria-label="Sebelumnya"
                        class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-stone-300 bg-white pl-2 pr-3 text-sm font-medium text-stone-600 transition hover:border-green-700 hover:text-green-700 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-green-700 disabled:opacity-60">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                        Sebelumnya
                    </button>
                @endif

                {{-- Nomor halaman --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex h-9 w-9 items-center justify-center text-sm text-stone-400" aria-hidden="true">
                            {{ $element }}
                        </span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-transparent bg-green-700 text-sm font-semibold tabular-nums text-white">
                                        {{ $page }}
                                    </span>
                                @else
                                    <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                        x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled"
                                        aria-label="Ke halaman {{ $page }}"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-stone-300 bg-white text-sm font-medium tabular-nums text-stone-600 transition hover:border-green-700 hover:text-green-700 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-green-700 disabled:opacity-60">
                                        {{ $page }}
                                    </button>
                                @endif
                            </span>
                        @endforeach
                    @endif
                @endforeach

                {{-- Berikutnya --}}
                @if ($paginator->hasMorePages())
                    <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" aria-label="Berikutnya"
                        class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-stone-300 bg-white pl-3 pr-2 text-sm font-medium text-stone-600 transition hover:border-green-700 hover:text-green-700 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-green-700 disabled:opacity-60">
                        Berikutnya
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                @else
                    <span aria-disabled="true" aria-label="Berikutnya"
                        class="inline-flex h-9 cursor-not-allowed items-center gap-1.5 rounded-lg border border-stone-200/70 bg-white pl-3 pr-2 text-sm font-medium text-stone-300">
                        Berikutnya
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </span>
                @endif
            </div>
        </nav>
    @endif
</div>
