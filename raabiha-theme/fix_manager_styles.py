import os
import re

directory = "src/components"

for filename in os.listdir(directory):
    if filename.endswith("Manager.vue"):
        filepath = os.path.join(directory, filename)
        with open(filepath, 'r') as f:
            content = f.read()

        # Fix h2 headers
        content = re.sub(r'class="text-xl font-bold text-\[#1e1e1e\]"', 'class="font-sans text-[28px] md:text-[32px] font-bold text-[#1e1e1e] mb-2 leading-tight"', content)
        
        # Fix paragraph descriptions
        content = re.sub(r'class="text-xs text-\[#757575\] mt-1"', 'class="text-[13px] text-[#757575]"', content)
        
        # Fix card titles (h3)
        content = re.sub(r'class="text-sm font-bold text-\[#1e1e1e\]"', 'class="text-[14px] font-bold text-[#1e1e1e]"', content)
        
        # Fix button overrides
        content = content.replace("!py-1.5 !px-3 text-xs flex items-center gap-1.5", "flex items-center gap-1.5")
        
        # Fix table classes
        content = content.replace('class="w-full text-left text-xs border-collapse"', 'class="w-full text-left text-[13px] border-collapse"')
        content = content.replace('class="py-2.5 font-medium', 'class="py-3 font-semibold text-[#757575] uppercase tracking-wider text-[11px]')
        content = content.replace('class="py-2.5 text-right font-medium"', 'class="py-3 text-right font-semibold text-[#757575] uppercase tracking-wider text-[11px]"')

        # Fix table rows
        content = content.replace('class="py-3 font-medium text-[#1e1e1e] pr-4"', 'class="py-3 text-[13px] font-medium text-[#1e1e1e] pr-4"')
        
        # Fix status badges
        content = content.replace('px-2 py-0.5 rounded text-[10px]', 'px-2.5 py-1 rounded-xl text-[11px]')
        
        # Fix empty states
        content = content.replace('class="text-[#757575] text-xs"', 'class="text-[#757575] text-[13px]"')
        
        with open(filepath, 'w') as f:
            f.write(content)
        print(f"Updated {filename}")
