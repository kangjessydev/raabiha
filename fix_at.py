import glob, re
for f in glob.glob('resources/views/**/*.blade.php', recursive=True):
    with open(f, 'r') as file:
        content = file.read()
    content = re.sub(r'(?<!@)@(?!@)', '@@', content)
    with open(f, 'w') as file:
        file.write(content)
print("Fixed @ symbols")
