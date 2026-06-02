import re

def fix_links(html):
    html = re.sub(r'href="index\.html"', 'href="{{ url(\'/\') }}"', html)
    html = re.sub(r'href="shop\.html"', 'href="{{ url(\'/shop\') }}"', html)
    html = re.sub(r'href="about\.html"', 'href="{{ url(\'/about\') }}"', html)
    html = re.sub(r'href="contact\.html"', 'href="{{ url(\'/contact\') }}"', html)
    html = re.sub(r'href="blog\.html"', 'href="{{ url(\'/blog\') }}"', html)
    html = re.sub(r'href="gallery\.html"', 'href="{{ url(\'/gallery\') }}"', html)
    html = re.sub(r'href="cart\.html"', 'href="{{ url(\'/cart\') }}"', html)
    html = re.sub(r'href="product-detail\.html\?product=([^"]*)"', 'href="{{ url(\'/product/\\1\') }}"', html)
    # Fix asset paths
    html = re.sub(r'src="assets/', 'src="{{ asset(\'assets/', html)
    html = re.sub(r'src="\{\{ asset\(\'assets/([^"]+)"', 'src="{{ asset(\'assets/\\1\') }}"', html)
    return html

# Slice index.html
with open('index.html', 'r', encoding='utf-8') as f:
    lines = f.readlines()

# 1-522 is top
layout_top = "".join(lines[0:522])
# 522-753 is main content
home_content = "".join(lines[522:754])
# 754-end is bottom
layout_bottom = "".join(lines[754:])

layout_html = layout_top + "\n    {{ $slot }}\n\n" + layout_bottom
layout_html = fix_links(layout_html)
home_content = fix_links(home_content)

with open('resources/views/components/layouts/app.blade.php', 'w', encoding='utf-8') as f:
    f.write(layout_html)

with open('resources/views/home.blade.php', 'w', encoding='utf-8') as f:
    f.write('<x-layouts.app>\n' + home_content + '</x-layouts.app>\n')

# Slice about.html
try:
    with open('about.html', 'r', encoding='utf-8') as f:
        about_text = f.read()
    # Find <main> ... </main>
    match = re.search(r'<main.*?</main>', about_text, re.DOTALL)
    if match:
        about_content = fix_links(match.group(0))
        with open('resources/views/about.blade.php', 'w', encoding='utf-8') as f:
            f.write('<x-layouts.app>\n' + about_content + '\n</x-layouts.app>\n')
except Exception as e:
    print("Error about.html:", e)

# Slice contact.html
try:
    with open('contact.html', 'r', encoding='utf-8') as f:
        contact_text = f.read()
    match = re.search(r'<main.*?</main>', contact_text, re.DOTALL)
    if match:
        contact_content = fix_links(match.group(0))
        with open('resources/views/contact.blade.php', 'w', encoding='utf-8') as f:
            f.write('<x-layouts.app>\n' + contact_content + '\n</x-layouts.app>\n')
except Exception as e:
    print("Error contact.html:", e)

# Slice shop.html
try:
    with open('shop.html', 'r', encoding='utf-8') as f:
        shop_text = f.read()
    match = re.search(r'<main.*?</main>', shop_text, re.DOTALL)
    if match:
        shop_content = fix_links(match.group(0))
        with open('resources/views/shop.blade.php', 'w', encoding='utf-8') as f:
            f.write('<x-layouts.app>\n' + shop_content + '\n</x-layouts.app>\n')
except Exception as e:
    print("Error shop.html:", e)

# Slice blog.html
try:
    with open('blog.html', 'r', encoding='utf-8') as f:
        blog_text = f.read()
    match = re.search(r'<main.*?</main>', blog_text, re.DOTALL)
    if match:
        blog_content = fix_links(match.group(0))
        with open('resources/views/blog.blade.php', 'w', encoding='utf-8') as f:
            f.write('<x-layouts.app>\n' + blog_content + '\n</x-layouts.app>\n')
except Exception as e:
    print("Error blog.html:", e)

# Slice gallery.html
try:
    with open('gallery.html', 'r', encoding='utf-8') as f:
        gallery_text = f.read()
    match = re.search(r'<main.*?</main>', gallery_text, re.DOTALL)
    if match:
        gallery_content = fix_links(match.group(0))
        with open('resources/views/gallery.blade.php', 'w', encoding='utf-8') as f:
            f.write('<x-layouts.app>\n' + gallery_content + '\n</x-layouts.app>\n')
except Exception as e:
    print("Error gallery.html:", e)

print("Slicing complete!")
