import re
import os

def extract_blog_detail():
    with open('blog-detail.html', 'r', encoding='utf-8') as f:
        content = f.read()

    main_match = re.search(r'(<main.*?</main>)', content, re.DOTALL)
    
    if main_match:
        # replace asset paths
        main_html = main_match.group(1)
        # Fix asset paths
        main_html = re.sub(r'src="assets/', r'src="{{ asset(\'assets/', main_html)
        main_html = re.sub(r'(src="{{ asset\(\'assets/[^\"]+)(")', r'\1\') }}"', main_html)
        
        # We need to escape @@ for Blade
        main_html = main_html.replace('@', '@@')
        
        blade_content = f"""<x-layouts.app>
    <x-slot:header>
        <x-global.mobile-subnav title="Detail Blog" backUrl="/blog" />
    </x-slot:header>

    <div class="page-slide-in">
        {main_html}
    </div>
</x-layouts.app>
"""
        with open('resources/views/blog-detail.blade.php', 'w', encoding='utf-8') as out:
            out.write(blade_content)

extract_blog_detail()
