import re

# 1. Update tailwind.config.js font
with open('tailwind.config.js', 'r') as f:
    config = f.read()

config = re.sub(
    r"sans: \['\"Hanken Grotesk\"', 'system-ui', 'sans-serif'\],",
    "sans: ['\"Poppins\"', 'sans-serif'],",
    config
)

with open('tailwind.config.js', 'w') as f:
    f.write(config)

# 2. Update App.vue
with open('src/App.vue', 'r') as f:
    app = f.read()

# Make the menu toggle button visible on desktop too
app = app.replace(
    '<button @click="isDesktopMenuCollapsed = false, isMobileMenuOpen = true" class="md:hidden text-[#1e1e1e]">',
    '<button @click="isDesktopMenuCollapsed = !isDesktopMenuCollapsed; isMobileMenuOpen = !isMobileMenuOpen" class="text-[#1e1e1e] hover:bg-[#f0f0f1] p-1.5 rounded-lg transition-colors">'
)

# Fix accordion logic
# Add openGroups state
script_addition = """
const activeView = ref(props.initialView || 'overview')
const isMobileMenuOpen = ref(false)
const isDesktopMenuCollapsed = ref(false)
const showUserMenu = ref(false)
const openGroups = ref([]) // NEW

function toggleGroup(groupTitle) {
  if (openGroups.value.includes(groupTitle)) {
    openGroups.value = openGroups.value.filter(g => g !== groupTitle)
  } else {
    openGroups.value.push(groupTitle)
  }
}
function isGroupActive(group) {
  // Always open if one of its items is active, otherwise check openGroups
  if (group.items.some(item => item.id === activeView.value)) return true
  return openGroups.value.includes(group.title)
}
"""
app = re.sub(
    r"const activeView = ref.*?function isGroupActive\(group\) \{.*?\}",
    script_addition.strip(),
    app,
    flags=re.DOTALL
)

# Change the group button @click from setView to toggleGroup
app = app.replace(
    '<button @click="setView(group.items[0].id)" \n                        class="w-full flex items-center justify-between gap-3 px-3 py-2 text-[13px] rounded-[2px] transition-colors"',
    '<button @click="toggleGroup(group.title)" \n                        class="w-full flex items-center justify-between gap-3 px-3 py-2 text-[13px] rounded-lg transition-colors"'
)

app = app.replace(
    '<button @click="setView(group.items[0].id)" \n                        class="w-full flex items-center justify-between gap-3 px-3 py-2 text-[13px] rounded-lg transition-colors"',
    '<button @click="toggleGroup(group.title)" \n                        class="w-full flex items-center justify-between gap-3 px-3 py-2 text-[13px] rounded-lg transition-colors"'
)

# Also ensure group single item buttons are rounded-lg
app = app.replace('rounded-[2px]', 'rounded-lg')
app = app.replace('rounded-[4px]', 'rounded-xl')


with open('src/App.vue', 'w') as f:
    f.write(app)

print("Fixed font, sidebar toggle, and accordion logic.")
