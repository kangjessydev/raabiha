import re

with open('raabiha-theme/src/App.vue', 'r', encoding='utf-8') as f:
    content = f.read()

# Add import
import_stmt = "import WebSettingsManager from './components/WebSettingsManager.vue'\nimport StoreSettingsManager from './components/StoreSettingsManager.vue'"
content = content.replace("import WebSettingsManager from './components/WebSettingsManager.vue'", import_stmt)

# Replace the placeholder in the template
old_placeholder = """<div class="text-[#737373] text-sm">Integrations settings page placeholder... (To be implemented)</div>"""
new_content = """<StoreSettingsManager :config="config" />"""
content = content.replace(old_placeholder, new_content)

with open('raabiha-theme/src/App.vue', 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated App.vue with StoreSettingsManager")
