<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filter Bar -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Filter Rentang Waktu</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Filter data kunjungan berdasarkan tanggal pilihan Anda</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="w-full sm:w-auto">
                        <select wire:model.live="period" class="w-full sm:w-48 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2.5">
                            <option value="today">Hari Ini</option>
                            <option value="yesterday">Kemarin</option>
                            <option value="this_week">Minggu Ini</option>
                            <option value="this_month">Bulan Ini</option>
                            <option value="custom">Rentang Kustom</option>
                        </select>
                    </div>

                    @if($period === 'custom')
                        <div class="flex items-center gap-2 w-full sm:w-auto mt-2 sm:mt-0">
                            <input type="date" wire:model.live="startDate" class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2"/>
                            <span class="text-gray-500 text-xs">s/d</span>
                            <input type="date" wire:model.live="endDate" class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2"/>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Summary Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Card 1: Hari Ini -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6 shadow-sm flex flex-col justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-primary-500/5 rounded-full translate-x-8 -translate-y-8"></div>
                <div>
                    <span class="text-xs font-mono uppercase tracking-widest text-gray-500 dark:text-gray-400">Hari Ini</span>
                    <h4 class="text-3xl font-serif font-bold text-gray-900 dark:text-white mt-2">{{ number_format($todayUnique) }}</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pengunjung Unik</p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800/50 flex items-center justify-between">
                    <span class="text-xs text-gray-500">Pertumbuhan vs Kemarin:</span>
                    @if($todayGrowth >= 0)
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 px-2 py-0.5 rounded-full">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                            {{ number_format($todayGrowth, 1) }}%
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 px-2 py-0.5 rounded-full">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                            {{ number_format(abs($todayGrowth), 1) }}%
                        </span>
                    @endif
                </div>
            </div>

            <!-- Card 2: Bulan Ini -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6 shadow-sm flex flex-col justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-primary-500/5 rounded-full translate-x-8 -translate-y-8"></div>
                <div>
                    <span class="text-xs font-mono uppercase tracking-widest text-gray-500 dark:text-gray-400">Bulan Ini</span>
                    <h4 class="text-3xl font-serif font-bold text-gray-900 dark:text-white mt-2">{{ number_format($thisMonthUnique) }}</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pengunjung Unik</p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800/50 flex items-center justify-between">
                    <span class="text-xs text-gray-500">Pertumbuhan vs Bln Lalu:</span>
                    @if($monthGrowth >= 0)
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 px-2 py-0.5 rounded-full">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                            {{ number_format($monthGrowth, 1) }}%
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 px-2 py-0.5 rounded-full">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                            {{ number_format(abs($monthGrowth), 1) }}%
                        </span>
                    @endif
                </div>
            </div>

            <!-- Card 3: Total Pageviews (Filter) -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6 shadow-sm flex flex-col justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-primary-500/5 rounded-full translate-x-8 -translate-y-8"></div>
                <div>
                    <span class="text-xs font-mono uppercase tracking-widest text-gray-500 dark:text-gray-400">Total Kunjungan</span>
                    <h4 class="text-3xl font-serif font-bold text-gray-900 dark:text-white mt-2">{{ number_format($totalPageviews) }}</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Halaman Dilihat (Pageviews)</p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800/50 flex items-center justify-between">
                    <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">Untuk periode terpilih</span>
                    <span class="text-[11px] font-mono text-gray-400 dark:text-gray-500">{{ strtoupper(str_replace('_', ' ', $period)) }}</span>
                </div>
            </div>

            <!-- Card 4: Unique Visitors (Filter) -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6 shadow-sm flex flex-col justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-primary-500/5 rounded-full translate-x-8 -translate-y-8"></div>
                <div>
                    <span class="text-xs font-mono uppercase tracking-widest text-gray-500 dark:text-gray-400">Pengunjung Unik</span>
                    <h4 class="text-3xl font-serif font-bold text-gray-900 dark:text-white mt-2">{{ number_format($uniqueVisitors) }}</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Unique Visitors</p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800/50 flex items-center justify-between">
                    <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">Rasio Kunjungan:</span>
                    <span class="text-[11px] font-bold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/30 px-2 py-0.5 rounded-full">
                        {{ $uniqueVisitors > 0 ? number_format($totalPageviews / $uniqueVisitors, 1) : 0 }}x per orang
                    </span>
                </div>
            </div>
        </div>

        <!-- Grid Popular Lists -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- List 1: Most Viewed Pages -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Halaman Terpopuler</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Halaman publik paling sering dikunjungi</p>
                    </div>
                    <span class="p-2 bg-gray-50 dark:bg-gray-800 rounded-lg text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.905 0-5.64-.53-8.157-1.418m16.314 0C19.645 11.75 20 13.833 20 16m-16 0c0-2.167.355-4.25 1.006-5.418M20 16a11.975 11.975 0 01-8.157 3.918m8.157-3.918c-.029.083-.06.164-.093.245M4 16a11.975 11.975 0 008.157 3.918m-8.157-3.918c.029.083.06.164.093.245"/></svg>
                    </span>
                </div>

                <div class="space-y-4">
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
                                <div class="bg-primary-500 h-full rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400 dark:text-gray-500 text-xs font-sans">
                            Belum ada data kunjungan pada halaman.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- List 2: Most Viewed Products -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Produk Terpopuler</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Produk katalog paling sering diincar</p>
                    </div>
                    <span class="p-2 bg-gray-50 dark:bg-gray-800 rounded-lg text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </span>
                </div>

                <div class="space-y-4">
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
                                <div class="bg-amber-500 h-full rounded-full transition-all duration-500" style="width: {{ $prodPercentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400 dark:text-gray-500 text-xs font-sans">
                            Belum ada data kunjungan pada produk.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- List 3: Most Viewed Posts (Articles) -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Artikel Blog Terpopuler</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Artikel blog paling sering dibaca</p>
                    </div>
                    <span class="p-2 bg-gray-50 dark:bg-gray-800 rounded-lg text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 12h-15m0 0l6.75 6.75M4.5 12l6.75-6.75"/></svg>
                    </span>
                </div>

                <div class="space-y-4">
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
                                <div class="bg-indigo-500 h-full rounded-full transition-all duration-500" style="width: {{ $postPercentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400 dark:text-gray-500 text-xs font-sans">
                            Belum ada data kunjungan pada artikel.
                        </div>
                    @endforelse
                </div>
            </div>
            
        </div>
    </div>
</x-filament-panels::page>
