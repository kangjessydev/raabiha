import os
import re

src_dir = '/home/kangjessy/Documents/projects/testing-ecommerce-wp-custom/raabiha-theme/src/components'

for filename in os.listdir(src_dir):
    if not filename.endswith('.vue'):
        continue
    filepath = os.path.join(src_dir, filename)
    with open(filepath, 'r') as f:
        content = f.read()
    
    if 'alert(' not in content:
        continue
    
    # Replace alert(...) with showToast('error', ...) or showToast('success', ...)
    # If the message contains "berhasil", we make it success. Otherwise error.
    
    def replacer(match):
        msg = match.group(1)
        if 'berhasil' in msg.lower() or 'disimpan' in msg.lower():
            return f"showToast('success', {msg})"
        else:
            return f"showToast('error', {msg})"
            
    new_content = re.sub(r'alert\((.*?)\)', replacer, content)
    
    # Now we need to inject the import
    if 'useToast' not in new_content:
        import_str = "import { useToast } from '../composables/useToast'\n"
        setup_str = "const { showToast } = useToast()\n"
        
        # Inject after <script setup>
        new_content = new_content.replace('<script setup>', '<script setup>\n' + import_str + setup_str)
        
    with open(filepath, 'w') as f:
        f.write(new_content)
    print(f"Updated {filename}")
