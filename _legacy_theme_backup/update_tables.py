import os

files_to_update = [
    "src/components/PageManager.vue",
    "src/components/ProductManager.vue",
    "src/components/BlogManager.vue",
    "src/components/OrderManager.vue"
]

for filepath in files_to_update:
    if not os.path.exists(filepath):
        continue
    with open(filepath, 'r') as f:
        content = f.read()

    # Add padding to table headers
    content = content.replace('<th class="py-3 font-semibold', '<th class="py-3 pl-5 font-semibold', 1) # First TH gets pl-5
    content = content.replace('<th class="py-3 text-right font-semibold', '<th class="py-3 pr-5 text-right font-semibold') # Last TH gets pr-5

    # Add padding to table body cells (first and last)
    content = content.replace('<td class="py-3 text-[13px] font-medium', '<td class="py-3 pl-5 text-[13px] font-medium')
    content = content.replace('<td class="py-3 text-right">', '<td class="py-3 pr-5 text-right">')

    # Change tr hover class
    content = content.replace('hover:bg-white/50 transition-colors', 'hover:bg-[#f9f9fa] transition-colors')

    with open(filepath, 'w') as f:
        f.write(content)
        print("Updated " + filepath)
