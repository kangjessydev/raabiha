import re

with open('raabiha-theme/src/App.vue', 'r', encoding='utf-8') as f:
    content = f.read()

new_template = """<template>
  <div class="flex h-screen bg-[#f7f7f5] text-[#1a1a1a] font-sans overflow-hidden">
    <!-- Sidebar -->
    <aside class="hidden md:flex flex-col w-[260px] bg-[#f7f7f5] border-r border-[#e5e5e5] flex-shrink-0 z-10">
      <div class="px-8 py-10 flex flex-col items-start border-b-4 border-[#1a1a1a]">
        <h1 class="font-serif text-2xl font-bold tracking-widest text-[#1a1a1a] uppercase leading-none mb-1.5">AESTHETE</h1>
        <p class="font-sans text-[9px] tracking-[0.2em] uppercase text-[#737373]">ADMINISTRATION</p>
      </div>
      
      <nav class="flex-1 pt-6 px-0 space-y-1 overflow-y-auto">
         <div v-for="group in menuGroups" :key="group.title" class="mb-6">
            <p v-if="group.title !== 'Dasar'" class="px-8 text-[9px] font-bold text-[#a3a3a3] uppercase tracking-widest mb-3">{{ group.title }}</p>
            <button v-for="item in group.items" :key="item.id" @click="setView(item.id)"
              class="w-full text-left px-8 py-3 text-xs tracking-wider transition-colors uppercase font-medium flex items-center gap-4"
              :class="activeView === item.id ? 'bg-[#1a1a1a] text-white' : 'text-[#737373] hover:bg-[#e5e5e5]/50 hover:text-[#1a1a1a]'"
            >
               <svg v-if="item.id === 'overview'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
               <svg v-else-if="item.id === 'all_products'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
               <svg v-else-if="item.id === 'orders'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
               <svg v-else-if="item.id === 'store_settings'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
               <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
               {{ item.label }}
            </button>
         </div>
      </nav>

      <div class="p-6 mt-auto">
         <button class="w-full bg-[#064e3b] text-white text-[10px] tracking-widest uppercase font-bold py-4 px-4 hover:bg-[#043326] transition-colors flex items-center justify-center gap-2 rounded-sm shadow-sm">
           <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Create New Listing
         </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 bg-[#f7f7f5]">
      <!-- Header Bar -->
      <header class="flex justify-between items-center px-10 py-5 border-b border-[#e5e5e5] bg-[#f7f7f5] z-10 sticky top-0">
         <div class="flex items-center gap-3 text-[#a3a3a3] w-96 relative">
            <svg class="w-4 h-4 absolute left-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" placeholder="Search resources..." class="pl-8 bg-transparent border-none outline-none text-xs w-full placeholder-[#a3a3a3] text-[#1a1a1a] font-sans">
         </div>
         <div class="flex items-center gap-8">
            <button class="relative text-[#737373] hover:text-[#1a1a1a] transition-colors">
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
               <span class="absolute top-0 right-1 w-1.5 h-1.5 bg-red-500 rounded-full border border-white"></span>
            </button>
            <button class="text-[#737373] hover:text-[#1a1a1a] transition-colors">
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </button>
            <div class="flex items-center gap-3 border-l border-[#d4d4d4] pl-8">
               <div class="text-right">
                  <p class="text-[10px] uppercase font-bold tracking-widest text-[#1a1a1a] leading-none mb-1">{{ user.name || 'JULIANNE V.' }}</p>
                  <p class="text-[9px] uppercase tracking-widest text-[#a3a3a3] leading-none">{{ user.role || 'ADMINISTRATOR' }}</p>
               </div>
               <div class="w-9 h-9 rounded-full bg-[#d4d4d4] overflow-hidden border border-[#d4d4d4]">
                   <img v-if="user.avatar" :src="user.avatar" alt="User" class="w-full h-full object-cover">
                   <div v-else class="w-full h-full bg-[#e5e5e5] flex items-center justify-center text-xs font-bold text-[#737373]">JV</div>
               </div>
            </div>
         </div>
      </header>

      <!-- Page Content -->
      <div class="flex-1 overflow-y-auto px-10 py-12">
        <div class="max-w-[1200px] mx-auto">
          
          <!-- OVERVIEW VIEW -->
          <div v-if="activeView === 'overview'" class="space-y-8">
            <div class="flex justify-between items-end mb-8">
               <div>
                  <h2 class="font-serif text-[32px] font-bold text-[#1a1a1a] mb-2 leading-none">Portfolio Overview</h2>
                  <p class="text-[11px] text-[#737373] font-sans uppercase tracking-widest">Real-time performance metrics for Raabiha Luxury Goods.</p>
               </div>
               <div class="flex gap-4">
                  <button class="px-5 py-2.5 border border-[#d4d4d4] bg-white text-[9px] uppercase font-bold tracking-widest text-[#1a1a1a] flex items-center gap-2 hover:bg-[#f5f5f3] transition-colors rounded-sm shadow-sm">
                     <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> Last 30 Days
                  </button>
                  <button class="px-6 py-2.5 bg-[#1a1a1a] text-white text-[9px] uppercase font-bold tracking-widest flex items-center gap-2 hover:bg-black transition-colors rounded-sm shadow-sm">
                     <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Export PDF
                  </button>
               </div>
            </div>
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-4 gap-6">
              <!-- Stat 1 -->
              <div class="bg-[#f5f5f3] border border-[#e5e5e5] p-6 rounded-sm shadow-sm">
                 <div class="flex justify-between items-center mb-4">
                    <h3 class="text-[9px] uppercase font-bold tracking-widest text-[#737373]">TOTAL SALES</h3>
                    <span class="text-[9px] font-bold text-[#064e3b]">+12.4%</span>
                 </div>
                 <div class="text-3xl font-serif font-bold text-[#1a1a1a] mb-6">$184,200</div>
                 <!-- Decorative Chart Line -->
                 <svg class="w-full h-8 text-[#064e3b]" viewBox="0 0 100 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M0 15 Q15 5, 25 10 T50 15 T75 5 T100 15" /></svg>
              </div>
              <!-- Stat 2 -->
              <div class="bg-[#f5f5f3] border border-[#e5e5e5] p-6 rounded-sm shadow-sm">
                 <div class="flex justify-between items-center mb-4">
                    <h3 class="text-[9px] uppercase font-bold tracking-widest text-[#737373]">ACTIVE PRODUCTS</h3>
                    <span class="text-[9px] font-bold text-[#737373]">Steady</span>
                 </div>
                 <div class="text-3xl font-serif font-bold text-[#1a1a1a] mb-6">1,429</div>
                 <!-- Decorative Chart Line -->
                 <div class="w-full h-[2px] bg-[#d4d4d4] mt-8 rounded-full"></div>
              </div>
              <!-- Stat 3 -->
              <div class="bg-[#f5f5f3] border border-[#e5e5e5] p-6 rounded-sm shadow-sm">
                 <div class="flex justify-between items-center mb-4">
                    <h3 class="text-[9px] uppercase font-bold tracking-widest text-[#737373]">FULFILLMENT RATE</h3>
                    <span class="text-[9px] font-bold text-[#064e3b]">+0.8%</span>
                 </div>
                 <div class="text-3xl font-serif font-bold text-[#1a1a1a] mb-6">98.2<span class="text-xl">%</span></div>
                 <!-- Decorative Chart Line -->
                 <svg class="w-full h-8 text-[#064e3b]" viewBox="0 0 100 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M0 18 L100 2" /></svg>
              </div>
              <!-- Stat 4 -->
              <div class="bg-[#f5f5f3] border border-[#e5e5e5] p-6 rounded-sm shadow-sm">
                 <div class="flex justify-between items-center mb-4">
                    <h3 class="text-[9px] uppercase font-bold tracking-widest text-[#737373]">CUSTOMER GROWTH</h3>
                    <span class="text-[9px] font-bold text-red-600">-2.1%</span>
                 </div>
                 <div class="text-3xl font-serif font-bold text-[#1a1a1a] mb-6">854</div>
                 <!-- Decorative Chart Line -->
                 <svg class="w-full h-8 text-red-600" viewBox="0 0 100 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M0 5 L30 10 L60 18 L80 12 L100 15" /></svg>
              </div>
            </div>

            <!-- Middle Section -->
            <div class="grid grid-cols-4 gap-6">
               <div class="col-span-3 bg-[#f5f5f3] border border-[#e5e5e5] p-8 rounded-sm shadow-sm relative h-[400px]">
                  <div class="flex justify-between items-center mb-10">
                     <h3 class="text-2xl font-serif font-bold text-[#1a1a1a]">Revenue vs. Target</h3>
                     <div class="flex gap-4 items-center">
                        <div class="flex items-center gap-2"><div class="w-2.5 h-2.5 bg-[#1a1a1a] rounded-sm"></div><span class="text-[9px] font-bold uppercase tracking-widest text-[#1a1a1a]">Actual</span></div>
                        <div class="flex items-center gap-2"><div class="w-2.5 h-2.5 bg-[#d4d4d4] rounded-sm"></div><span class="text-[9px] font-bold uppercase tracking-widest text-[#a3a3a3]">Target</span></div>
                     </div>
                  </div>
                  <!-- Chart Lines Mockup -->
                  <div class="space-y-[50px] mt-8 w-full">
                     <div class="w-full border-b border-[#d4d4d4]"></div>
                     <div class="w-full border-b border-[#d4d4d4]"></div>
                     <div class="w-full border-b border-[#d4d4d4]"></div>
                     <div class="w-full border-b border-[#d4d4d4]"></div>
                  </div>
                  <div class="flex justify-between px-8 text-[9px] font-bold uppercase tracking-widest text-[#737373] mt-4">
                     <span>JAN</span><span>FEB</span><span>MAR</span><span>APR</span><span>MAY</span>
                  </div>
               </div>
               
               <div class="col-span-1 bg-[#f5f5f3] border border-[#e5e5e5] p-6 rounded-sm shadow-sm">
                  <div class="flex justify-between items-center mb-6">
                     <h3 class="text-[9px] uppercase font-bold tracking-widest text-[#1a1a1a]">STOCK ALERTS</h3>
                     <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  </div>
                  
                  <div class="space-y-5 mb-8">
                     <div class="flex gap-4">
                        <div class="w-12 h-12 bg-black flex-shrink-0 border border-[#e5e5e5]">
                           <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&q=80&w=100" class="w-full h-full object-cover grayscale brightness-75">
                        </div>
                        <div>
                           <p class="text-xs font-bold text-[#1a1a1a] mb-1">Elite Runner V1</p>
                           <p class="text-[9px] font-bold text-red-600 uppercase tracking-wider">2 Units Remaining</p>
                        </div>
                     </div>
                     <div class="flex gap-4">
                        <div class="w-12 h-12 bg-black flex-shrink-0 border border-[#e5e5e5]">
                           <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&q=80&w=100" class="w-full h-full object-cover grayscale brightness-75">
                        </div>
                        <div>
                           <p class="text-xs font-bold text-[#1a1a1a] mb-1">Minimalist Chrono</p>
                           <p class="text-[9px] font-bold text-red-600 uppercase tracking-wider">0 Units (Restock Req.)</p>
                        </div>
                     </div>
                  </div>
                  
                  <div class="border-t border-[#e5e5e5] pt-6">
                     <h3 class="text-[9px] uppercase font-bold tracking-widest text-[#1a1a1a] mb-5">TOP SELLERS</h3>
                     <div class="space-y-4">
                        <div class="flex justify-between items-center text-xs">
                           <span class="text-[#1a1a1a] font-medium">Leather Weekender</span>
                           <span class="text-[#064e3b] font-bold">$12,400</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                           <span class="text-[#1a1a1a] font-medium">Cashmere Scarf</span>
                           <span class="text-[#064e3b] font-bold">$8,120</span>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <!-- Recent Orders Table Section -->
            <div class="bg-[#f5f5f3] border border-[#e5e5e5] rounded-sm shadow-sm mb-12">
               <div class="p-6 flex justify-between items-center border-b border-[#e5e5e5]">
                  <h3 class="text-2xl font-serif font-bold text-[#1a1a1a]">Recent Orders</h3>
                  <button class="px-4 py-1.5 border border-[#d4d4d4] bg-white text-[9px] uppercase font-bold tracking-widest text-[#1a1a1a] flex items-center gap-2 hover:bg-[#f5f5f3] transition-colors rounded-sm">
                     <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg> Filter
                  </button>
               </div>
               
               <div class="overflow-x-auto">
                  <table class="w-full text-left">
                     <thead>
                        <tr class="bg-[#ebebeb] text-[9px] uppercase font-bold tracking-widest text-[#737373]">
                           <th class="py-4 px-6">ORDER ID</th>
                           <th class="py-4 px-6">CUSTOMER</th>
                           <th class="py-4 px-6">DATE</th>
                           <th class="py-4 px-6">TOTAL</th>
                           <th class="py-4 px-6">STATUS</th>
                           <th class="py-4 px-6">ACTION</th>
                        </tr>
                     </thead>
                     <tbody class="text-xs font-medium text-[#1a1a1a] divide-y divide-[#e5e5e5]">
                        <tr class="hover:bg-white/50 transition-colors">
                           <td class="py-4 px-6 font-bold">#RB-9214</td>
                           <td class="py-4 px-6 flex items-center gap-3">
                              <div class="w-6 h-6 rounded-full bg-[#1a1a1a] text-white flex items-center justify-center text-[8px] font-bold">AS</div>
                              Alexander Smith
                           </td>
                           <td class="py-4 px-6 text-[#737373]">Oct 12, 2023</td>
                           <td class="py-4 px-6 font-bold">$1,250.00</td>
                           <td class="py-4 px-6">
                              <span class="bg-[#dcfce7] text-[#166534] px-2 py-0.5 rounded-full text-[8px] font-bold uppercase tracking-widest">COMPLETED</span>
                           </td>
                           <td class="py-4 px-6 text-[#737373]">•••</td>
                        </tr>
                        <tr class="hover:bg-white/50 transition-colors">
                           <td class="py-4 px-6 font-bold">#RB-9215</td>
                           <td class="py-4 px-6 flex items-center gap-3">
                              <div class="w-6 h-6 rounded-full bg-[#737373] text-white flex items-center justify-center text-[8px] font-bold">ML</div>
                              Maria Lopez
                           </td>
                           <td class="py-4 px-6 text-[#737373]">Oct 12, 2023</td>
                           <td class="py-4 px-6 font-bold">$430.50</td>
                           <td class="py-4 px-6">
                              <span class="bg-[#e5e5e5] text-[#525252] px-2 py-0.5 rounded-full text-[8px] font-bold uppercase tracking-widest">PROCESSING</span>
                           </td>
                           <td class="py-4 px-6 text-[#737373]">•••</td>
                        </tr>
                        <tr class="hover:bg-white/50 transition-colors">
                           <td class="py-4 px-6 font-bold">#RB-9216</td>
                           <td class="py-4 px-6 flex items-center gap-3">
                              <div class="w-6 h-6 rounded-full bg-[#e5e5e5] text-[#1a1a1a] flex items-center justify-center text-[8px] font-bold border border-[#d4d4d4]">JW</div>
                              James Wilson
                           </td>
                           <td class="py-4 px-6 text-[#737373]">Oct 11, 2023</td>
                           <td class="py-4 px-6 font-bold">$2,100.00</td>
                           <td class="py-4 px-6">
                              <span class="bg-[#fef3c7] text-[#92400e] px-2 py-0.5 rounded-full text-[8px] font-bold uppercase tracking-widest">PENDING</span>
                           </td>
                           <td class="py-4 px-6 text-[#737373]">•••</td>
                        </tr>
                        <tr class="hover:bg-white/50 transition-colors">
                           <td class="py-4 px-6 font-bold">#RB-9217</td>
                           <td class="py-4 px-6 flex items-center gap-3">
                              <div class="w-6 h-6 rounded-full bg-[#064e3b] text-white flex items-center justify-center text-[8px] font-bold">EK</div>
                              Elena Kovic
                           </td>
                           <td class="py-4 px-6 text-[#737373]">Oct 11, 2023</td>
                           <td class="py-4 px-6 font-bold">$89.00</td>
                           <td class="py-4 px-6">
                              <span class="bg-[#fee2e2] text-[#b91c1c] px-2 py-0.5 rounded-full text-[8px] font-bold uppercase tracking-widest">SHIPPED</span>
                           </td>
                           <td class="py-4 px-6 text-[#737373]">•••</td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               
               <div class="p-6 border-t border-[#e5e5e5] flex justify-between items-center">
                  <span class="text-[9px] uppercase font-bold tracking-widest text-[#737373]">SHOWING 4 OF 1,288 ENTRIES</span>
                  <div class="flex gap-1">
                     <button class="w-7 h-7 border border-[#d4d4d4] flex items-center justify-center bg-white text-[#737373] hover:text-[#1a1a1a] text-xs">&lt;</button>
                     <button class="w-7 h-7 border border-[#1a1a1a] flex items-center justify-center bg-[#1a1a1a] text-white text-[10px] font-bold">1</button>
                     <button class="w-7 h-7 border border-[#d4d4d4] flex items-center justify-center bg-white text-[#737373] hover:text-[#1a1a1a] text-[10px] font-bold">2</button>
                     <button class="w-7 h-7 border border-[#d4d4d4] flex items-center justify-center bg-white text-[#737373] hover:text-[#1a1a1a] text-xs">&gt;</button>
                  </div>
               </div>
            </div>
          </div>
          
          <!-- OTHER VIEWS (Fallback to standard background logic if needed, but styling inherited) -->
          <div v-else-if="activeView === 'all_products'" class="bg-white p-8 rounded-sm shadow-sm border border-[#e5e5e5]">
            <h2 class="font-serif text-3xl font-bold text-[#1a1a1a] mb-6">Inventory</h2>
            <ProductManager :config="config" :initial-view="activeView" @set-view="setView" />
          </div>

          <div v-else-if="['blog_posts', 'add_blog_post', 'blog_categories'].includes(activeView)" class="bg-white p-8 rounded-sm shadow-sm border border-[#e5e5e5]">
            <h2 class="font-serif text-3xl font-bold text-[#1a1a1a] mb-6">Blog Manager</h2>
            <BlogManager :config="config" :initial-view="activeView" @set-view="setView" />
          </div>

          <div v-else-if="['pages_list', 'add_page'].includes(activeView)" class="bg-white p-8 rounded-sm shadow-sm border border-[#e5e5e5]">
            <h2 class="font-serif text-3xl font-bold text-[#1a1a1a] mb-6">Page Manager</h2>
            <PageManager :config="config" :initial-view="activeView" @set-view="setView" />
          </div>

          <div v-else-if="activeView === 'store_settings'" class="bg-white p-8 rounded-sm shadow-sm border border-[#e5e5e5]">
            <h2 class="font-serif text-3xl font-bold text-[#1a1a1a] mb-6">Store Settings</h2>
            <div class="text-[#737373] text-sm">Integrations settings page placeholder... (To be implemented)</div>
          </div>

          <div v-else class="flex flex-col items-center justify-center py-20 text-[#a3a3a3]">
             <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
             <h2 class="text-lg font-serif text-[#1a1a1a] mb-2">Module Not Ready</h2>
             <p class="text-xs">This view ({{ activeView }}) is under construction.</p>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>"""

new_content = re.sub(r'<template>.*</template>', new_template, content, flags=re.DOTALL)

with open('raabiha-theme/src/App.vue', 'w', encoding='utf-8') as f:
    f.write(new_content)

print("Replaced template successfully.")
