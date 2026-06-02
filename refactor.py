import os, re

with open('resources/views/components/layouts/app.blade.php', 'r', encoding='utf-8') as f:
    html = f.read()

# Extract header
# <header ... </header>
header_match = re.search(r'<header.*?</header>', html, re.DOTALL)
if header_match:
    os.makedirs('resources/views/components/global', exist_ok=True)
    with open('resources/views/components/global/navbar.blade.php', 'w', encoding='utf-8') as f:
        f.write(header_match.group(0))
    html = html.replace(header_match.group(0), '<x-global.navbar />')

# Extract footer
# <footer ... </footer>
footer_match = re.search(r'<footer.*?</footer>', html, re.DOTALL)
if footer_match:
    with open('resources/views/components/global/footer.blade.php', 'w', encoding='utf-8') as f:
        f.write(footer_match.group(0))
    html = html.replace(footer_match.group(0), '<x-global.footer />')

with open('resources/views/components/layouts/app.blade.php', 'w', encoding='utf-8') as f:
    f.write(html)
print("Refactor complete")
