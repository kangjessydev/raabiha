import os
import re

components_dir = 'src/components'

for filename in os.listdir(components_dir):
    if filename.endswith('.vue'):
        filepath = os.path.join(components_dir, filename)
        with open(filepath, 'r') as f:
            content = f.read()
        
        # Replace rounded corners
        content = content.replace('rounded-[2px]', 'rounded-lg')
        content = content.replace('rounded-[4px]', 'rounded-xl')
        
        # Replace colors
        colors_to_replace = [
            r'bg-\[#135e96\]', r'bg-\[#3858E9\]', r'bg-\[#3858e9\]'
        ]
        for color in colors_to_replace:
            content = re.sub(color, 'bg-[#007CBA]', content)
            
        content = re.sub(r'hover:bg-\[#0f4b78\]', 'hover:bg-[#006ba1]', content)
        content = re.sub(r'hover:bg-blue-700', 'hover:bg-[#006ba1]', content)
        
        text_colors = [r'text-\[#3858E9\]', r'text-\[#3858e9\]', r'text-blue-600']
        for color in text_colors:
            content = re.sub(color, 'text-[#007CBA]', content)
            
        border_colors = [r'border-\[#3858E9\]', r'border-\[#3858e9\]', r'border-blue-600']
        for color in border_colors:
            content = re.sub(color, 'border-[#007CBA]', content)
            
        ring_colors = [r'ring-\[#3858E9\]', r'ring-\[#3858e9\]', r'ring-blue-600']
        for color in ring_colors:
            content = re.sub(color, 'ring-[#007CBA]', content)

        # Remove shadows
        content = re.sub(r'\bshadow-sm\b', '', content)
        content = re.sub(r'\bshadow-md\b', '', content)
        content = re.sub(r'\bshadow-lg\b', '', content)
        content = re.sub(r'\bshadow-xl\b', '', content)
        content = re.sub(r'\bshadow-2xl\b', '', content)
        content = re.sub(r'\bshadow\b', '', content)
        
        # tracking-widest -> tracking-wider for Poppins
        content = content.replace('tracking-widest', 'tracking-wider')
        
        with open(filepath, 'w') as f:
            f.write(content)
            
print("Design system applied to all components.")
