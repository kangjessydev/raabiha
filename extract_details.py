import re
import os

def extract_product_detail():
    with open('product-detail.html', 'r', encoding='utf-8') as f:
        content = f.read()

    main_match = re.search(r'(<main.*?</main>)', content, re.DOTALL)
    script_match = re.search(r'(<script>\s*const products = .*?</script>)', content, re.DOTALL)
    
    if main_match and script_match:
        blade_content = f"""<x-layouts.app>
    <x-slot:header>
        <x-global.mobile-subnav title="Detail Produk" backUrl="/shop" />
    </x-slot:header>

    <div class="page-slide-in">
        {main_match.group(1)}
        
        {script_match.group(1)}
    </div>
</x-layouts.app>
"""
        with open('resources/views/product-detail.blade.php', 'w', encoding='utf-8') as out:
            out.write(blade_content)

def extract_blog_detail():
    with open('blog.html', 'r', encoding='utf-8') as f:
        # We don't have blog-detail.html, but let's see if we have it
        pass

extract_product_detail()
