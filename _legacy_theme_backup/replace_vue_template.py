import re

with open('src/App.vue', 'r') as f:
    content = f.read()

# We want to replace everything from <template> to </template>
new_template = """<template>
  <div class="flex h-screen bg-[#f0f0f1] text-[#1e1e1e] font-sans overflow-hidden relative">
    
    <!-- Mobile Overlay -->
    <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/50 z-40 md:hidden transition-opacity"></div>

    <!-- Sidebar (Gutenberg Style) -->
    <aside :class="[
      'flex-col bg-white border-r border-[#e0e0e0] flex-shrink-0 z-50 fixed md:relative h-full transition-all duration-300',
      isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
      isDesktopMenuCollapsed ? 'md:w-0 md:overflow-hidden md:border-none' : 'w-[280px]'
    ]" class="flex">
      
      <!-- Sidebar Tabs -->
      <div class="flex items-center px-4 pt-2 border-b border-[#e0e0e0] h-[60px] flex-shrink-0">
         <button class="px-4 h-full border-b-2 border-[#3858e9] text-[13px] font-medium text-[#1e1e1e]">Navigasi</button>
         <button class="px-4 h-full border-b-2 border-transparent text-[13px] text-[#757575] hover:text-[#1e1e1e]">Sistem</button>
         <button class="ml-auto text-[#757575] hover:text-[#1e1e1e]" @click="isDesktopMenuCollapsed = true">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
         </button>
      </div>
      
      <nav class="flex-1 py-4 overflow-y-auto">
         <div class="px-4 mb-4">
            <!-- Gutenberg Feature Image Style Button -->
            <button class="w-full bg-white border border-[#757575] text-[#1e1e1e] text-[13px] py-1.5 px-4 hover:bg-[#f0f0f1] transition-colors rounded-[2px] mb-3">
              Tetapkan menu utama
            </button>
            <p class="text-[12px] text-[#757575] mb-5">Terakhir diperbarui 13 menit lalu.</p>
            
            <div class="space-y-1">
                <template v-for="(section, idx) in menuSections" :key="idx">
                    <div v-if="section.label" class="mt-4 mb-2 flex items-center justify-between cursor-pointer group">
                        <span class="text-[13px] font-medium text-[#1e1e1e]">{{ section.label }}</span>
                        <svg class="w-4 h-4 text-[#757575] group-hover:text-[#1e1e1e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    
                    <div v-for="group in section.groups" :key="group.id" class="flex flex-col py-0.5">
                       <!-- Single Item -->
                       <div v-if="group.items.length === 1" class="flex items-center justify-between py-1.5">
                           <span class="text-[13px] text-[#1e1e1e] flex items-center gap-2">
                              <!-- Optional Icon Placeholder -->
                              <span class="w-4 h-4 flex items-center justify-center text-[#757575]">
                                 <svg v-if="group.icon === 'chart'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                 <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                              </span>
                              {{ group.title }}
                           </span>
                           <button @click="setView(group.items[0].id)" class="text-[13px] text-[#3858e9] hover:underline" :class="{'underline': activeView === group.items[0].id}">Buka</button>
                       </div>
                       
                       <!-- Multiple Items -->
                       <div v-else-if="group.items.length > 1" class="flex items-start justify-between py-1.5">
                           <span class="text-[13px] text-[#1e1e1e] flex items-center gap-2 mt-0.5">
                               <span class="w-4 h-4 flex items-center justify-center text-[#757575]">
                                 <svg v-if="group.icon === 'products'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                 <svg v-else-if="group.icon === 'woo'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                 <svg v-else-if="group.icon === 'posts'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                 <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                               </span>
                               {{ group.title }}
                           </span>
                           <div class="flex flex-col items-end space-y-1.5 ml-2">
                               <button v-for="item in group.items" :key="item.id" @click="setView(item.id)" class="text-[13px] text-[#3858e9] hover:underline text-right" :class="{'underline': activeView === item.id}">{{ item.label }}</button>
                           </div>
                       </div>
    
                       <!-- Empty Item -->
                       <div v-else class="flex items-center justify-between py-1.5 opacity-50">
                           <span class="text-[13px] text-[#1e1e1e] flex items-center gap-2">
                               <span class="w-4 h-4 flex items-center justify-center text-[#757575]">
                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                               </span>
                               {{ group.title }}
                           </span>
                           <span class="text-[13px] text-[#3858e9]">Terkunci</span>
                       </div>
                    </div>
                </template>
            </div>
         </div>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 bg-[#f0f0f1]">
      
      <!-- Header Bar (Gutenberg Style) -->
      <header class="flex justify-between items-center px-2 md:px-4 h-[60px] flex-shrink-0 border-b border-[#e0e0e0] bg-white z-10 sticky top-0">
         <!-- Left: Menu Toggle / Logo -->
         <div class="flex items-center gap-2">
            <button @click="isDesktopMenuCollapsed = false, isMobileMenuOpen = true" class="md:hidden w-10 h-10 flex items-center justify-center text-[#1e1e1e] hover:bg-[#f0f0f1] rounded-[2px]">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="hidden md:flex w-10 h-10 items-center justify-center hover:bg-[#f0f0f1] rounded-[2px] transition-colors cursor-pointer">
              <div class="w-8 h-8 bg-[#1e1e1e] text-white flex items-center justify-center rounded-[2px] font-bold text-sm">R</div>
            </div>
         </div>
         
         <!-- Center: Document Title / Search (Gutenberg Command Palette Style) -->
         <div class="flex-1 max-w-[400px] flex justify-center mx-4">
            <button class="w-full flex items-center justify-between px-4 h-10 bg-[#f0f0f1] hover:bg-[#e0e0e0] transition-colors rounded-[4px] text-[#1e1e1e] group cursor-text">
               <span class="text-[13px]">Raabiha Dashboard · Laman</span>
               <span class="text-[11px] text-[#757575] font-medium group-hover:text-[#1e1e1e]">Ctrl+K</span>
            </button>
         </div>
         
         <!-- Right: Actions -->
         <div class="flex items-center gap-1">
            <span class="hidden lg:inline-block text-[13px] text-[#757575] mr-2">Simpan konsep</span>
            <button class="w-10 h-10 flex items-center justify-center text-[#1e1e1e] hover:bg-[#f0f0f1] rounded-[2px] transition-colors" title="Pratinjau Desktop/Mobile">
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </button>
            <button @click="alert('Tidak ada undo history.')" class="w-10 h-10 flex items-center justify-center text-[#1e1e1e] hover:bg-[#f0f0f1] rounded-[2px] transition-colors">
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
            </button>
            <button class="w-10 h-10 flex items-center justify-center text-[#1e1e1e] hover:bg-[#f0f0f1] rounded-[2px] transition-colors">
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            </button>
            
            <!-- Active Settings Toggle Icon -->
            <button @click="isDesktopMenuCollapsed = !isDesktopMenuCollapsed" class="w-10 h-10 flex items-center justify-center transition-colors relative" title="Pengaturan">
               <div class="w-8 h-8 flex items-center justify-center rounded-[2px]" :class="!isDesktopMenuCollapsed ? 'bg-[#1e1e1e] text-white ring-2 ring-[#3858e9] ring-offset-1' : 'bg-transparent text-[#1e1e1e] hover:bg-[#f0f0f1]'">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
               </div>
            </button>
            
            <!-- Primary Action -->
            <button class="ml-1 mr-1 bg-[#135e96] text-white text-[13px] font-medium px-4 h-8 rounded-[2px] hover:bg-[#0f4b78] transition-colors shadow-sm">
               Terbitkan
            </button>
            
            <!-- Three dots menu -->
            <button class="w-8 h-8 flex items-center justify-center text-[#1e1e1e] hover:bg-[#f0f0f1] rounded-[2px] transition-colors" @click="showUserMenu = !showUserMenu">
               <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z"/></svg>
            </button>
            
            <!-- Dropdown User Menu -->
            <div v-if="showUserMenu" class="absolute right-4 top-[100%] mt-1 w-48 bg-white border border-[#e0e0e0] shadow-sm rounded-[2px] overflow-hidden z-50">
               <div class="py-1">
                  <div class="px-4 py-2 border-b border-[#e0e0e0] mb-1">
                     <p class="text-[13px] font-medium text-[#1e1e1e]">{{ user.name || 'Julianne V.' }}</p>
                     <p class="text-[11px] text-[#757575]">{{ user.role || 'Administrator' }}</p>
                  </div>
                  <a href="#" class="block px-4 py-1.5 text-[13px] text-[#1e1e1e] hover:bg-[#f0f0f1] transition-colors">Edit Profile</a>
                  <a href="#" class="block px-4 py-1.5 text-[13px] text-[#1e1e1e] hover:bg-[#f0f0f1] transition-colors">Pengaturan</a>
                  <div class="border-t border-[#e0e0e0] my-1"></div>
                  <a href="/wp-login.php?action=logout" class="block px-4 py-1.5 text-[13px] text-red-600 hover:bg-[#f0f0f1] transition-colors">Keluar</a>
               </div>
            </div>
         </div>
      </header>

      <!-- Router Content -->
      <div class="flex-1 overflow-auto p-4 md:p-8 bg-white">
        <!-- Dashboard components will inherit standard WP content styles -->
        <div class="max-w-6xl mx-auto">
          <ProductManager v-if="activeView === 'all_products' || activeView === 'products' || activeView === 'product_categories' || activeView === 'product_tags' || activeView === 'product_attributes'" :activeView="activeView" />
          <BlogManager v-else-if="activeView === 'blog_posts' || activeView === 'add_blog_post' || activeView === 'blog_categories' || activeView === 'blog_tags'" :activeView="activeView" />
          <PageManager v-else-if="activeView === 'pages_list'" />
          <WebSettingsManager v-else-if="activeView === 'web_settings'" />
          <StoreSettingsManager v-else-if="activeView === 'store_settings'" />
          <OrderManager v-else-if="activeView === 'orders'" :orders="mockOrders" @edit-order="handleEditOrder" />
          
          <div v-else-if="activeView === 'overview'" class="space-y-6">
             <div class="mb-8">
               <h2 class="font-sans text-[28px] md:text-[32px] font-bold text-[#1e1e1e] mb-2 leading-none">Portfolio Overview</h2>
               <p class="text-[11px] text-[#757575] uppercase tracking-widest">Real-time performance metrics for Raabiha Luxury Goods.</p>
             </div>

             <!-- Overview Cards -->
             <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
               <div class="bg-white p-6 border border-[#e0e0e0] rounded-[2px] hover:border-[#1e1e1e] transition-colors relative overflow-hidden group">
                 <div class="absolute inset-0 bg-[#f0f0f1] scale-x-0 group-hover:scale-x-100 origin-left transition-transform duration-500 ease-out z-0"></div>
                 <div class="relative z-10">
                   <h3 class="text-[10px] tracking-widest uppercase font-bold text-[#757575] mb-4">Total Sales</h3>
                   <div class="text-3xl font-sans font-bold text-[#1e1e1e] mb-6">{{ stats.formatted_rev || 'Rp 0' }}</div>
                   <div class="w-full h-8 flex items-end">
                      <svg class="w-full h-full text-[#3858E9]" viewBox="0 0 100 20" preserveAspectRatio="none"><path d="M0 20 Q 20 10 40 15 T 80 5 T 100 15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                   </div>
                 </div>
               </div>
               
               <div class="bg-white p-6 border border-[#e0e0e0] rounded-[2px] hover:border-[#1e1e1e] transition-colors relative overflow-hidden group">
                 <div class="absolute inset-0 bg-[#f0f0f1] scale-x-0 group-hover:scale-x-100 origin-left transition-transform duration-500 ease-out z-0"></div>
                 <div class="relative z-10">
                   <h3 class="text-[10px] tracking-widest uppercase font-bold text-[#757575] mb-4">Active Products</h3>
                   <div class="text-3xl font-sans font-bold text-[#1e1e1e] mb-6">{{ stats.total_products || 0 }}</div>
                   <div class="w-full h-8 flex items-end border-b border-[#e0e0e0]"></div>
                 </div>
               </div>
               
               <div class="bg-white p-6 border border-[#e0e0e0] rounded-[2px] hover:border-[#1e1e1e] transition-colors relative overflow-hidden group">
                 <div class="absolute inset-0 bg-[#f0f0f1] scale-x-0 group-hover:scale-x-100 origin-left transition-transform duration-500 ease-out z-0"></div>
                 <div class="relative z-10">
                   <h3 class="text-[10px] tracking-widest uppercase font-bold text-[#757575] mb-4">Orders Today</h3>
                   <div class="text-3xl font-sans font-bold text-[#1e1e1e] mb-6">{{ stats.orders_today || 0 }}</div>
                   <div class="w-full h-8 flex items-end">
                      <svg class="w-full h-full text-[#3858e9]" viewBox="0 0 100 20" preserveAspectRatio="none"><path d="M0 20 L 100 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                   </div>
                 </div>
               </div>
               
               <div class="bg-white p-6 border border-[#e0e0e0] rounded-[2px] hover:border-[#1e1e1e] transition-colors relative overflow-hidden group">
                 <div class="absolute inset-0 bg-[#f0f0f1] scale-x-0 group-hover:scale-x-100 origin-left transition-transform duration-500 ease-out z-0"></div>
                 <div class="relative z-10">
                   <h3 class="text-[10px] tracking-widest uppercase font-bold text-[#757575] mb-4">Customer Base</h3>
                   <div class="text-3xl font-sans font-bold text-[#1e1e1e] mb-6">{{ stats.total_customers || 0 }}</div>
                   <div class="w-full h-8 flex items-end">
                      <svg class="w-full h-full text-[#1e1e1e]" viewBox="0 0 100 20" preserveAspectRatio="none"><path d="M0 20 L 20 15 L 40 18 L 60 12 L 80 16 L 100 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                   </div>
                 </div>
               </div>
             </div>

             <!-- Main Content Area -->
             <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Large Chart Placeholder -->
                <div class="lg:col-span-2 bg-[#f0f0f1] p-6 border border-[#e0e0e0] rounded-[2px]">
                   <div class="flex justify-between items-center mb-8">
                     <h3 class="text-2xl font-sans font-bold text-[#1e1e1e]">Revenue vs. Target</h3>
                     <div class="flex gap-4">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#1e1e1e]"></span><span class="text-[10px] uppercase tracking-widest font-bold">Actual</span></div>
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#cccccc]"></span><span class="text-[10px] uppercase tracking-widest font-bold text-[#757575]">Target</span></div>
                     </div>
                   </div>
                   <div class="h-64 flex items-end gap-2">
                     <div v-for="i in 12" :key="i" class="flex-1 bg-[#cccccc] hover:bg-[#1e1e1e] transition-colors relative group" :style="{ height: `${Math.random() * 80 + 20}%` }">
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-[#1e1e1e] text-white text-[10px] px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">IDR {{ Math.floor(Math.random() * 10) + 1 }}M</div>
                     </div>
                   </div>
                </div>

                <!-- Right Sidebar (Alerts/Info) -->
                <div class="space-y-6">
                   <!-- Alerts -->
                   <div class="bg-white border border-[#e0e0e0] rounded-[2px] p-6">
                      <div class="flex justify-between items-center mb-6">
                         <h4 class="text-[10px] uppercase tracking-widest font-bold text-[#1e1e1e]">Stock Alerts</h4>
                         <span class="text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                      </div>
                      <div class="space-y-4">
                         <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-[#f0f0f1] flex-shrink-0">
                               <img v-if="recentProducts[0]" :src="recentProducts[0].image" class="w-full h-full object-cover">
                            </div>
                            <div>
                               <p class="text-sm font-bold text-[#1e1e1e] leading-tight">Elite Runner V1</p>
                               <p class="text-[10px] font-bold text-red-500 uppercase tracking-widest mt-1">2 Units Remaining</p>
                            </div>
                         </div>
                         <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-[#f0f0f1] flex-shrink-0">
                               <img v-if="recentProducts[1]" :src="recentProducts[1].image" class="w-full h-full object-cover">
                            </div>
                            <div>
                               <p class="text-sm font-bold text-[#1e1e1e] leading-tight">Minimalist Chrono</p>
                               <p class="text-[10px] font-bold text-red-500 uppercase tracking-widest mt-1">0 Units (Restock Req.)</p>
                            </div>
                         </div>
                      </div>
                   </div>

                   <!-- Recent Activity -->
                   <div class="bg-white border border-[#e0e0e0] rounded-[2px] p-6">
                      <h4 class="text-[10px] uppercase tracking-widest font-bold text-[#1e1e1e] mb-6">Recent Activity</h4>
                      <div class="space-y-4">
                         <div class="border-l-2 border-[#1e1e1e] pl-4">
                            <p class="text-xs text-[#1e1e1e] font-bold">New order #1089</p>
                            <p class="text-[10px] text-[#757575] mt-1">10 minutes ago</p>
                         </div>
                         <div class="border-l-2 border-[#e0e0e0] pl-4">
                            <p class="text-xs text-[#757575]">Product price updated</p>
                            <p class="text-[10px] text-[#757575] mt-1">1 hour ago</p>
                         </div>
                         <div class="border-l-2 border-[#e0e0e0] pl-4">
                            <p class="text-xs text-[#757575]">System backup complete</p>
                            <p class="text-[10px] text-[#757575] mt-1">3 hours ago</p>
                         </div>
                      </div>
                   </div>
                </div>
             </div>
          </div>
          <div v-else class="text-center py-20">
             <h2 class="text-lg font-sans text-[#1e1e1e] mb-2">Module Not Ready</h2>
             <p class="text-sm text-[#757575]">The selected module is currently under development.</p>
          </div>
        </div>
      </div>
    </main>

    <!-- Cashier Authorization Modal -->
    <div v-if="showApprovalModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
       <div class="bg-white max-w-sm w-full p-6 border-t-4 border-[#1e1e1e] shadow-2xl relative overflow-hidden rounded-[2px]">
          <h3 class="font-sans text-xl font-bold text-[#1e1e1e] mb-2">Otorisasi Diperlukan</h3>
          <p class="text-[#757575] text-xs mb-6 leading-relaxed">Pesanan ini telah dikunci. Anda membutuhkan PIN atau Password dari Shop Manager untuk mengubah status.</p>
          
          <input type="password" v-model="approvalPassword" placeholder="Masukkan PIN/Password" class="w-full bg-[#f0f0f1] border-none text-[#1e1e1e] px-4 py-3 text-sm focus:ring-1 focus:ring-[#1e1e1e] outline-none transition-shadow mb-2 rounded-[2px]">
          <p v-if="approvalError" class="text-red-500 text-[10px] uppercase tracking-widest font-bold mb-4">{{ approvalError }}</p>
          
          <div class="flex gap-3 mt-6">
             <button @click="showApprovalModal = false" class="flex-1 bg-[#f0f0f1] text-[#1e1e1e] text-xs font-bold uppercase tracking-widest py-3 hover:bg-[#e0e0e0] transition-colors rounded-[2px]">Batal</button>
             <button @click="verifyApproval" class="flex-1 bg-[#1e1e1e] text-white text-xs font-bold uppercase tracking-widest py-3 hover:bg-[#333] transition-colors shadow-lg rounded-[2px]">Verifikasi</button>
          </div>
       </div>
    </div>

    <!-- Edit Order Status Modal -->
    <div v-if="showEditOrderModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
       <div class="bg-white max-w-sm w-full p-6 shadow-2xl rounded-[2px]">
          <h3 class="font-sans text-xl font-bold text-[#1e1e1e] mb-4">Ubah Status Pesanan</h3>
          <p class="text-xs text-[#757575] mb-2">Pesanan: <strong class="text-[#1e1e1e]">{{ editingOrder?.id }}</strong> - {{ editingOrder?.customer }}</p>
          
          <select v-model="editOrderStatus" class="w-full bg-[#f0f0f1] border-none text-[#1e1e1e] px-4 py-3 text-sm focus:ring-1 focus:ring-[#1e1e1e] outline-none transition-shadow mb-6 rounded-[2px]">
             <option value="Pending Payment">Pending Payment</option>
             <option value="Processing">Processing</option>
             <option value="Completed">Completed</option>
             <option value="Cancelled">Cancelled</option>
          </select>
          
          <div class="flex gap-3">
             <button @click="showEditOrderModal = false" class="flex-1 bg-[#f0f0f1] text-[#1e1e1e] text-xs font-bold uppercase tracking-widest py-3 hover:bg-[#e0e0e0] transition-colors rounded-[2px]">Batal</button>
             <button @click="saveOrderStatus" class="flex-1 bg-[#3858E9] text-white text-xs font-bold uppercase tracking-widest py-3 hover:bg-blue-700 transition-colors shadow-lg rounded-[2px]">Simpan</button>
          </div>
       </div>
    </div>

  </div>
</template>
"""

content = re.sub(r'<template>.*</template>', new_template, content, flags=re.DOTALL)

with open('src/App.vue', 'w') as f:
    f.write(content)

print("Template replaced.")
