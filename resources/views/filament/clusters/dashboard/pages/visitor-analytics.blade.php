<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filter Bar -->
        <x-filament::section>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Filter Rentang Waktu</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Filter data kunjungan berdasarkan tanggal pilihan Anda</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <x-filament::input.wrapper class="w-full sm:w-48">
                        <x-filament::input.select wire:model.live="period">
                            <option value="today">Hari Ini</option>
                            <option value="yesterday">Kemarin</option>
                            <option value="this_week">Minggu Ini</option>
                            <option value="this_month">Bulan Ini</option>
                            <option value="custom">Rentang Kustom</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>

                    @if($period === 'custom')
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <x-filament::input.wrapper>
                                <x-filament::input type="date" wire:model.live="startDate"/>
                            </x-filament::input.wrapper>
                            <span class="text-gray-500 text-xs">s/d</span>
                            <x-filament::input.wrapper>
                                <x-filament::input type="date" wire:model.live="endDate"/>
                            </x-filament::input.wrapper>
                        </div>
                    @endif
                </div>
            </div>
        </x-filament::section>

        <!-- Summary Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Card 1: Hari Ini -->
            <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="space-y-1">
                    <div class="text-xs font-mono uppercase tracking-widest text-gray-500 dark:text-gray-400">Hari Ini</div>
                    <div class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white mt-1">
                        {{ number_format($todayUnique) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Pengunjung Unik</div>
                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800/50 flex items-center justify-between">
                        <span class="text-[10px] text-gray-400 dark:text-gray-500">vs Kemarin:</span>
                        @if($todayGrowth >= 0)
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 px-2 py-0.5 rounded-full">
                                +{{ number_format($todayGrowth, 1) }}%
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 px-2 py-0.5 rounded-full">
                                {{ number_format($todayGrowth, 1) }}%
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card 2: Bulan Ini -->
            <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="space-y-1">
                    <div class="text-xs font-mono uppercase tracking-widest text-gray-500 dark:text-gray-400">Bulan Ini</div>
                    <div class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white mt-1">
                        {{ number_format($thisMonthUnique) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Pengunjung Unik</div>
                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800/50 flex items-center justify-between">
                        <span class="text-[10px] text-gray-400 dark:text-gray-500">vs Bln Lalu:</span>
                        @if($monthGrowth >= 0)
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 px-2 py-0.5 rounded-full">
                                +{{ number_format($monthGrowth, 1) }}%
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 px-2 py-0.5 rounded-full">
                                {{ number_format($monthGrowth, 1) }}%
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card 3: Total Pageviews -->
            <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="space-y-1">
                    <div class="text-xs font-mono uppercase tracking-widest text-gray-500 dark:text-gray-400">Total Kunjungan</div>
                    <div class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white mt-1">
                        {{ number_format($totalPageviews) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Halaman Dilihat (Pageviews)</div>
                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800/50 flex items-center justify-between">
                        <span class="text-[10px] text-gray-400 dark:text-gray-500">Periode:</span>
                        <span class="text-[10px] font-mono text-gray-500 dark:text-gray-400">{{ strtoupper(str_replace('_', ' ', $period)) }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Unique Visitors (Filter) -->
            <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="space-y-1">
                    <div class="text-xs font-mono uppercase tracking-widest text-gray-500 dark:text-gray-400">Rasio Pengunjung</div>
                    <div class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white mt-1">
                        {{ number_format($uniqueVisitors) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Unique Visitors</div>
                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800/50 flex items-center justify-between">
                        <span class="text-[10px] text-gray-400 dark:text-gray-500">Rasio Kunjungan:</span>
                        <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/30 px-2 py-0.5 rounded-full">
                            {{ $uniqueVisitors > 0 ? number_format($totalPageviews / $uniqueVisitors, 1) : 0 }}x / orang
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid Popular Lists -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- List 1: Most Viewed Pages -->
            <x-filament::section>
                <x-slot name="heading">
                    Halaman Terpopuler
                </x-slot>
                <x-slot name="description">
                    Halaman publik paling sering dikunjungi
                </x-slot>

                <div class="space-y-4 mt-4">
                    @forelse($topPages as $page)
                        @php
                            $maxViews = $topPages->first()->views_count ?? 1;
                            $percentage = ($page->views_count / $maxViews) * 100;
                        @endphp
                        <div class="space-y-1.5">
                            <div class="flex items-start justify-between text-xs gap-4">
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-800 dark:text-gray-200 truncate">{{ $page->title ?: $page->url }}</p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-mono truncate">{{ $page->url }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($page->views_count) }}</span>
                                    <span class="text-gray-400 dark:text-gray-500 text-[10px] ml-1">({{ number_format($page->visitors_count) }} unik)</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-800 h-1.5 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500" style="width: {{ $percentage }}%; background-color: var(--primary-600, #10b981);"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400 dark:text-gray-500 text-xs">
                            Belum ada data kunjungan pada halaman.
                        </div>
                    @endforelse
                </div>
            </x-filament::section>

            <!-- List 2: Most Viewed Products -->
            <x-filament::section>
                <x-slot name="heading">
                    Produk Terpopuler
                </x-slot>
                <x-slot name="description">
                    Produk katalog paling sering diincar
                </x-slot>

                <div class="space-y-4 mt-4">
                    @forelse($topProducts as $prod)
                        @php
                            $maxProdViews = $topProducts->first()->views_count ?? 1;
                            $prodPercentage = ($prod->views_count / $maxProdViews) * 100;
                        @endphp
                        <div class="space-y-1.5">
                            <div class="flex items-start justify-between text-xs gap-4">
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-800 dark:text-gray-200 truncate">{{ $prod->title }}</p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-mono">ID Produk: #{{ $prod->model_id }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($prod->views_count) }}</span>
                                    <span class="text-gray-400 dark:text-gray-500 text-[10px] ml-1">({{ number_format($prod->visitors_count) }} unik)</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-800 h-1.5 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500" style="width: {{ $prodPercentage }}%; background-color: #f59e0b;"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400 dark:text-gray-500 text-xs">
                            Belum ada data kunjungan pada produk.
                        </div>
                    @endforelse
                </div>
            </x-filament::section>

            <!-- List 3: Most Viewed Posts -->
            <x-filament::section>
                <x-slot name="heading">
                    Artikel Blog Terpopuler
                </x-slot>
                <x-slot name="description">
                    Artikel blog paling sering dibaca
                </x-slot>

                <div class="space-y-4 mt-4">
                    @forelse($topPosts as $post)
                        @php
                            $maxPostViews = $topPosts->first()->views_count ?? 1;
                            $postPercentage = ($post->views_count / $maxPostViews) * 100;
                        @endphp
                        <div class="space-y-1.5">
                            <div class="flex items-start justify-between text-xs gap-4">
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-800 dark:text-gray-200 truncate">{{ $post->title }}</p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-mono">ID Post: #{{ $post->model_id }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($post->views_count) }}</span>
                                    <span class="text-gray-400 dark:text-gray-500 text-[10px] ml-1">({{ number_format($post->visitors_count) }} unik)</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-800 h-1.5 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500" style="width: {{ $postPercentage }}%; background-color: #6366f1;"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400 dark:text-gray-500 text-xs">
                            Belum ada data kunjungan pada artikel.
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
            
        </div>
    </div>
</x-filament-panels::page>
