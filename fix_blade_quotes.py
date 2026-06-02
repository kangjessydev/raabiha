with open('resources/views/components/layouts/app.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace(r'\"{{', '"{{')
content = content.replace(r'}}\"', '}}"')
content = content.replace(r"\'", "'")

with open('resources/views/components/layouts/app.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
