import re

with open('src/App.vue', 'r') as f:
    content = f.read()

# Remove the fancy hover background animation
content = re.sub(
    r'<div class="absolute inset-0 bg-\[#f0f0f1\] scale-x-0 group-hover:scale-x-100 origin-left transition-transform duration-500 ease-out z-0"></div>\s*',
    '',
    content
)

# Remove the z-10 class which was used for the fancy animation
content = re.sub(r'<div class="relative z-10">', '<div>', content)

# Change the "New Listing" and "Perbarui" colors to WP Blue #007CBA instead of #3858e9 or #135e96
content = re.sub(r'bg-\[#135e96\]', 'bg-[#007CBA]', content)
content = re.sub(r'hover:bg-\[#0f4b78\]', 'hover:bg-[#006ba1]', content)
content = re.sub(r'text-blue-600', 'text-[#007CBA]', content)
content = re.sub(r'border-blue-600', 'border-[#007CBA]', content)
content = re.sub(r'ring-blue-600', 'ring-[#007CBA]', content)
content = re.sub(r'bg-\[#3858e9\]', 'bg-[#007CBA]', content)
content = re.sub(r'text-\[#3858E9\]', 'text-[#007CBA]', content)
content = re.sub(r'text-\[#3858e9\]', 'text-[#007CBA]', content)
content = re.sub(r'border-\[#3858e9\]', 'border-[#007CBA]', content)
content = re.sub(r'ring-\[#3858e9\]', 'ring-[#007CBA]', content)

# Clean up any leftover shadows
content = re.sub(r'shadow-sm', '', content)
content = re.sub(r'shadow-lg', '', content)
content = re.sub(r'shadow-2xl', '', content)
content = re.sub(r'shadow', '', content)

with open('src/App.vue', 'w') as f:
    f.write(content)

print("Overview cards and buttons updated to strict Gutenberg rules.")
