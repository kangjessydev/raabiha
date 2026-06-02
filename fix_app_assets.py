import re

file_path = 'resources/views/components/layouts/app.blade.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Fix href="wp-..." and src="wp-..."
content = re.sub(r'href="(wp-[^"]+)"', r'href="{{ asset(\'\1\') }}"', content)
content = re.sub(r'src="(wp-[^"]+)"', r'src="{{ asset(\'\1\') }}"', content)
content = re.sub(r"href='(wp-[^']+)'", r"href=\"{{ asset('\1') }}\"", content)
content = re.sub(r"src='(wp-[^']+)'", r"src=\"{{ asset('\1') }}\"", content)

# Fix href="assets/..." and src="assets/..."
content = re.sub(r'href="(assets/[^"]+)"', r'href="{{ asset(\'\1\') }}"', content)
content = re.sub(r'src="(assets/[^"]+)"', r'src="{{ asset(\'\1\') }}"', content)
content = re.sub(r"href='(assets/[^']+)'", r"href=\"{{ asset('\1') }}\"", content)
content = re.sub(r"src='(assets/[^']+)'", r"src=\"{{ asset('\1') }}\"", content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated app.blade.php assets.")
