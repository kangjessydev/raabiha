import re
import glob

replacements = {
    r'(?i)#FAF7F0': '#f0f0f1',
    r'(?i)#222523': '#1e1e1e',
    r'(?i)#e5e5e5': '#e0e0e0',
    r'(?i)#F2EFE8': '#f0f0f1',
    r'(?i)#737373': '#757575',
    r'(?i)#a3a3a3': '#949494',
    r'(?i)#0B4E26': '#3858e9',
    r'(?i)#083B1D': '#135e96',
    r'(?i)#d4d4d4': '#cccccc'
}

for filepath in glob.glob('src/components/*.vue'):
    with open(filepath, 'r') as f:
        content = f.read()
    
    for pattern, replacement in replacements.items():
        content = re.sub(pattern, replacement, content)
        
    with open(filepath, 'w') as f:
        f.write(content)

print("Component colors updated successfully.")
