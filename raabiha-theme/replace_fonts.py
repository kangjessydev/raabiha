import re
import glob

for filepath in glob.glob('src/**/*.vue', recursive=True):
    with open(filepath, 'r') as f:
        content = f.read()
    
    # Remove font-serif class
    content = re.sub(r'\bfont-serif\b', 'font-sans', content)
    
    with open(filepath, 'w') as f:
        f.write(content)

print("Fonts updated successfully.")
