import re

def fix_file(path):
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Fix image src
    content = re.sub(r'src="assets/', r'src="{{ asset(\'assets/', content)
    content = re.sub(r'(src="\{\{ asset\(\'assets/[^\"]+)(")', r'\1\') }}"', content)
    
    # Fix Blade @
    # content = content.replace('@', '@@') -> no wait, in product-detail we might have legitimate @, but for js we can just ignore for now or do it safely.
    # Actually, in product detail, the script doesn't use @, except maybe email. So let's skip @ escaping for product-detail for now to avoid breaking existing syntax if any.

    with open(path, 'w', encoding='utf-8') as out:
        out.write(content)

fix_file('resources/views/product-detail.blade.php')
