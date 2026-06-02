import re

with open('src/App.vue', 'r') as f:
    content = f.read()

# Replace the sidebar classes
old_aside_class = "isDesktopMenuCollapsed ? 'md:w-0 md:overflow-hidden md:border-none' : 'w-[260px]'"
new_aside_class = "isDesktopMenuCollapsed ? 'md:w-[64px] overflow-hidden' : 'w-[260px]'"
content = content.replace(old_aside_class, new_aside_class)

# Header Logo
old_header = """      <div class="flex items-center px-4 h-[60px] border-b border-[#e0e0e0] flex-shrink-0">
         <div class="w-8 h-8 bg-[#1e1e1e] text-white flex items-center justify-center rounded-[2px] font-bold text-sm mr-3">R</div>
         <h1 class="text-[14px] font-semibold text-[#1e1e1e]">Raabiha Store</h1>
         <button @click="isMobileMenuOpen = false" class="md:hidden ml-auto text-[#757575]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
         </button>
      </div>"""
new_header = """      <div class="flex items-center px-4 h-[60px] border-b border-[#e0e0e0] flex-shrink-0" :class="isDesktopMenuCollapsed ? 'justify-center px-0' : ''">
         <div class="w-8 h-8 bg-[#1e1e1e] text-white flex items-center justify-center rounded-[2px] font-bold text-sm flex-shrink-0" :class="!isDesktopMenuCollapsed ? 'mr-3' : ''">R</div>
         <h1 v-if="!isDesktopMenuCollapsed" class="text-[14px] font-semibold text-[#1e1e1e] whitespace-nowrap">Raabiha Store</h1>
         <button v-if="!isDesktopMenuCollapsed" @click="isMobileMenuOpen = false" class="md:hidden ml-auto text-[#757575]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
         </button>
      </div>"""
content = content.replace(old_header, new_header)

# Section Headers (MANAJEMEN, dll)
old_section = """            <div v-if="section.label" class="px-3 mb-2">
               <h3 class="text-[11px] font-semibold text-[#757575] uppercase tracking-wider">{{ section.label }}</h3>
            </div>"""
new_section = """            <div v-if="section.label && !isDesktopMenuCollapsed" class="px-3 mb-2">
               <h3 class="text-[11px] font-semibold text-[#757575] uppercase tracking-wider whitespace-nowrap">{{ section.label }}</h3>
            </div>
            <div v-else-if="section.label && isDesktopMenuCollapsed" class="w-full flex justify-center mb-2 mt-4">
               <div class="w-4 h-[1px] bg-[#e0e0e0]"></div>
            </div>"""
content = content.replace(old_section, new_section)

# Single item button
old_single_item = """                  <div v-if="group.items.length === 1">
                     <button @click="setView(group.items[0].id)" 
                        class="w-full flex items-center gap-3 px-3 py-2 text-[13px] rounded-lg transition-colors"
                        :class="activeView === group.items[0].id ? 'bg-[#007CBA] text-white' : 'text-[#1e1e1e] hover:bg-[#f0f0f1]'"
                     >
                        <svg v-if="group.icon === 'chart'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ group.items[0].label }}
                     </button>
                  </div>"""

new_single_item = """                  <div v-if="group.items.length === 1">
                     <button @click="setView(group.items[0].id)" 
                        class="w-full flex items-center gap-3 py-2 text-[13px] rounded-lg transition-colors group/item relative"
                        :class="[activeView === group.items[0].id ? 'bg-[#007CBA] text-white' : 'text-[#1e1e1e] hover:bg-[#f0f0f1]', isDesktopMenuCollapsed ? 'justify-center px-0' : 'px-3']"
                        :title="isDesktopMenuCollapsed ? group.items[0].label : ''"
                     >
                        <svg v-if="group.icon === 'chart'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <svg v-else class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span v-if="!isDesktopMenuCollapsed" class="whitespace-nowrap">{{ group.items[0].label }}</span>
                     </button>
                  </div>"""
content = content.replace(old_single_item, new_single_item)

# Multi item group
old_multi = """                  <div v-else-if="group.items.length > 1" class="group/nav">
                     <button @click="toggleGroup(group.title)" 
                        class="w-full flex items-center justify-between gap-3 px-3 py-2 text-[13px] rounded-lg transition-colors"
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
                     <div v-show="isGroupActive(group)" class="mt-1 ml-9 pl-3 border-l border-[#e0e0e0] space-y-1 py-1">"""

new_multi = """                  <div v-else-if="group.items.length > 1" class="group/nav relative">
                     <button @click="isDesktopMenuCollapsed ? (isDesktopMenuCollapsed = false, toggleGroup(group.title)) : toggleGroup(group.title)" 
                        class="w-full flex items-center py-2 text-[13px] rounded-lg transition-colors group/item relative"
                        :class="[isGroupActive(group) ? 'text-[#007CBA] font-medium' : 'text-[#1e1e1e] hover:bg-[#f0f0f1]', isDesktopMenuCollapsed ? 'justify-center px-0' : 'justify-between px-3']"
                        :title="isDesktopMenuCollapsed ? group.title : ''"
                     >
                        <div class="flex items-center gap-3">
                           <svg v-if="group.icon === 'products'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                           <svg v-else-if="group.icon === 'woo'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                           <svg v-else-if="group.icon === 'posts'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                           <svg v-else class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                           <span v-if="!isDesktopMenuCollapsed" class="whitespace-nowrap">{{ group.title }}</span>
                        </div>
                        <svg v-if="!isDesktopMenuCollapsed" class="w-4 h-4 text-[#757575] transition-transform duration-200" :class="isGroupActive(group) ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/></svg>
                     </button>
                     
                     <!-- Dropdown Sub-items -->
                     <div v-show="isGroupActive(group) && !isDesktopMenuCollapsed" class="mt-1 ml-9 pl-3 border-l border-[#e0e0e0] space-y-1 py-1">"""
content = content.replace(old_multi, new_multi)

# Empty Group
old_empty = """                  <div v-else>
                     <button class="w-full flex items-center justify-between gap-3 px-3 py-2 text-[13px] rounded-lg text-[#757575] opacity-50 cursor-not-allowed">
                        <div class="flex items-center gap-3">
                           <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                           {{ group.title }}
                        </div>
                        <span class="text-[10px] bg-[#e0e0e0] px-1.5 py-0.5 rounded">Segera</span>
                     </button>
                  </div>"""

new_empty = """                  <div v-else>
                     <button class="w-full flex items-center gap-3 py-2 text-[13px] rounded-lg text-[#757575] opacity-50 cursor-not-allowed group/item relative"
                        :class="isDesktopMenuCollapsed ? 'justify-center px-0' : 'justify-between px-3'"
                        :title="isDesktopMenuCollapsed ? group.title + ' (Segera)' : ''"
                     >
                        <div class="flex items-center gap-3">
                           <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                           <span v-if="!isDesktopMenuCollapsed" class="whitespace-nowrap">{{ group.title }}</span>
                        </div>
                        <span v-if="!isDesktopMenuCollapsed" class="text-[10px] bg-[#e0e0e0] px-1.5 py-0.5 rounded whitespace-nowrap">Segera</span>
                     </button>
                  </div>"""
content = content.replace(old_empty, new_empty)

# Bottom collapse button
old_bottom = """      <!-- Bottom Setting/Collapse -->
      <div class="border-t border-[#e0e0e0] p-4 flex items-center justify-between">
         <button class="flex items-center gap-2 text-[#757575] hover:text-[#1e1e1e] text-[13px] transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Pengaturan
         </button>
         <button @click="isDesktopMenuCollapsed = true" class="hidden md:block text-[#757575] hover:text-[#1e1e1e]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
         </button>
      </div>"""

new_bottom = """      <!-- Bottom Setting/Collapse -->
      <div class="border-t border-[#e0e0e0] p-4 flex items-center" :class="isDesktopMenuCollapsed ? 'justify-center px-0' : 'justify-between'">
         <button class="flex items-center gap-2 text-[#757575] hover:text-[#1e1e1e] text-[13px] transition-colors group/item" :title="isDesktopMenuCollapsed ? 'Pengaturan' : ''">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span v-if="!isDesktopMenuCollapsed">Pengaturan</span>
         </button>
         <button v-if="!isDesktopMenuCollapsed" @click="isDesktopMenuCollapsed = true" class="hidden md:block text-[#757575] hover:text-[#1e1e1e]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
         </button>
      </div>"""
content = content.replace(old_bottom, new_bottom)

with open('src/App.vue', 'w') as f:
    f.write(content)
