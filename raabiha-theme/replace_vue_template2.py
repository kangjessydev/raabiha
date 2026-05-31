import re

with open('src/App.vue', 'r') as f:
    content = f.read()

new_template = """<template>
  <div class="flex h-screen bg-[#f0f0f1] text-[#1e1e1e] font-sans overflow-hidden relative">
    
    <!-- Mobile Overlay -->
    <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/50 z-40 md:hidden transition-opacity"></div>

    <!-- Sidebar (Gutenberg Dashboard Style) -->
    <aside :class="[
      'flex-col bg-white border-r border-[#e0e0e0] flex-shrink-0 z-50 fixed md:relative h-full transition-all duration-300',
      isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
      isDesktopMenuCollapsed ? 'md:w-0 md:overflow-hidden md:border-none' : 'w-[260px]'
    ]" class="flex">
      
      <!-- Sidebar Header (Logo) -->
      <div class="flex items-center px-4 h-[60px] border-b border-[#e0e0e0] flex-shrink-0">
         <div class="w-8 h-8 bg-[#1e1e1e] text-white flex items-center justify-center rounded-[2px] font-bold text-sm mr-3">R</div>
         <h1 class="text-[14px] font-semibold text-[#1e1e1e]">Raabiha Store</h1>
         <button @click="isMobileMenuOpen = false" class="md:hidden ml-auto text-[#757575]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
         </button>
      </div>
      
      <!-- Sidebar Navigation -->
      <nav class="flex-1 py-4 overflow-y-auto px-3">
         <div v-for="(section, idx) in menuSections" :key="idx" class="mb-6">
            <div v-if="section.label" class="px-3 mb-2">
               <h3 class="text-[11px] font-semibold text-[#757575] uppercase tracking-wider">{{ section.label }}</h3>
            </div>
            
            <div class="space-y-1">
               <div v-for="group in section.groups" :key="group.id" class="relative">
                  
                  <!-- Single Item Group -->
                  <div v-if="group.items.length === 1">
                     <button @click="setView(group.items[0].id)" 
                        class="w-full flex items-center gap-3 px-3 py-2 text-[13px] rounded-[2px] transition-colors"
                        :class="activeView === group.items[0].id ? 'bg-[#007CBA] text-white' : 'text-[#1e1e1e] hover:bg-[#f0f0f1]'"
                     >
                        <svg v-if="group.icon === 'chart'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ group.items[0].label }}
                     </button>
                  </div>
                  
                  <!-- Multi-Item Group -->
                  <div v-else-if="group.items.length > 1" class="group/nav">
                     <button @click="setView(group.items[0].id)" 
                        class="w-full flex items-center justify-between gap-3 px-3 py-2 text-[13px] rounded-[2px] transition-colors"
                        :class="isGroupActive(group) ? 'text-[#007CBA] font-medium' : 'text-[#1e1e1e] hover:bg-[#f0f0f1]'"
                     >
                        <div class="flex items-center gap-3">
                           <svg v-if="group.icon === 'products'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                           <svg v-else-if="group.icon === 'woo'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                           <svg v-else-if="group.icon === 'posts'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                           <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                           {{ group.title }}
                        </div>
                        <svg class="w-4 h-4 text-[#757575] transition-transform duration-200" :class="isGroupActive(group) ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/></svg>
                     </button>
                     
                     <!-- Dropdown Sub-items -->
                     <div v-show="isGroupActive(group)" class="mt-1 ml-9 pl-3 border-l border-[#e0e0e0] space-y-1 py-1">
                        <button v-for="item in group.items" :key="item.id" @click="setView(item.id)"
                           class="w-full text-left px-3 py-1.5 text-[13px] rounded-[2px] transition-colors"
                           :class="activeView === item.id ? 'bg-[#f0f0f1] text-[#007CBA] font-medium' : 'text-[#1e1e1e] hover:text-[#007CBA] hover:bg-[#f0f0f1]'"
                        >
                           {{ item.label }}
                        </button>
                     </div>
                  </div>
                  
                  <!-- Empty Group -->
                  <div v-else>
                     <button class="w-full flex items-center justify-between gap-3 px-3 py-2 text-[13px] rounded-[2px] text-[#757575] opacity-50 cursor-not-allowed">
                        <div class="flex items-center gap-3">
                           <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                           {{ group.title }}
                        </div>
                        <span class="text-[10px] bg-[#e0e0e0] px-1.5 py-0.5 rounded">Segera</span>
                     </button>
                  </div>

               </div>
            </div>
         </div>
      </nav>
      
      <!-- Bottom Setting/Collapse -->
      <div class="border-t border-[#e0e0e0] p-4 flex items-center justify-between">
         <button class="flex items-center gap-2 text-[#757575] hover:text-[#1e1e1e] text-[13px] transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Pengaturan
         </button>
         <button @click="isDesktopMenuCollapsed = true" class="hidden md:block text-[#757575] hover:text-[#1e1e1e]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
         </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 bg-[#f0f0f1]">
      
      <!-- Top Header Bar -->
      <header class="flex justify-between items-center px-4 md:px-6 h-[60px] flex-shrink-0 border-b border-[#e0e0e0] bg-white z-10 sticky top-0">
         
         <div class="flex items-center gap-4">
            <button @click="isDesktopMenuCollapsed = false, isMobileMenuOpen = true" class="md:hidden text-[#1e1e1e]">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            
            <!-- Breadcrumbs -->
            <div class="hidden sm:flex items-center text-[13px] text-[#1e1e1e]">
               <span class="text-[#757575] hover:text-[#1e1e1e] cursor-pointer">Dashboard</span>
               <span class="mx-2 text-[#e0e0e0]">/</span>
               <span class="font-medium capitalize">{{ activeView.replace(/_/g, ' ') }}</span>
            </div>
         </div>
         
         <!-- Actions -->
         <div class="flex items-center gap-2">
            
            <div class="relative w-64 hidden md:flex items-center bg-white border border-[#e0e0e0] rounded-[2px] px-3 py-1.5 focus-within:border-[#007CBA] focus-within:ring-1 focus-within:ring-[#007CBA] transition-all">
              <svg class="w-4 h-4 text-[#757575] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              <input type="text" placeholder="Search..." class="bg-transparent border-none outline-none text-[13px] w-full placeholder-[#757575] text-[#1e1e1e] font-sans">
            </div>

            <button class="w-9 h-9 flex items-center justify-center text-[#757575] hover:text-[#1e1e1e] hover:bg-[#f0f0f1] rounded-[2px] transition-colors relative ml-2">
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
               <span class="absolute top-2 right-2 w-2 h-2 bg-[#007CBA] rounded-full border border-white"></span>
            </button>

            <!-- User Profile Dropdown -->
            <div class="relative ml-2">
               <button @click="showUserMenu = !showUserMenu" class="flex items-center gap-2 hover:bg-[#f0f0f1] px-2 py-1 rounded-[2px] transition-colors">
                  <div class="w-7 h-7 rounded-full bg-[#007CBA] text-white flex items-center justify-center text-[11px] font-bold">
                     JV
                  </div>
               </button>
               
               <div v-if="showUserMenu" class="absolute right-0 top-full mt-2 w-48 bg-white border border-[#e0e0e0] shadow-sm rounded-[2px] overflow-hidden z-50">
                  <div class="py-1">
                     <div class="px-4 py-2 border-b border-[#e0e0e0] mb-1 bg-[#f0f0f1]">
                        <p class="text-[13px] font-medium text-[#1e1e1e]">{{ user.name || 'Julianne V.' }}</p>
                        <p class="text-[11px] text-[#757575]">{{ user.role || 'Administrator' }}</p>
                     </div>
                     <a href="#" class="block px-4 py-1.5 text-[13px] text-[#1e1e1e] hover:bg-[#007CBA] hover:text-white transition-colors">Edit Profile</a>
                     <a href="#" class="block px-4 py-1.5 text-[13px] text-[#1e1e1e] hover:bg-[#007CBA] hover:text-white transition-colors">Pengaturan</a>
                     <div class="border-t border-[#e0e0e0] my-1"></div>
                     <a href="/wp-login.php?action=logout" class="block px-4 py-1.5 text-[13px] text-red-600 hover:bg-[#f0f0f1] transition-colors">Keluar</a>
                  </div>
               </div>
            </div>
         </div>
      </header>

      <!-- Router Content -->
      <div class="flex-1 overflow-auto p-4 md:p-8 bg-[#f0f0f1]">
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
               <h2 class="font-sans text-[28px] md:text-[32px] font-bold text-[#1e1e1e] mb-2 leading-tight">Dashboard</h2>
               <p class="text-[13px] text-[#757575]">Real-time performance metrics for Raabiha Luxury Goods.</p>
             </div>

             <!-- Overview Cards -->
             <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
               <div class="bg-white p-5 border border-[#e0e0e0] rounded-[2px] transition-colors">
                 <div>
                   <h3 class="text-[13px] font-medium text-[#757575] mb-2">Total Sales</h3>
                   <div class="text-3xl font-sans font-bold text-[#1e1e1e] mb-4">{{ stats.formatted_rev || 'Rp 0' }}</div>
                   <div class="w-full h-8 flex items-end">
                      <svg class="w-full h-full text-[#007CBA]" viewBox="0 0 100 20" preserveAspectRatio="none"><path d="M0 20 Q 20 10 40 15 T 80 5 T 100 15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                   </div>
                 </div>
               </div>
               
               <div class="bg-white p-5 border border-[#e0e0e0] rounded-[2px] transition-colors">
                 <div>
                   <h3 class="text-[13px] font-medium text-[#757575] mb-2">Active Products</h3>
                   <div class="text-3xl font-sans font-bold text-[#1e1e1e] mb-4">{{ stats.total_products || 0 }}</div>
                   <div class="w-full h-8 flex items-end border-b border-[#e0e0e0]"></div>
                 </div>
               </div>
               
               <div class="bg-white p-5 border border-[#e0e0e0] rounded-[2px] transition-colors">
                 <div>
                   <h3 class="text-[13px] font-medium text-[#757575] mb-2">Orders Today</h3>
                   <div class="text-3xl font-sans font-bold text-[#1e1e1e] mb-4">{{ stats.orders_today || 0 }}</div>
                   <div class="w-full h-8 flex items-end">
                      <svg class="w-full h-full text-[#007CBA]" viewBox="0 0 100 20" preserveAspectRatio="none"><path d="M0 20 L 100 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                   </div>
                 </div>
               </div>
               
               <div class="bg-white p-5 border border-[#e0e0e0] rounded-[2px] transition-colors">
                 <div>
                   <h3 class="text-[13px] font-medium text-[#757575] mb-2">Customer Base</h3>
                   <div class="text-3xl font-sans font-bold text-[#1e1e1e] mb-4">{{ stats.total_customers || 0 }}</div>
                   <div class="w-full h-8 flex items-end">
                      <svg class="w-full h-full text-[#1e1e1e]" viewBox="0 0 100 20" preserveAspectRatio="none"><path d="M0 20 L 20 15 L 40 18 L 60 12 L 80 16 L 100 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                   </div>
                 </div>
               </div>
             </div>

             <!-- Main Content Area -->
             <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Large Chart Placeholder -->
                <div class="lg:col-span-2 bg-white p-6 border border-[#e0e0e0] rounded-[2px]">
                   <div class="flex justify-between items-center mb-8">
                     <h3 class="text-[16px] font-sans font-semibold text-[#1e1e1e]">Revenue vs. Target</h3>
                     <div class="flex gap-4">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#007CBA]"></span><span class="text-[11px] font-medium text-[#1e1e1e]">Actual</span></div>
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#e0e0e0]"></span><span class="text-[11px] font-medium text-[#757575]">Target</span></div>
                     </div>
                   </div>
                   <div class="h-64 flex items-end gap-2">
                     <div v-for="i in 12" :key="i" class="flex-1 bg-[#e0e0e0] hover:bg-[#007CBA] transition-colors relative group" :style="{ height: `${Math.random() * 80 + 20}%` }">
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-[#1e1e1e] text-white text-[10px] px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap rounded-[2px]">IDR {{ Math.floor(Math.random() * 10) + 1 }}M</div>
                     </div>
                   </div>
                </div>

                <!-- Right Sidebar (Alerts/Info) -->
                <div class="space-y-4">
                   <!-- Alerts -->
                   <div class="bg-white border border-[#e0e0e0] rounded-[2px] p-5">
                      <div class="flex justify-between items-center mb-4">
                         <h4 class="text-[13px] font-semibold text-[#1e1e1e]">Stock Alerts</h4>
                         <span class="text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                      </div>
                      <div class="space-y-4">
                         <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-[#f0f0f1] flex-shrink-0 border border-[#e0e0e0]">
                               <img v-if="recentProducts[0]" :src="recentProducts[0].image" class="w-full h-full object-cover">
                            </div>
                            <div>
                               <p class="text-[13px] font-medium text-[#1e1e1e] leading-tight">Elite Runner V1</p>
                               <p class="text-[11px] text-red-500 mt-0.5">2 Units Remaining</p>
                            </div>
                         </div>
                         <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-[#f0f0f1] flex-shrink-0 border border-[#e0e0e0]">
                               <img v-if="recentProducts[1]" :src="recentProducts[1].image" class="w-full h-full object-cover">
                            </div>
                            <div>
                               <p class="text-[13px] font-medium text-[#1e1e1e] leading-tight">Minimalist Chrono</p>
                               <p class="text-[11px] text-red-500 mt-0.5">0 Units (Restock Req.)</p>
                            </div>
                         </div>
                      </div>
                   </div>

                   <!-- Recent Activity -->
                   <div class="bg-white border border-[#e0e0e0] rounded-[2px] p-5">
                      <h4 class="text-[13px] font-semibold text-[#1e1e1e] mb-4">Recent Activity</h4>
                      <div class="space-y-4">
                         <div class="border-l-2 border-[#007CBA] pl-3">
                            <p class="text-[13px] text-[#1e1e1e] font-medium">New order #1089</p>
                            <p class="text-[11px] text-[#757575] mt-0.5">10 minutes ago</p>
                         </div>
                         <div class="border-l-2 border-[#e0e0e0] pl-3">
                            <p class="text-[13px] text-[#1e1e1e]">Product price updated</p>
                            <p class="text-[11px] text-[#757575] mt-0.5">1 hour ago</p>
                         </div>
                         <div class="border-l-2 border-[#e0e0e0] pl-3">
                            <p class="text-[13px] text-[#1e1e1e]">System backup complete</p>
                            <p class="text-[11px] text-[#757575] mt-0.5">3 hours ago</p>
                         </div>
                      </div>
                   </div>
                </div>
             </div>
          </div>
          <div v-else class="text-center py-20">
             <h2 class="text-lg font-sans text-[#1e1e1e] mb-2">Module Not Ready</h2>
             <p class="text-[13px] text-[#757575]">The selected module is currently under development.</p>
          </div>
        </div>
      </div>
    </main>

    <!-- Cashier Authorization Modal -->
    <div v-if="showApprovalModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
       <div class="bg-white max-w-sm w-full p-6 shadow-sm relative overflow-hidden rounded-[2px]">
          <h3 class="font-sans text-lg font-bold text-[#1e1e1e] mb-2">Otorisasi Diperlukan</h3>
          <p class="text-[#757575] text-[13px] mb-6 leading-relaxed">Pesanan ini telah dikunci. Anda membutuhkan PIN atau Password dari Shop Manager untuk mengubah status.</p>
          
          <input type="password" v-model="approvalPassword" placeholder="Masukkan PIN/Password" class="w-full bg-white border border-[#e0e0e0] text-[#1e1e1e] px-3 py-2 text-[13px] focus:border-[#007CBA] focus:outline-none transition-colors mb-2 rounded-[2px]">
          <p v-if="approvalError" class="text-red-500 text-[11px] font-medium mb-4">{{ approvalError }}</p>
          
          <div class="flex gap-3 mt-4">
             <button @click="showApprovalModal = false" class="flex-1 bg-white border border-[#1e1e1e] text-[#1e1e1e] text-[13px] font-medium py-1.5 hover:bg-[#f0f0f1] transition-colors rounded-[2px]">Batal</button>
             <button @click="verifyApproval" class="flex-1 bg-[#007CBA] text-white text-[13px] font-medium py-1.5 hover:bg-[#006ba1] transition-colors rounded-[2px]">Verifikasi</button>
          </div>
       </div>
    </div>

    <!-- Edit Order Status Modal -->
    <div v-if="showEditOrderModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
       <div class="bg-white max-w-sm w-full p-6 shadow-sm rounded-[2px]">
          <h3 class="font-sans text-lg font-bold text-[#1e1e1e] mb-4">Ubah Status Pesanan</h3>
          <p class="text-[13px] text-[#757575] mb-2">Pesanan: <strong class="text-[#1e1e1e]">{{ editingOrder?.id }}</strong> - {{ editingOrder?.customer }}</p>
          
          <select v-model="editOrderStatus" class="w-full bg-white border border-[#e0e0e0] text-[#1e1e1e] px-3 py-2 text-[13px] focus:border-[#007CBA] focus:outline-none transition-colors mb-6 rounded-[2px]">
             <option value="Pending Payment">Pending Payment</option>
             <option value="Processing">Processing</option>
             <option value="Completed">Completed</option>
             <option value="Cancelled">Cancelled</option>
          </select>
          
          <div class="flex gap-3">
             <button @click="showEditOrderModal = false" class="flex-1 bg-white border border-[#1e1e1e] text-[#1e1e1e] text-[13px] font-medium py-1.5 hover:bg-[#f0f0f1] transition-colors rounded-[2px]">Batal</button>
             <button @click="saveOrderStatus" class="flex-1 bg-[#007CBA] text-white text-[13px] font-medium py-1.5 hover:bg-[#006ba1] transition-colors rounded-[2px]">Simpan</button>
          </div>
       </div>
    </div>

  </div>
</template>
"""

content = re.sub(r'<template>.*</template>', new_template, content, flags=re.DOTALL)

with open('src/App.vue', 'w') as f:
    f.write(content)

print("Template replaced again to be a true Dashboard.")
