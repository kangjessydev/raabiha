import re

with open('src/App.vue', 'r') as f:
    app = f.read()

# Make sure toggleGroup is injected
replacement = """const activeView = ref(props.initialView || 'overview')
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
  if (group.items.some(item => item.id === activeView.value)) return true
  return openGroups.value.includes(group.title)
}
"""

app = re.sub(
    r"const activeView = ref\(props\.initialView \|\| 'overview'\)\nconst isMobileMenuOpen = ref\(false\)\nconst isDesktopMenuCollapsed = ref\(false\)\nconst showUserMenu = ref\(false\)\n\nfunction isGroupActive\(group\) {\n  return group\.items\.some\(item => item\.id === activeView\.value\)\n}",
    replacement,
    app
)

# Fix the toggle menu button in the template
app = app.replace(
    '<button @click="isDesktopMenuCollapsed = false, isMobileMenuOpen = true" class="md:hidden text-[#1e1e1e]">',
    '<button @click="isDesktopMenuCollapsed = !isDesktopMenuCollapsed; isMobileMenuOpen = !isMobileMenuOpen" class="text-[#1e1e1e] hover:bg-[#f0f0f1] p-1.5 rounded-lg transition-colors">'
)

app = app.replace(
    '<button @click="setView(group.items[0].id)" \n                        class="w-full flex items-center justify-between gap-3 px-3 py-2 text-[13px] rounded-lg transition-colors"',
    '<button @click="toggleGroup(group.title)" \n                        class="w-full flex items-center justify-between gap-3 px-3 py-2 text-[13px] rounded-lg transition-colors"'
)


with open('src/App.vue', 'w') as f:
    f.write(app)
