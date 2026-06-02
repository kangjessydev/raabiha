<script setup>
import { useToast } from '../composables/useToast'
import { useConfirm } from '../composables/useConfirm'

const { showToast } = useToast()
const { triggerConfirm } = useConfirm()

import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  config: { type: Object, required: true },
  initialView: { type: String, default: 'pages_list' }
})
const emit = defineEmits(['set-view'])

const activeTab = ref(props.initialView)

// Watch for external view changes from App.vue sidebar
watch(() => props.initialView, (newVal) => {
  activeTab.value = newVal
  if (newVal === 'pages_list') {
    fetchPages()
    editingId.value = null
  }
})

// HTTP Client configured for WP REST API
const api = axios.create({
  baseURL: props.config.rest_url + 'wp/v2/',
  headers: {
    'X-WP-Nonce': props.config.rest_nonce
  }
})

// ── State: Pages ──────────────────────────────────────────────
const pages = ref([])
const loadingPages = ref(false)
const editingId = ref(null)

const selectedItems = ref([])
const selectAll = computed({
  get: () => pages.value.length > 0 && selectedItems.value.length === pages.value.length,
  set: (val) => {
    selectedItems.value = val ? pages.value.map(p => p.id) : []
  }
})

async function fetchPages() {
  loadingPages.value = true
  try {
    const res = await api.get('pages?_embed&per_page=50')
    pages.value = res.data
  } catch (err) {
    console.error('Error fetching pages', err)
  } finally {
    loadingPages.value = false
  }
}

function deletePage(id) {
  triggerConfirm(
    'Hapus Halaman',
    'Apakah Anda yakin ingin menghapus halaman ini?',
    async () => {
      try {
        await api.delete(`pages/${id}`)
        fetchPages()
      } catch (err) {
        console.error('Error deleting page', err)
        showToast('error', 'Gagal menghapus halaman.')
      }
    }
  )
}

function editPage(page) {
  editingId.value = page.id
  emit('set-view', 'add_page')
}

function handleIframeLoad(e) {
  // Optional: Add a loader here later
}

// ── Lifecycle ─────────────────────────────────────────────────
onMounted(() => {
  if (activeTab.value === 'pages_list') fetchPages()
})
</script>

<template>
  <div class="h-full flex flex-col">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6" v-if="activeTab === 'pages_list'">
      <div>
        <h2 class="font-sans text-[28px] md:text-[32px] font-bold text-[#1e1e1e] mb-2 leading-tight">Manajemen Halaman</h2>
        <p class="text-[13px] text-[#757575]">Kelola halaman statis seperti Privacy Policy, Terms, atau landing page dengan editor visual.</p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="fetchPages" :disabled="loadingPages" class="btn-secondary flex items-center gap-2">
          <svg class="w-4 h-4" :class="{'animate-spin': loadingPages}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          Sync Data
        </button>
        <a :href="`${config.site_url}/wp-admin/post-new.php?post_type=page&context=raabiha_dashboard`" target="_blank" class="btn-primary flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
          Buat Halaman
        </a>
      </div>
    </div>

    <!-- ── View: List Pages ── -->
    <div v-if="activeTab === 'pages_list'" class="raabiha-card overflow-hidden">
      <!-- Card Tabs -->
      <div class="px-5 pt-4 border-b border-[#f1f1f1] flex gap-6 text-[13px] font-medium bg-white">
         <button class="text-[#007CBA] border-b-2 border-[#007CBA] pb-3 px-1">Semua <span class="text-[#757575] font-normal ml-1">({{ pages.length }})</span></button>
         <button class="text-[#757575] hover:text-[#1e1e1e] pb-3 px-1 transition-colors">Published</button>
         <button class="text-[#757575] hover:text-[#1e1e1e] pb-3 px-1 transition-colors">Draft</button>
      </div>

      <!-- Card Toolbar Top -->
      <div class="px-5 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border-b border-[#f1f1f1]">
        <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto pb-2 sm:pb-0">
           <!-- Bulk Action -->
           <select class="w-auto flex-shrink-0 text-[13px] text-[#1e1e1e] bg-white border border-[#cccccc] rounded px-2.5 py-1.5 focus:border-[#007CBA] outline-none">
             <option>Tindakan Massal</option>
             <option>Edit</option>
             <option>Pindahkan ke Sampah</option>
           </select>
           <button class="flex-shrink-0 border border-[#007CBA] text-[#007CBA] hover:bg-[#007CBA] hover:text-white rounded px-3 py-1.5 text-[13px] font-medium transition-colors">
             Terapkan
           </button>
           
           <div class="w-px h-5 bg-[#e0e0e0] mx-2 hidden sm:block"></div>
           
           <!-- Filters -->
           <select class="w-auto flex-shrink-0 text-[13px] text-[#1e1e1e] bg-white border border-[#cccccc] rounded px-2.5 py-1.5 focus:border-[#007CBA] outline-none">
             <option>Semua tanggal</option>
           </select>
           <button class="flex-shrink-0 border border-[#cccccc] text-[#1e1e1e] hover:bg-[#f0f0f1] rounded px-3 py-1.5 text-[13px] font-medium transition-colors">
             Saring
           </button>
        </div>
        
        <div class="flex items-center gap-4 w-full sm:w-auto">
           <!-- Search -->
           <div class="relative w-full sm:w-48 flex items-center bg-white border border-[#cccccc] rounded px-2.5 py-1.5 focus-within:border-[#007CBA] focus-within:ring-1 focus-within:ring-[#007CBA] transition-all">
             <input type="text" placeholder="Cari Laman" class="bg-transparent border-none outline-none text-[13px] w-full placeholder-[#757575] text-[#1e1e1e] font-sans">
           </div>
           <!-- Item Count -->
           <span class="text-[13px] text-[#757575] font-medium whitespace-nowrap">{{ pages.length }} item</span>
        </div>
      </div>

      <div v-if="loadingPages" class="p-5 space-y-3">
        <div v-for="n in 3" :key="n" class="h-12 bg-[#E5E1D8] animate-pulse rounded-lg"></div>
      </div>
      
      <div v-else-if="pages.length === 0" class="text-center py-10">
        <p class="text-[#757575] text-[13px]">Belum ada halaman yang dibuat.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-[13px]">
          <thead class="bg-[#f9f9fa] border-b border-[#cccccc]">
            <tr class="text-[#1e1e1e]">
              <th class="py-3 pl-5 w-12"><input type="checkbox" v-model="selectAll" class="rounded border-[#8c8f94] text-[#007CBA] focus:ring-[#007CBA] cursor-pointer"></th>
              <th class="py-3 pl-3 font-semibold text-[13px] min-w-[200px]">Judul</th>
              <th class="py-3 font-semibold text-[13px] min-w-[100px]">Penulis</th>
              <th class="py-3 font-semibold text-[13px] min-w-[120px]">Tanggal</th>
              <th class="py-3 pr-5 text-right font-semibold text-[13px] min-w-[150px]">Aksi</th>
            </tr>
          </thead>
          <tbody class="text-[#1e1e1e]">
            <tr v-for="page in pages" :key="page.id" class="border-b border-[#f1f1f1] hover:bg-[#fcfcfc] transition-colors group">
              <td class="py-3 pl-5"><input type="checkbox" v-model="selectedItems" :value="page.id" class="rounded border-[#8c8f94] text-[#007CBA] focus:ring-[#007CBA] cursor-pointer"></td>
              <td class="py-3 pl-3 text-[13px] font-bold text-[#007CBA] pr-4 hover:underline cursor-pointer" v-html="page.title.rendered || '(Tanpa Judul)'"></td>
              <td class="py-3 text-[13px] text-[#007CBA] hover:underline cursor-pointer">admin</td>
              <td class="py-3 text-[13px] text-[#757575]">
                <div class="font-medium text-[#1e1e1e]">{{ page.status === 'publish' ? 'Telah Terbit' : 'Draft' }}</div>
                <div class="text-[11px]">{{ new Date(page.date).toLocaleDateString('id-ID', {year: 'numeric', month: '2-digit', day: '2-digit'}) }}</div>
              </td>
              <td class="py-3 pr-5 text-right">
                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                  <a :href="page.link" target="_blank" class="p-1.5 text-[#757575] hover:text-[#007CBA] hover:bg-blue-50 rounded-lg transition-colors" title="Lihat Halaman">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  </a>
                  <a :href="`${config.site_url}/wp-admin/post.php?post=${page.id}&action=edit&context=raabiha_dashboard`" target="_blank" class="p-1.5 text-[#757575] hover:text-[#1e1e1e] hover:bg-gray-100 rounded-lg transition-colors" title="Edit via Gutenberg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  </a>
                  <button @click="deletePage(page.id)" class="p-1.5 text-[#757575] hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
          <tfoot class="bg-[#f9f9fa] border-t border-[#cccccc]">
            <tr>
              <th class="py-3 pl-5 w-12"><input type="checkbox" v-model="selectAll" class="rounded border-[#8c8f94] text-[#007CBA] focus:ring-[#007CBA] cursor-pointer"></th>
              <th class="py-3 pl-3 font-semibold text-[13px] text-[#1e1e1e] min-w-[200px]">Judul</th>
              <th class="py-3 font-semibold text-[13px] text-[#1e1e1e] min-w-[100px]">Penulis</th>
              <th class="py-3 font-semibold text-[13px] text-[#1e1e1e] min-w-[120px]">Tanggal</th>
              <th class="py-3 pr-5 text-right font-semibold text-[13px] text-[#1e1e1e] min-w-[150px]">Aksi</th>
            </tr>
          </tfoot>
        </table>
      </div>
      
      <!-- Card Toolbar Bottom Outer -->
      <div class="px-5 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-[#f9f9fa] border-t border-[#f1f1f1]">
        <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
           <select class="w-auto flex-shrink-0 text-[13px] text-[#1e1e1e] bg-white border border-[#cccccc] rounded px-2.5 py-1.5 focus:border-[#007CBA] outline-none">
             <option>Tindakan Massal</option>
             <option>Edit</option>
             <option>Pindahkan ke Sampah</option>
           </select>
           <button class="flex-shrink-0 border border-[#007CBA] text-[#007CBA] hover:bg-[#007CBA] hover:text-white rounded px-3 py-1.5 text-[13px] font-medium transition-colors">
             Terapkan
           </button>
        </div>
        <span class="text-[13px] text-[#757575] font-medium">{{ pages.length }} item</span>
      </div>
    </div>

  </div>
</template>
