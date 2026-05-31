import sys

def replace_in_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Color mapping
    # Ivory/Cream: #FAF7F0 replaces #f7f7f5 and #f5f5f3 and #ebebeb
    content = content.replace('#f7f7f5', '#FAF7F0')
    content = content.replace('#f5f5f3', '#F2EFE8') # slightly darker cream for cards
    content = content.replace('#ebebeb', '#E5E1D8') # slightly darker for headers

    # Charcoal: #222523 replaces #1a1a1a
    content = content.replace('#1a1a1a', '#222523')
    
    # Emerald: #0B4E26 replaces #064e3b
    content = content.replace('#064e3b', '#0B4E26')
    content = content.replace('#043326', '#083B1D') # hover state

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

replace_in_file('raabiha-theme/src/App.vue')
replace_in_file('raabiha-theme/src/components/StoreSettingsManager.vue')
print("Color updates applied successfully.")
