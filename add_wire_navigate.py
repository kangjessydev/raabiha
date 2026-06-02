import re

def add_wire_navigate(path):
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Add wire:navigate to <a href="{{ url(...) }}"
    content = re.sub(r'(<a\s+href="\{\{\s*url\([^)]+\)\s*\}\}")(?!.*?wire:navigate)', r'\1 wire:navigate', content)
    
    # Add wire:navigate to <a href="/something"
    content = re.sub(r'(<a\s+href="\/[^"]+")(?!.*?wire:navigate)', r'\1 wire:navigate', content)

    with open(path, 'w', encoding='utf-8') as out:
        out.write(content)

files_to_update = [
    'resources/views/components/global/navbar.blade.php',
    'resources/views/components/global/footer.blade.php',
    'resources/views/components/global/mobile-subnav.blade.php',
    'resources/views/components/layouts/app.blade.php', # Sidebar menu
    'resources/views/shop.blade.php', # Product grid
    'resources/views/blog.blade.php', # Blog grid
]

for f in files_to_update:
    add_wire_navigate(f)

print("Added wire:navigate to links!")
