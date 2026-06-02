<script setup>
/**
 * App.vue — Raabiha Dashboard Root Component
 * Main shell: sidebar navigation + content area router
 */
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import ProductManager from './components/ProductManager.vue'
import BlogManager from './components/BlogManager.vue'
import PageManager from './components/PageManager.vue'
import WebSettingsManager from './components/WebSettingsManager.vue'
import StoreSettingsManager from './components/StoreSettingsManager.vue'
import OrderManager from './components/OrderManager.vue'
import UserManager from './components/UserManager.vue'
import UISetting from './components/UISetting.vue'
import ToastNotification from './components/ToastNotification.vue'
import MediaManager from './components/MediaManager.vue'
import ConfirmModal from './components/ConfirmModal.vue'


const config       = ref(window.RaabihaConfig || {})
const activeView   = ref('overview')
const user         = ref(config.value.user || {})
const stats        = ref({ total_products: 0, orders_today: 0, revenue_month: 0, formatted_rev: 'Rp 0' })
const recentProducts = ref([])
const loadingStats = ref(false)
const showUserMenu = ref(false)
const isMobileMenuOpen = ref(false)
const isDesktopMenuCollapsed = ref(false)
const openGroups = ref([])

function toggleMenu() {
  if (window.innerWidth < 768) {
    isMobileMenuOpen.value = !isMobileMenuOpen.value
    if (isMobileMenuOpen.value) {
      isDesktopMenuCollapsed.value = false // ensure text is visible when open
    }
  } else {
    isDesktopMenuCollapsed.value = !isDesktopMenuCollapsed.value
  }
}

function toggleGroup(groupTitle) {
  if (openGroups.value.includes(groupTitle)) {
    openGroups.value = openGroups.value.filter(g => g !== groupTitle)
  } else {
    openGroups.value.push(groupTitle)
  }
}

// Dynamic navigation structure based on user roles
const menuSections = computed(() => {
  const role = user.value.role || 'customer'
  
  const dasarGroup = {
    id: 'dasar',
    title: 'Dasar',
    icon: 'chart',
    items: [
      { id: 'overview', label: 'Overview', icon: 'chart' },
    ]
  }
  
  const katalogGroup = {
    id: 'katalog',
    title: 'Katalog Produk',
    icon: 'products',
    items: [
      { id: 'all_products', label: 'Semua Produk', icon: 'list' },
      { id: 'products', label: 'Tambah Produk', icon: 'plus-box' },
      { id: 'product_categories', label: 'Kategori Produk', icon: 'folder' },
      { id: 'product_tags', label: 'Tag Produk', icon: 'tag' },
      { id: 'product_attributes', label: 'Atribut Produk', icon: 'adjustments' },
      { id: 'product_reviews', label: 'Ulasan Produk', icon: 'chat-bubble' },
    ]
  }
  
  const penjualanGroup = {
    id: 'penjualan',
    title: 'Penjualan (Woo)',
    icon: 'woo',
    items: [
      { id: 'orders', label: 'Semua Pesanan', icon: 'shopping-cart' },
      { id: 'customers', label: 'Data Pelanggan', icon: 'users' },
      { id: 'coupons', label: 'Kupon Diskon', icon: 'ticket' },
      { id: 'reports', label: 'Laporan Penjualan', icon: 'chart-bar' },
      { id: 'store_settings', label: 'Pengaturan Toko', icon: 'cog' },
    ]
  }

  // Cashier has limited billing/orders access
  if (role === 'raabiha_cashier') {
    penjualanGroup.items = penjualanGroup.items.filter(item => item.id !== 'store_settings')
  }
  
  const blogGroup = {
    id: 'blog',
    title: 'Blog & Artikel',
    icon: 'posts',
    items: [
      { id: 'blog_posts', label: 'Semua Artikel', icon: 'document-text' },
      { id: 'add_blog_post', label: 'Tulis Artikel Baru', icon: 'pencil-square' },
      { id: 'blog_categories', label: 'Kategori Blog', icon: 'folder-open' },
      { id: 'blog_tags', label: 'Tag Artikel', icon: 'tag' },
    ]
  }
  
  const sistemGroup = {
    id: 'sistem',
    title: 'System',
    icon: 'settings',
    items: [
      { id: 'header_setting', label: 'Header', icon: 'desktop-computer' },
      { id: 'footer_setting', label: 'Footer', icon: 'device-mobile' },
      { id: 'security', label: 'Pengaturan Masking', icon: 'shield-check' },
    ]
  }
  
  const kontenGroup = {
    id: 'konten',
    title: 'Laman',
    icon: 'pages',
    items: [
      { id: 'pages_list', label: 'Halaman Statis', icon: 'document' },
      { id: 'web_settings', label: 'Tampilan Web', icon: 'globe' },
    ]
  }

  const mediaGroup = {
    id: 'media',
    title: 'Pustaka Media',
    icon: 'media',
    items: [
      { id: 'media_library', label: 'Pustaka Media', icon: 'photograph' }
    ]
  }


  const penggunaGroup = {
    id: 'pengguna',
    title: 'Pengguna',
    icon: 'users',
    items: [
      { id: 'users_list', label: 'Semua Pengguna', icon: 'users' },
      { id: 'add_user', label: 'Tambah Baru', icon: 'user-plus' },
    ]
  }

  const sections = []

  if (role === 'administrator' || role === 'shop_manager' || role === 'raabiha_owner' || role === 'raabiha_co_admin') {
    sections.push({ label: '', groups: [dasarGroup] })
    sections.push({
      label: 'MANAJEMEN',
      groups: [
        katalogGroup,
        penjualanGroup,
        { id: 'setting_manajemen', title: 'Setting', icon: 'settings', items: [] }
      ]
    })
    
    let manajemenKontenGroups = [blogGroup]
    if (role === 'administrator' || role === 'raabiha_owner' || role === 'raabiha_co_admin') {
      manajemenKontenGroups.push(kontenGroup)
      manajemenKontenGroups.push(mediaGroup)
    }
    sections.push({ label: 'MANAJEMEN KONTEN', groups: manajemenKontenGroups })
    
    if (role === 'administrator' || role === 'raabiha_owner' || role === 'raabiha_co_admin') {
      sections.push({ label: 'SISTEM', groups: [penggunaGroup, sistemGroup] })
    }
  } else if (role === 'raabiha_cashier') {
    sections.push({ label: '', groups: [dasarGroup] })
    sections.push({ label: 'MANAJEMEN', groups: [penjualanGroup] })
    sections.push({ label: 'SISTEM', groups: [penggunaGroup] })
  } else if (role === 'raabiha_blogger') {
    sections.push({ label: '', groups: [dasarGroup] })
    sections.push({ label: 'MANAJEMEN KONTEN', groups: [blogGroup] })
  }

  return sections
})

function isGroupActive(group) {
  if (group.items.some(item => item.id === activeView.value)) return true
  return openGroups.value.includes(group.title)
}

// Cashier order lock & authorization flow
const mockOrders = ref([
  { id: '#1024', customer: 'Dewi Rahmawati', date: '22 Mei 2026', total: 'Rp 275.000', status: 'Completed', locked: true },
  { id: '#1023', customer: 'Budi Santoso', date: '21 Mei 2026', total: 'Rp 150.000', status: 'Processing', locked: true },
  { id: '#1022', customer: 'Siti Aminah', date: '20 Mei 2026', total: 'Rp 420.000', status: 'Pending Payment', locked: false },
  { id: '#1021', customer: 'Ahmad Fauzi', date: '19 Mei 2026', total: 'Rp 95.000', status: 'Completed', locked: true },
])

const showApprovalModal = ref(false)
const selectedOrderToEdit = ref(null)
const approvalPassword = ref('')
const approvalError = ref('')
const editingOrder = ref(null)
const showEditOrderModal = ref(false)
const editOrderStatus = ref('')

function handleEditOrder(order) {
  const role = user.value.role || 'customer'
  
  selectedOrderToEdit.value = order
  
  // Kasir cannot edit locked orders without Shop Manager's PIN/Password
  if (role === 'raabiha_cashier' && order.locked) {
    showApprovalModal.value = true
    approvalPassword.value = ''
    approvalError.value = ''
  } else {
    // Admins and Shop Managers bypass verification
    openEditOrder(order)
  }
}

function verifyApproval() {
  if (approvalPassword.value === 'manager_pass' || approvalPassword.value === 'raabiha_pass') {
    showApprovalModal.value = false
    openEditOrder(selectedOrderToEdit.value)
  } else {
    approvalError.value = 'Password salah! Otorisasi ditolak.'
  }
}

function openEditOrder(order) {
  editingOrder.value = order
  editOrderStatus.value = order.status
  showEditOrderModal.value = true
}

function saveOrderStatus() {
  if (editingOrder.value) {
    editingOrder.value.status = editOrderStatus.value
    // Auto-lock again if completed/processing
    editingOrder.value.locked = ['Completed', 'Processing'].includes(editOrderStatus.value)
  }
  showEditOrderModal.value = false
  editingOrder.value = null
}

function setView(id) { 
  activeView.value = id
  isMobileMenuOpen.value = false // close sidebar on mobile after click
  if (id === 'overview' || id === 'all_products') {
    fetchStats()
  }
}

async function fetchStats() {
  loadingStats.value = true
  try {
    const params = new URLSearchParams()
    params.append('action', 'raabiha_get_stats')
    params.append('nonce', config.value.nonce || '')

    const response = await axios.get(config.value.ajax_url, { params })
    if (response.data && response.data.success) {
      stats.value = response.data.data.stats
      recentProducts.value = response.data.data.recent_products
    }
  } catch (error) {
    console.error('Gagal mengambil data statistik:', error)
  } finally {
    loadingStats.value = false
  }
}

// Auto refresh stats on product save
function onProductSaved() {
  fetchStats()
}

onMounted(() => {
  // Signal to page-dashboard.php that Vue has mounted — hides loader
  window.dispatchEvent(new CustomEvent('raabiha:app-mounted'))
  // Load stats initially
  fetchStats()
})
</script>

<template>
  <div class="flex h-screen bg-[#f0f0f1] text-[#1e1e1e] font-sans overflow-hidden relative">
    
    <!-- Mobile Overlay -->
    <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/50 z-40 md:hidden transition-opacity"></div>

    <!-- Sidebar (Gutenberg Dashboard Style) -->
    <aside :class="[
      'flex-col bg-white border-r border-[#e0e0e0] flex-shrink-0 z-50 fixed md:relative h-full transition-all duration-300',
      isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
      isDesktopMenuCollapsed ? 'md:w-[64px]' : 'w-[260px]'
    ]" class="flex">
      
      <!-- Sidebar Header (Logo) -->
      <div class="flex items-center h-[60px] border-b border-[#e0e0e0] flex-shrink-0 overflow-hidden" :class="isDesktopMenuCollapsed ? 'justify-center px-0' : 'px-4'">
         <div class="w-8 h-8 bg-[#1e1e1e] text-white flex items-center justify-center rounded-xl font-bold text-sm flex-shrink-0" :class="isDesktopMenuCollapsed ? '' : 'mr-3'">R</div>
         <h1 v-if="!isDesktopMenuCollapsed" class="text-[14px] font-semibold text-[#1e1e1e] whitespace-nowrap">Raabiha Store</h1>
         <button v-if="!isDesktopMenuCollapsed" @click="isMobileMenuOpen = false" class="md:hidden ml-auto text-[#757575]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
         </button>
      </div>
      
      <!-- Sidebar Navigation -->
      <nav class="flex-1 py-4 overflow-y-auto px-3 overflow-x-hidden">
         <div v-for="(section, idx) in menuSections" :key="idx" class="mb-6">
            <div v-if="section.label && !isDesktopMenuCollapsed" class="px-3 mb-2">
               <h3 class="text-[11px] font-semibold text-[#757575] uppercase tracking-wider">{{ section.label }}</h3>
            </div>
            <div v-else-if="section.label && isDesktopMenuCollapsed" class="w-full flex justify-center mb-2 mt-4">
               <div class="w-4 h-[1px] bg-[#e0e0e0]"></div>
            </div>
            
            <div class="space-y-1">
               <div v-for="group in section.groups" :key="group.id" class="relative">
                  
                  <!-- Single Item Group -->
                  <div v-if="group.items.length === 1">
                     <button @click="setView(group.items[0].id)" 
                        class="w-full flex items-center py-2 text-[13px] rounded-xl transition-colors relative group/item"
                        :class="[activeView === group.items[0].id ? 'bg-[#007CBA] text-white' : 'text-[#1e1e1e] hover:bg-[#f0f0f1]', isDesktopMenuCollapsed ? 'justify-center px-0' : 'justify-start px-3 gap-3']"
                        :title="isDesktopMenuCollapsed ? group.items[0].label : ''"
                     >
                        <svg v-if="group.icon === 'chart'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <svg v-else-if="group.icon === 'media'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <svg v-else class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span v-if="!isDesktopMenuCollapsed" class="whitespace-nowrap">{{ group.items[0].label }}</span>
                     </button>
                  </div>
                  
                  <!-- Multi-Item Group -->
                  <div v-else-if="group.items.length > 1" class="group/nav relative">
                     <button @click="isDesktopMenuCollapsed ? (isDesktopMenuCollapsed = false, toggleGroup(group.title)) : toggleGroup(group.title)" 
                        class="w-full flex items-center py-2 text-[13px] rounded-xl transition-colors group/item relative"
                        :class="[isGroupActive(group) ? 'text-[#007CBA] font-medium' : 'text-[#1e1e1e] hover:bg-[#f0f0f1]', isDesktopMenuCollapsed ? 'justify-center px-0' : 'justify-between px-3']"
                        :title="isDesktopMenuCollapsed ? group.title : ''"
                     >
                        <div class="flex items-center gap-3">
                           <svg v-if="group.icon === 'products'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                           <svg v-else-if="group.icon === 'woo'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                           <svg v-else-if="group.icon === 'posts'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                           <svg v-else-if="group.icon === 'pages'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                           <svg v-else class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                           <span v-if="!isDesktopMenuCollapsed" class="whitespace-nowrap">{{ group.title }}</span>
                        </div>
                        <svg v-if="!isDesktopMenuCollapsed" class="w-4 h-4 text-[#757575] transition-transform duration-200 flex-shrink-0" :class="isGroupActive(group) ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/></svg>
                     </button>
                     
                     <!-- Dropdown Sub-items -->
                     <div v-show="isGroupActive(group) && !isDesktopMenuCollapsed" class="mt-1 ml-9 pl-3 border-l border-[#e0e0e0] space-y-1 py-1">
                        <button v-for="item in group.items" :key="item.id" @click="setView(item.id)"
                           class="w-full text-left px-3 py-1.5 text-[13px] rounded-xl transition-colors"
                           :class="activeView === item.id ? 'bg-[#f0f0f1] text-[#007CBA] font-medium' : 'text-[#1e1e1e] hover:text-[#007CBA] hover:bg-[#f0f0f1]'"
                        >
                           {{ item.label }}
                        </button>
                     </div>
                  </div>
                  
                  <!-- Empty Group -->
                  <div v-else>
                     <button class="w-full flex items-center py-2 text-[13px] rounded-xl text-[#757575] opacity-50 cursor-not-allowed group/item relative"
                        :class="isDesktopMenuCollapsed ? 'justify-center px-0' : 'justify-between px-3'"
                        :title="isDesktopMenuCollapsed ? group.title + ' (Segera)' : ''"
                     >
                        <div class="flex items-center gap-3">
                           <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                           <span v-if="!isDesktopMenuCollapsed" class="whitespace-nowrap">{{ group.title }}</span>
                        </div>
                        <span v-if="!isDesktopMenuCollapsed" class="text-[10px] bg-[#e0e0e0] px-1.5 py-0.5 rounded whitespace-nowrap">Segera</span>
                     </button>
                  </div>

               </div>
            </div>
         </div>
      </nav>
      
      <!-- Bottom Setting/Collapse -->
      <div class="border-t border-[#e0e0e0] p-4 flex items-center" :class="isDesktopMenuCollapsed ? 'justify-center px-0' : 'justify-between'">
         <button class="flex items-center gap-2 text-[#757575] hover:text-[#1e1e1e] text-[13px] transition-colors group/item" :title="isDesktopMenuCollapsed ? 'Pengaturan' : ''">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span v-if="!isDesktopMenuCollapsed">Pengaturan</span>
         </button>
         <button v-if="!isDesktopMenuCollapsed" @click="isDesktopMenuCollapsed = true" class="hidden md:block text-[#757575] hover:text-[#1e1e1e]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
         </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 bg-[#f0f0f1]">
      
      <!-- Top Header Bar -->
      <header class="flex justify-between items-center px-4 md:px-6 h-[60px] flex-shrink-0 border-b border-[#e0e0e0] bg-white z-10 sticky top-0">
         
         <div class="flex items-center gap-4">
            <button @click="toggleMenu" class="text-[#1e1e1e] hover:bg-[#f0f0f1] p-1.5 rounded-lg transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            
            <!-- Breadcrumbs -->
            <div class="hidden sm:flex items-center text-[13px] text-[#1e1e1e]">
               <span class="text-[#757575] hover:text-[#1e1e1e] cursor-pointer">Dashboard</span>
               <span class="mx-2 text-[#e0e0e0]">/</span>
               <span class="font-medium capitalize">{{ activeView.replace(/_/g, ' ') }}</span>
            </div>
         </div>
         
         <!-- Actions -->
         <div class="flex items-center gap-2">
            
            <div class="relative w-64 hidden md:flex items-center bg-white border border-[#e0e0e0] rounded-xl px-3 py-1.5 focus-within:border-[#007CBA] focus-within:ring-1 focus-within:ring-[#007CBA] transition-all">
              <svg class="w-4 h-4 text-[#757575] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              <input type="text" placeholder="Search..." class="bg-transparent border-none outline-none text-[13px] w-full placeholder-[#757575] text-[#1e1e1e] font-sans">
            </div>

            <button class="w-9 h-9 flex items-center justify-center text-[#757575] hover:text-[#1e1e1e] hover:bg-[#f0f0f1] rounded-xl transition-colors relative ml-2">
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
               <span class="absolute top-2 right-2 w-2 h-2 bg-[#007CBA] rounded-full border border-white"></span>
            </button>

            <!-- User Profile Dropdown -->
            <div class="relative ml-2">
               <button @click="showUserMenu = !showUserMenu" class="flex items-center gap-2 hover:bg-[#f0f0f1] px-2 py-1 rounded-xl transition-colors">
                  <div class="w-7 h-7 rounded-full bg-[#007CBA] text-white flex items-center justify-center text-[11px] font-bold">
                     JV
                  </div>
               </button>
               
               <div v-if="showUserMenu" class="absolute right-0 top-full mt-2 w-48 bg-white border border-[#e0e0e0] shadow-sm rounded-xl overflow-hidden z-50">
                  <div class="py-1">
                     <div class="px-4 py-2 border-b border-[#e0e0e0] mb-1 bg-[#f0f0f1]">
                        <p class="text-[13px] font-medium text-[#1e1e1e]">{{ user.name || 'Julianne V.' }}</p>
                        <p class="text-[11px] text-[#757575]">{{ user.role || 'Administrator' }}</p>
                     </div>
                     <a href="#" class="block px-4 py-1.5 text-[13px] text-[#1e1e1e] hover:bg-[#007CBA] hover:text-white transition-colors">Edit Profile</a>
                     <a href="#" class="block px-4 py-1.5 text-[13px] text-[#1e1e1e] hover:bg-[#007CBA] hover:text-white transition-colors">Pengaturan</a>
                     <div class="border-t border-[#e0e0e0] my-1"></div>
                     <a href="/wp-login.php?action=logout" class="block px-4 py-1.5 text-[13px] text-red-600 hover:bg-[#f0f0f1] transition-colors">Keluar</a>
                  </div>
               </div>
            </div>
         </div>
      </header>

      <!-- Router Content -->
      <div class="flex-1 overflow-auto p-4 md:p-8 bg-[#f0f0f1]">
        <!-- Dashboard components will inherit standard WP content styles -->
        <div class="max-w-6xl mx-auto">
          <ProductManager v-if="activeView === 'all_products' || activeView === 'products' || activeView === 'product_categories' || activeView === 'product_tags' || activeView === 'product_attributes'" :config="config" :initialView="activeView" />
          <BlogManager v-else-if="activeView === 'blog_posts' || activeView === 'add_blog_post' || activeView === 'blog_categories' || activeView === 'blog_tags'" :config="config" :initialView="activeView" />
          <PageManager v-else-if="activeView === 'pages_list'" :config="config" :initialView="activeView" />
          <MediaManager v-else-if="activeView === 'media_library'" :config="config" :initialView="activeView" />
          <WebSettingsManager v-else-if="activeView === 'web_settings'" :config="config" :initialView="activeView" />
          <StoreSettingsManager v-else-if="activeView === 'store_settings'" :config="config" :initialView="activeView" />
          <OrderManager v-else-if="activeView === 'orders'" :config="config" :initialView="activeView" :orders="mockOrders" @edit-order="handleEditOrder" />
          <UserManager v-else-if="activeView === 'users_list' || activeView === 'add_user'" :config="config" :initialView="activeView" @set-view="v => activeView = v" />
          <UISetting v-else-if="activeView === 'header_setting' || activeView === 'footer_setting'" :config="config" :initialView="activeView" @set-view="v => activeView = v" />
          
          <div v-else-if="activeView === 'overview'" class="space-y-6">
             <div class="mb-8">
               <h2 class="font-sans text-[28px] md:text-[32px] font-bold text-[#1e1e1e] mb-2 leading-tight">Dashboard</h2>
               <p class="text-[13px] text-[#757575]">Real-time performance metrics for Raabiha Luxury Goods.</p>
             </div>

             <!-- Overview Cards -->
             <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
               <div class="bg-white p-5 border border-[#e0e0e0] rounded-xl transition-colors">
                 <div>
                   <h3 class="text-[13px] font-medium text-[#757575] mb-2">Total Sales</h3>
                   <div class="text-3xl font-sans font-bold text-[#1e1e1e] mb-4">{{ stats.formatted_rev || 'Rp 0' }}</div>
                   <div class="w-full h-8 flex items-end">
                      <svg class="w-full h-full text-[#007CBA]" viewBox="0 0 100 20" preserveAspectRatio="none"><path d="M0 20 Q 20 10 40 15 T 80 5 T 100 15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                   </div>
                 </div>
               </div>
               
               <div class="bg-white p-5 border border-[#e0e0e0] rounded-xl transition-colors">
                 <div>
                   <h3 class="text-[13px] font-medium text-[#757575] mb-2">Active Products</h3>
                   <div class="text-3xl font-sans font-bold text-[#1e1e1e] mb-4">{{ stats.total_products || 0 }}</div>
                   <div class="w-full h-8 flex items-end border-b border-[#e0e0e0]"></div>
                 </div>
               </div>
               
               <div class="bg-white p-5 border border-[#e0e0e0] rounded-xl transition-colors">
                 <div>
                   <h3 class="text-[13px] font-medium text-[#757575] mb-2">Orders Today</h3>
                   <div class="text-3xl font-sans font-bold text-[#1e1e1e] mb-4">{{ stats.orders_today || 0 }}</div>
                   <div class="w-full h-8 flex items-end">
                      <svg class="w-full h-full text-[#007CBA]" viewBox="0 0 100 20" preserveAspectRatio="none"><path d="M0 20 L 100 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                   </div>
                 </div>
               </div>
               
               <div class="bg-white p-5 border border-[#e0e0e0] rounded-xl transition-colors">
                 <div>
                   <h3 class="text-[13px] font-medium text-[#757575] mb-2">Customer Base</h3>
                   <div class="text-3xl font-sans font-bold text-[#1e1e1e] mb-4">{{ stats.total_customers || 0 }}</div>
                   <div class="w-full h-8 flex items-end">
                      <svg class="w-full h-full text-[#1e1e1e]" viewBox="0 0 100 20" preserveAspectRatio="none"><path d="M0 20 L 20 15 L 40 18 L 60 12 L 80 16 L 100 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                   </div>
                 </div>
               </div>
             </div>

             <!-- Main Content Area -->
             <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Large Chart Placeholder -->
                <div class="lg:col-span-2 bg-white p-6 border border-[#e0e0e0] rounded-xl">
                   <div class="flex justify-between items-center mb-8">
                     <h3 class="text-[16px] font-sans font-semibold text-[#1e1e1e]">Revenue vs. Target</h3>
                     <div class="flex gap-4">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#007CBA]"></span><span class="text-[11px] font-medium text-[#1e1e1e]">Actual</span></div>
                        <div class="flex items-center gap-2"><span class="w-3 h-3 bg-[#e0e0e0]"></span><span class="text-[11px] font-medium text-[#757575]">Target</span></div>
                     </div>
                   </div>
                   <div class="h-64 flex items-end gap-2">
                     <div v-for="i in 12" :key="i" class="flex-1 bg-[#e0e0e0] hover:bg-[#007CBA] transition-colors relative group" :style="{ height: `${Math.random() * 80 + 20}%` }">
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-[#1e1e1e] text-white text-[10px] px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap rounded-xl">IDR {{ Math.floor(Math.random() * 10) + 1 }}M</div>
                     </div>
                   </div>
                </div>

                <!-- Right Sidebar (Alerts/Info) -->
                <div class="space-y-4">
                   <!-- Alerts -->
                   <div class="bg-white border border-[#e0e0e0] rounded-xl p-5">
                      <div class="flex justify-between items-center mb-4">
                         <h4 class="text-[13px] font-semibold text-[#1e1e1e]">Stock Alerts</h4>
                         <span class="text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                      </div>
                      <div class="space-y-4">
                         <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-[#f0f0f1] flex-shrink-0 border border-[#e0e0e0]">
                               <img v-if="recentProducts[0]" :src="recentProducts[0].image" class="w-full h-full object-cover">
                            </div>
                            <div>
                               <p class="text-[13px] font-medium text-[#1e1e1e] leading-tight">Elite Runner V1</p>
                               <p class="text-[11px] text-red-500 mt-0.5">2 Units Remaining</p>
                            </div>
                         </div>
                         <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-[#f0f0f1] flex-shrink-0 border border-[#e0e0e0]">
                               <img v-if="recentProducts[1]" :src="recentProducts[1].image" class="w-full h-full object-cover">
                            </div>
                            <div>
                               <p class="text-[13px] font-medium text-[#1e1e1e] leading-tight">Minimalist Chrono</p>
                               <p class="text-[11px] text-red-500 mt-0.5">0 Units (Restock Req.)</p>
                            </div>
                         </div>
                      </div>
                   </div>

                   <!-- Recent Activity -->
                   <div class="bg-white border border-[#e0e0e0] rounded-xl p-5">
                      <h4 class="text-[13px] font-semibold text-[#1e1e1e] mb-4">Recent Activity</h4>
                      <div class="space-y-4">
                         <div class="border-l-2 border-[#007CBA] pl-3">
                            <p class="text-[13px] text-[#1e1e1e] font-medium">New order #1089</p>
                            <p class="text-[11px] text-[#757575] mt-0.5">10 minutes ago</p>
                         </div>
                         <div class="border-l-2 border-[#e0e0e0] pl-3">
                            <p class="text-[13px] text-[#1e1e1e]">Product price updated</p>
                            <p class="text-[11px] text-[#757575] mt-0.5">1 hour ago</p>
                         </div>
                         <div class="border-l-2 border-[#e0e0e0] pl-3">
                            <p class="text-[13px] text-[#1e1e1e]">System backup complete</p>
                            <p class="text-[11px] text-[#757575] mt-0.5">3 hours ago</p>
                         </div>
                      </div>
                   </div>
                </div>
             </div>
          </div>
          <div v-else class="text-center py-20">
             <h2 class="text-lg font-sans text-[#1e1e1e] mb-2">Module Not Ready</h2>
             <p class="text-[13px] text-[#757575]">The selected module is currently under development.</p>
          </div>
        </div>
      </div>
    </main>

    <!-- Cashier Authorization Modal -->
    <div v-if="showApprovalModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
       <div class="bg-white max-w-sm w-full p-6 shadow-sm relative overflow-hidden rounded-xl">
          <h3 class="font-sans text-lg font-bold text-[#1e1e1e] mb-2">Otorisasi Diperlukan</h3>
          <p class="text-[#757575] text-[13px] mb-6 leading-relaxed">Pesanan ini telah dikunci. Anda membutuhkan PIN atau Password dari Shop Manager untuk mengubah status.</p>
          
          <input type="password" v-model="approvalPassword" placeholder="Masukkan PIN/Password" class="w-full bg-white border border-[#e0e0e0] text-[#1e1e1e] px-3 py-2 text-[13px] focus:border-[#007CBA] focus:outline-none transition-colors mb-2 rounded-xl">
          <p v-if="approvalError" class="text-red-500 text-[11px] font-medium mb-4">{{ approvalError }}</p>
          
          <div class="flex gap-3 mt-4">
             <button @click="showApprovalModal = false" class="flex-1 bg-white border border-[#1e1e1e] text-[#1e1e1e] text-[13px] font-medium py-1.5 hover:bg-[#f0f0f1] transition-colors rounded-xl">Batal</button>
             <button @click="verifyApproval" class="flex-1 bg-[#007CBA] text-white text-[13px] font-medium py-1.5 hover:bg-[#006ba1] transition-colors rounded-xl">Verifikasi</button>
          </div>
       </div>
    </div>

    <!-- Edit Order Status Modal -->
    <div v-if="showEditOrderModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
       <div class="bg-white max-w-sm w-full p-6 shadow-sm rounded-xl">
          <h3 class="font-sans text-lg font-bold text-[#1e1e1e] mb-4">Ubah Status Pesanan</h3>
          <p class="text-[13px] text-[#757575] mb-2">Pesanan: <strong class="text-[#1e1e1e]">{{ editingOrder?.id }}</strong> - {{ editingOrder?.customer }}</p>
          
          <select v-model="editOrderStatus" class="w-full bg-white border border-[#e0e0e0] text-[#1e1e1e] px-3 py-2 text-[13px] focus:border-[#007CBA] focus:outline-none transition-colors mb-6 rounded-xl">
             <option value="Pending Payment">Pending Payment</option>
             <option value="Processing">Processing</option>
             <option value="Completed">Completed</option>
             <option value="Cancelled">Cancelled</option>
          </select>
          
          <div class="flex gap-3">
             <button @click="showEditOrderModal = false" class="flex-1 bg-white border border-[#1e1e1e] text-[#1e1e1e] text-[13px] font-medium py-1.5 hover:bg-[#f0f0f1] transition-colors rounded-xl">Batal</button>
             <button @click="saveOrderStatus" class="flex-1 bg-[#007CBA] text-white text-[13px] font-medium py-1.5 hover:bg-[#006ba1] transition-colors rounded-xl">Simpan</button>
          </div>
       </div>
    </div>

    <ToastNotification />
    <ConfirmModal />
  </div>
</template>
