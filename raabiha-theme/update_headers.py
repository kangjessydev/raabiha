import re
import os

def update_manager(filepath, tab_id, entity_name):
    with open(filepath, 'r') as f:
        content = f.read()

    # Move buttons to header
    header_pattern = r'(<div class="flex items-center justify-between mb-6" v-if="activeTab === \'' + tab_id + r'\'">\s*<div>\s*<h2 class="font-sans text-\[28px\] md:text-\[32px\] font-bold text-\[#1e1e1e\] mb-2 leading-tight">.*?</p>\s*</div>\s*)</div>\s*<!-- ── View: List .*? ── -->\s*<div v-if="activeTab === \'' + tab_id + r'\'" class="raabiha-card p-6">\s*<div class="flex items-center justify-between mb-6">\s*<h3 class="text-\[14px\] font-bold text-\[#1e1e1e\]">.*?</h3>\s*(<div class="flex items-center gap-2">\s*<button.*?</div>)\s*</div>'
    
    match = re.search(header_pattern, content, re.DOTALL)
    if match:
        header_part = match.group(1)
        buttons_part = match.group(2)
        
        # Replace the old layout with the new layout
        new_header = header_part + buttons_part + '\n    </div>\n\n    <!-- ── View: List ' + entity_name + ' ── -->\n    <div v-if="activeTab === \'' + tab_id + '\'" class="raabiha-card overflow-hidden">\n      <!-- Card Tab Header -->\n      <div class="px-5 pt-5 border-b border-[#e0e0e0] flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white">\n        <div class="flex gap-6 text-[13px] font-medium">\n           <button class="text-[#007CBA] border-b-2 border-[#007CBA] pb-4 px-1">Semua <span class="text-[#757575] font-normal ml-1"></span></button>\n        </div>\n        <div class="relative w-full sm:w-64 mb-4 sm:mb-0 flex items-center bg-white border border-[#e0e0e0] rounded-lg px-3 py-1.5 focus-within:border-[#007CBA] focus-within:ring-1 focus-within:ring-[#007CBA] transition-all">\n          <svg class="w-4 h-4 text-[#757575] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>\n          <input type="text" placeholder="Cari..." class="bg-transparent border-none outline-none text-[13px] w-full placeholder-[#757575] text-[#1e1e1e] font-sans">\n        </div>\n      </div>'
        
        content = content[:match.start()] + new_header + content[match.end():]
        with open(filepath, 'w') as f:
            f.write(content)
        print(f"Updated {filepath} header")

update_manager('src/components/ProductManager.vue', 'products', 'Products')
update_manager('src/components/BlogManager.vue', 'blog_posts', 'Posts')
update_manager('src/components/OrderManager.vue', 'orders', 'Orders')

