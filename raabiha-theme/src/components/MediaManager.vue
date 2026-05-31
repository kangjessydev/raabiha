<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import { useToast } from '../composables/useToast'
import { useConfirm } from '../composables/useConfirm'

const { showToast } = useToast()
const { triggerConfirm } = useConfirm()

const props = defineProps({
  config: Object,
  initialView: String
})

const mediaItems = ref([])
const loading = ref(false)
const uploading = ref(false)
const showUploadZone = ref(false)
const isDragging = ref(false)
const searchQuery = ref('')
const page = ref(1)
const hasMore = ref(true)
const fileInput = ref(null)

// Advanced Filters & Tooling
const viewMode = ref('grid') // 'grid' or 'list'
const selectedType = ref('') // '', 'image', 'video', 'file'
const selectedDate = ref('') // '', 'YYYY-MM'
const isBulkMode = ref(false)
const selectedIds = ref([])

// Detail Sidebar / Panel
const selectedMedia = ref(null)
const editingTitle = ref('')
const editingAlt = ref('')
const editingCaption = ref('')
const savingMeta = ref(false)
const deletingMedia = ref(false)


// Dynamic Date Filters (Last 24 months in Indonesian)
const dateFilters = computed(() => {
  const options = []
  const date = new Date()
  const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ]
  for (let i = 0; i < 24; i++) {
    const m = date.getMonth()
    const y = date.getFullYear()
    options.push({
      label: `${months[m]} ${y}`,
      value: `${y}-${String(m + 1).padStart(2, '0')}`
    })
    date.setMonth(date.getMonth() - 1)
  }
  return options
})

async function fetchMedia(append = false) {
  if (loading.value) return
  loading.value = true
  try {
    const params = {
      per_page: 30,
      page: page.value,
      search: searchQuery.value || undefined
    }

    // Media type filtering
    if (selectedType.value === 'image') {
      params.media_type = 'image'
    } else if (selectedType.value === 'file') {
      params.media_type = 'file'
    } else if (selectedType.value === 'video') {
      params.mime_type = 'video'
    }

    // Date range filtering
    if (selectedDate.value) {
      const [year, month] = selectedDate.value.split('-')
      const lastDay = new Date(parseInt(year), parseInt(month), 0).getDate()
      params.after = `${year}-${month}-01T00:00:00`
      params.before = `${year}-${month}-${String(lastDay).padStart(2, '0')}T23:59:59`
    }

    const response = await axios.get(`${props.config.rest_url}wp/v2/media`, {
      params,
      headers: {
        'X-WP-Nonce': props.config.rest_nonce
      }
    })
    
    if (response.data.length < 30) {
      hasMore.value = false
    } else {
      hasMore.value = true
    }
    
    if (append) {
      mediaItems.value = [...mediaItems.value, ...response.data]
    } else {
      mediaItems.value = response.data
    }
  } catch (err) {
    console.error('Error fetching media', err)
    showToast('error', 'Gagal memuat pustaka media.')
  } finally {
    loading.value = false
  }
}

function loadMore() {
  if (!hasMore.value || loading.value) return
  page.value++
  fetchMedia(true)
}

// Watch search query to refetch (with reasonable debounce)
let searchTimeout = null
watch(searchQuery, () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    page.value = 1
    fetchMedia(false)
  }, 400)
})

// Watch filters to refetch
watch([selectedType, selectedDate], () => {
  page.value = 1
  fetchMedia(false)
})

async function uploadFile(file) {
  if (!file) return
  
  uploading.value = true
  const formData = new FormData()
  formData.append('file', file)
  
  try {
    const res = await axios.post(`${props.config.rest_url}wp/v2/media`, formData, {
      headers: {
        'X-WP-Nonce': props.config.rest_nonce
      }
    })
    mediaItems.value.unshift(res.data)
    selectMedia(res.data)
    showToast('success', 'Berkas media berhasil diunggah.')
    showUploadZone.value = false
  } catch (err) {
    const msg = err?.response?.data?.message || 'Gagal mengunggah berkas.'
    showToast('error', msg)
  } finally {
    uploading.value = false
    if (fileInput.value) fileInput.value.value = ''
  }
}

function handleFileInputChange(e) {
  if (e.target.files && e.target.files[0]) {
    uploadFile(e.target.files[0])
  }
}

function handleDrop(e) {
  e.preventDefault()
  isDragging.value = false
  if (e.dataTransfer.files && e.dataTransfer.files[0]) {
    uploadFile(e.dataTransfer.files[0])
  }
}

function selectMedia(media) {
  selectedMedia.value = media
  editingTitle.value = media.title?.rendered || ''
  editingAlt.value = media.alt_text || ''
  editingCaption.value = media.caption?.rendered || ''
}

function handleMediaClick(media) {
  if (isBulkMode.value) {
    if (selectedIds.value.includes(media.id)) {
      selectedIds.value = selectedIds.value.filter(id => id !== media.id)
    } else {
      selectedIds.value.push(media.id)
    }
  } else {
    selectMedia(media)
  }
}

function toggleBulkMode() {
  isBulkMode.value = !isBulkMode.value
  selectedIds.value = []
}

function selectAllVisible() {
  if (selectedIds.value.length === mediaItems.value.length) {
    selectedIds.value = []
  } else {
    selectedIds.value = mediaItems.value.map(m => m.id)
  }
}

function bulkDelete() {
  if (selectedIds.value.length === 0) return
  triggerConfirm(
    'Hapus Massal Media',
    `Hapus secara permanen ${selectedIds.value.length} berkas media yang dipilih? Tindakan ini tidak dapat dibatalkan.`,
    async () => {
      loading.value = true
      const idsToDelete = [...selectedIds.value]
      selectedIds.value = []
      isBulkMode.value = false
      
      let successCount = 0
      let failCount = 0
      
      for (const id of idsToDelete) {
        try {
          await axios.delete(`${props.config.rest_url}wp/v2/media/${id}?force=true`, {
            headers: {
              'X-WP-Nonce': props.config.rest_nonce
            }
          })
          successCount++
        } catch (err) {
          console.error(`Gagal menghapus media ID ${id}:`, err)
          failCount++
        }
      }
      
      if (successCount > 0) {
        showToast('success', `${successCount} berkas media berhasil dihapus secara permanen.`)
      }
      if (failCount > 0) {
        showToast('error', `${failCount} berkas media gagal dihapus.`)
      }
      
      page.value = 1
      loading.value = false // Allow fetchMedia to execute
      fetchMedia(false)
    }
  )
}

async function saveMetadata() {
  if (!selectedMedia.value) return
  savingMeta.value = true
  try {
    const res = await axios.post(
      `${props.config.rest_url}wp/v2/media/${selectedMedia.value.id}`,
      {
        title: editingTitle.value,
        alt_text: editingAlt.value,
        caption: editingCaption.value
      },
      {
        headers: {
          'X-WP-Nonce': props.config.rest_nonce
        }
      }
    )
    
    // Update in list
    const idx = mediaItems.value.findIndex(m => m.id === res.data.id)
    if (idx !== -1) {
      mediaItems.value[idx] = res.data
    }
    selectedMedia.value = res.data
    showToast('success', 'Metadata gambar berhasil diperbarui.')
  } catch (err) {
    showToast('error', 'Gagal memperbarui metadata.')
  } finally {
    savingMeta.value = false
  }
}
function deleteMedia() {
  if (!selectedMedia.value) return
  triggerConfirm(
    'Hapus Media',
    `Hapus berkas "${editingTitle.value || 'ini'}" secara permanen? Tindakan ini tidak dapat dibatalkan.`,
    async () => {
      deletingMedia.value = true
      try {
        await axios.delete(`${props.config.rest_url}wp/v2/media/${selectedMedia.value.id}?force=true`, {
          headers: {
            'X-WP-Nonce': props.config.rest_nonce
          }
        })
        
        mediaItems.value = mediaItems.value.filter(m => m.id !== selectedMedia.value.id)
        selectedMedia.value = null
        showToast('success', 'Gambar berhasil dihapus secara permanen.')
      } catch (err) {
        showToast('error', 'Gagal menghapus gambar.')
      } finally {
        deletingMedia.value = false
      }
    }
  )
}

function copyURL() {
  if (!selectedMedia.value) return
  navigator.clipboard.writeText(selectedMedia.value.source_url)
  showToast('success', 'Tautan gambar berhasil disalin ke clipboard.')
}

const formattedDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
}

onMounted(() => {
  fetchMedia()
})
</script>

<template>
  <div class="h-full flex flex-col">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-sans text-[28px] md:text-[32px] font-bold text-[#1e1e1e] mb-2 leading-tight">Pustaka Media</h2>
        <p class="text-[13px] text-[#757575]">Kelola seluruh berkas media situs Anda. Unggah berkas baru, edit metadata alt/judul, atau hapus berkas secara massal.</p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="showUploadZone = !showUploadZone" class="btn-primary flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          {{ showUploadZone ? 'Tutup Pengunggah' : 'Unggah File Media' }}
        </button>
      </div>
    </div>

    <!-- Drag & Drop Upload Zone -->
    <div v-if="showUploadZone"
         class="mb-6 border-2 border-dashed rounded-2xl bg-white p-8 transition-all cursor-pointer flex flex-col items-center justify-center min-h-[200px]"
         :class="isDragging ? 'border-[#007CBA] bg-[#f0f7ff]' : 'border-[#e0e0e0] hover:border-[#007CBA]'"
         @dragover.prevent="isDragging = true"
         @dragleave.prevent="isDragging = false"
         @drop="handleDrop"
         @click="fileInput.click()">
      <input type="file" ref="fileInput" class="hidden" @change="handleFileInputChange">
      <div v-if="!uploading" class="text-center pointer-events-none">
        <svg class="w-12 h-12 mx-auto mb-3 text-[#757575]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        <p class="text-[14px] font-semibold text-[#1e1e1e] mb-1">Seret & letakkan berkas di sini</p>
        <p class="text-[12px] text-[#757575]">atau klik untuk memilih berkas dari komputer Anda</p>
        <p class="mt-4 text-[11px] text-[#007CBA] bg-[#f0f7ff] px-3 py-1 rounded-full inline-block font-medium">✓ Gambar akan otomatis dikonversi ke WebP & dikompresi 80%</p>
      </div>
      <div v-else class="text-center pointer-events-none">
        <svg class="w-10 h-10 animate-spin text-[#007CBA] mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <p class="text-[13px] text-[#007CBA] font-semibold">Mengunggah & memproses berkas...</p>
      </div>
    </div>

    <!-- Filter & Toolbar -->
    <div class="mb-5 bg-white border border-[#e0e0e0] rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex flex-wrap items-center gap-3">
        <!-- View Toggle Buttons -->
        <div class="flex items-center bg-[#f0f0f1] rounded-xl p-0.5 border border-[#e0e0e0]">
          <button @click="viewMode = 'list'" 
                  class="p-1.5 rounded-lg transition-all"
                  :class="viewMode === 'list' ? 'bg-white text-[#007CBA] shadow-sm' : 'text-[#757575] hover:text-[#1e1e1e]'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>
          <button @click="viewMode = 'grid'" 
                  class="p-1.5 rounded-lg transition-all"
                  :class="viewMode === 'grid' ? 'bg-white text-[#007CBA] shadow-sm' : 'text-[#757575] hover:text-[#1e1e1e]'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
          </button>
        </div>

        <!-- Media Type Filter -->
        <select v-model="selectedType" class="bg-white border border-[#e0e0e0] text-[#1e1e1e] px-3.5 py-1.5 text-[13px] rounded-xl focus:border-[#007CBA] focus:outline-none transition-colors cursor-pointer">
          <option value="">Semua berkas media</option>
          <option value="image">Gambar</option>
          <option value="video">Video</option>
          <option value="file">Dokumen/Lainnya</option>
        </select>

        <!-- Date Filter -->
        <select v-model="selectedDate" class="bg-white border border-[#e0e0e0] text-[#1e1e1e] px-3.5 py-1.5 text-[13px] rounded-xl focus:border-[#007CBA] focus:outline-none transition-colors cursor-pointer">
          <option value="">Semua tanggal</option>
          <option v-for="opt in dateFilters" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>

        <!-- Bulk Selector Toggle -->
        <button @click="toggleBulkMode" 
                class="px-3.5 py-1.5 text-[13px] font-medium rounded-xl border transition-all"
                :class="isBulkMode ? 'bg-[#007CBA] text-white border-[#007CBA]' : 'bg-white border-[#e0e0e0] text-[#1e1e1e] hover:bg-[#f0f0f1]'">
          {{ isBulkMode ? 'Selesai Memilih' : 'Pilih banyak' }}
        </button>

        <!-- Bulk Action Buttons -->
        <div v-if="isBulkMode" class="flex items-center gap-2">
          <button @click="selectAllVisible" class="px-3 py-1.5 text-[13px] font-medium bg-white border border-[#e0e0e0] text-[#1e1e1e] hover:bg-[#f0f0f1] rounded-xl transition-all">
            {{ selectedIds.length === mediaItems.length && mediaItems.length > 0 ? 'Batalkan Pilihan' : 'Pilih Semua' }}
          </button>
          <button @click="bulkDelete" 
                  :disabled="selectedIds.length === 0"
                  class="px-3 py-1.5 text-[13px] font-medium bg-red-600 hover:bg-red-700 text-white rounded-xl transition-all disabled:opacity-50 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Hapus Terpilih ({{ selectedIds.length }})
          </button>
        </div>
      </div>

      <!-- Search bar -->
      <div class="relative w-full md:w-72">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="w-4 h-4 text-[#757575]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </span>
        <input type="text" v-model="searchQuery" placeholder="Cari media..." 
               class="w-full bg-white border border-[#e0e0e0] text-[#1e1e1e] pl-9 pr-4 py-1.5 text-[13px] rounded-xl focus:border-[#007CBA] focus:outline-none transition-colors">
      </div>
    </div>

    <!-- Content Layout -->
    <div class="flex-1 flex flex-col md:flex-row gap-6 min-h-0">
      
      <!-- Left side: Content Container -->
      <div class="flex-1 flex flex-col bg-white border border-[#e0e0e0] rounded-2xl p-5 overflow-hidden">
        
        <div class="flex-1 overflow-y-auto">
          <div v-if="loading && page === 1" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
            <div v-for="n in 12" :key="n" class="aspect-square bg-[#f0f0f1] animate-pulse rounded-xl"></div>
          </div>

          <div v-else-if="mediaItems.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
            <svg class="w-12 h-12 text-[#e0e0e0] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-[13px] text-[#757575]">Tidak ada berkas media ditemukan.</p>
          </div>

          <div v-else class="space-y-6">
            <!-- Grid View -->
            <div v-if="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
              <div v-for="media in mediaItems" :key="media.id"
                   @click="handleMediaClick(media)"
                   class="relative aspect-square bg-[#fafaf9] border border-[#e0e0e0] hover:border-[#007CBA] cursor-pointer rounded-xl overflow-hidden group transition-all"
                   :class="(isBulkMode ? selectedIds.includes(media.id) : (selectedMedia && selectedMedia.id === media.id)) ? 'ring-2 ring-[#007CBA] ring-offset-2' : ''">
                
                <!-- Display image or placeholder for files/video -->
                <img v-if="media.mime_type.startsWith('image/')"
                     :src="media.media_details?.sizes?.thumbnail?.source_url || media.source_url" 
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                     loading="lazy" />
                <div v-else class="w-full h-full flex flex-col items-center justify-center p-3 text-[#757575] bg-[#f5f5f5]">
                  <svg v-if="media.mime_type.startsWith('video/')" class="w-10 h-10 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                  <svg v-else class="w-10 h-10 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                  <span class="text-[10px] text-center truncate w-full" :title="media.title?.rendered">{{ media.title?.rendered }}</span>
                </div>

                <!-- Checkbox overlay when selected -->
                <div v-if="isBulkMode ? selectedIds.includes(media.id) : (selectedMedia && selectedMedia.id === media.id)" 
                     class="absolute inset-0 bg-[#007CBA]/10 flex items-center justify-center">
                  <div class="bg-[#007CBA] text-white rounded-full p-1.5 shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>

            <!-- List View -->
            <div v-else class="w-full overflow-x-auto">
              <table class="w-full border-collapse text-left text-[13px]">
                <thead>
                  <tr class="border-b border-[#e0e0e0] text-[#757575] font-semibold bg-[#fafaf9]">
                    <th v-if="isBulkMode" class="py-3 px-4 w-10">
                      <input type="checkbox" 
                             :checked="selectedIds.length === mediaItems.length && mediaItems.length > 0" 
                             @change="selectAllVisible" 
                             class="rounded border-[#e0e0e0] text-[#007CBA] focus:ring-[#007CBA]" />
                    </th>
                    <th class="py-3 px-4 w-20">Pratinjau</th>
                    <th class="py-3 px-4">Nama Berkas</th>
                    <th class="py-3 px-4 w-36">Tanggal</th>
                    <th class="py-3 px-4 w-32">Dimensi</th>
                    <th class="py-3 px-4 w-28 text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-[#e0e0e0]">
                  <tr v-for="media in mediaItems" :key="media.id" 
                      class="hover:bg-[#fcfcfc] transition-colors cursor-pointer"
                      :class="selectedMedia && selectedMedia.id === media.id ? 'bg-[#f0f7ff]' : ''"
                      @click="handleMediaClick(media)">
                    <td v-if="isBulkMode" class="py-3 px-4" @click.stop>
                      <input type="checkbox" :value="media.id" v-model="selectedIds" class="rounded border-[#e0e0e0] text-[#007CBA] focus:ring-[#007CBA]" />
                    </td>
                    <td class="py-3 px-4">
                      <img v-if="media.mime_type.startsWith('image/')"
                           :src="media.media_details?.sizes?.thumbnail?.source_url || media.source_url" 
                           class="w-12 h-12 object-cover rounded-lg border border-[#e0e0e0]" />
                      <div v-else class="w-12 h-12 flex items-center justify-center rounded-lg bg-[#f5f5f5] text-[#757575] border border-[#e0e0e0]">
                        <svg v-if="media.mime_type.startsWith('video/')" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                      </div>
                    </td>
                    <td class="py-3 px-4">
                      <div class="font-semibold text-[#1e1e1e] hover:text-[#007CBA] cursor-pointer" @click.stop="selectMedia(media)">
                        {{ media.title?.rendered || 'Tanpa Judul' }}
                      </div>
                      <div class="text-[11px] text-[#757575] truncate max-w-[200px] md:max-w-md">
                        {{ media.source_url.split('/').pop() }}
                      </div>
                    </td>
                    <td class="py-3 px-4 text-[#757575]">{{ formattedDate(media.date) }}</td>
                    <td class="py-3 px-4 text-[#757575]">
                      <span v-if="media.media_details?.width">
                        {{ media.media_details.width }} × {{ media.media_details.height }}
                      </span>
                      <span v-else>-</span>
                    </td>
                    <td class="py-3 px-4 text-right" @click.stop>
                      <div class="flex justify-end items-center gap-2">
                        <button @click="selectMedia(media)" class="p-1 hover:text-[#007CBA] text-[#757575] transition-colors" title="Edit Metadata">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                          </svg>
                        </button>
                        <button @click="navigator.clipboard.writeText(media.source_url); showToast('success', 'Tautan disalin.')" class="p-1 hover:text-[#007CBA] text-[#757575] transition-colors" title="Salin URL">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Load More Button -->
            <div v-if="hasMore" class="flex justify-center pt-4">
              <button @click="loadMore" :disabled="loading" class="btn-secondary text-[13px] flex items-center gap-2">
                <svg v-if="loading" class="w-4 h-4 animate-spin text-[#007CBA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                {{ loading ? 'Memuat...' : 'Muat Lebih Banyak' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right side: Detail Panel -->
      <div class="w-full md:w-80 flex-shrink-0 flex flex-col bg-white border border-[#e0e0e0] rounded-2xl overflow-hidden" 
           :class="!selectedMedia ? 'hidden md:flex justify-center items-center p-8 text-center text-[#757575]' : ''">
        
        <div v-if="!selectedMedia" class="flex flex-col items-center">
          <svg class="w-10 h-10 text-[#e0e0e0] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-[13px]">Pilih salah satu berkas media untuk melihat detail dan mengedit metadata.</p>
        </div>

        <div v-else class="flex-1 flex flex-col overflow-y-auto">
          <!-- Image/File Preview -->
          <div class="p-5 border-b border-[#e0e0e0] bg-[#fafaf9]">
            <img v-if="selectedMedia.mime_type.startsWith('image/')"
                 :src="selectedMedia.source_url" 
                 class="w-full h-44 object-contain rounded-xl shadow-sm bg-white" />
            <div v-else class="w-full h-44 flex flex-col items-center justify-center rounded-xl bg-white border border-[#e0e0e0] text-[#757575]">
              <svg v-if="selectedMedia.mime_type.startsWith('video/')" class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              <svg v-else class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
              <span class="text-[12px] text-center font-medium px-4 truncate w-full">{{ selectedMedia.title?.rendered }}</span>
            </div>
          </div>

          <!-- File Info -->
          <div class="px-5 py-4 border-b border-[#e0e0e0] text-[11px] text-[#757575] space-y-1.5">
            <div class="flex justify-between"><span class="font-medium text-[#1e1e1e]">Nama Berkas:</span> <span class="truncate max-w-[150px]" :title="selectedMedia.source_url.split('/').pop()">{{ selectedMedia.source_url.split('/').pop() }}</span></div>
            <div class="flex justify-between"><span class="font-medium text-[#1e1e1e]">Tipe Berkas:</span> <span>{{ selectedMedia.mime_type }}</span></div>
            <div class="flex justify-between"><span class="font-medium text-[#1e1e1e]">Diunggah Pada:</span> <span>{{ formattedDate(selectedMedia.date) }}</span></div>
            <div class="flex justify-between" v-if="selectedMedia.media_details?.width"><span class="font-medium text-[#1e1e1e]">Dimensi:</span> <span>{{ selectedMedia.media_details.width }} × {{ selectedMedia.media_details.height }} piksel</span></div>
          </div>

          <!-- Fields Form -->
          <div class="p-5 space-y-4 flex-1">
            <div>
              <label class="block text-[11px] font-semibold text-[#757575] mb-1.5 uppercase tracking-wider">Judul Gambar</label>
              <input type="text" v-model="editingTitle" class="w-full border border-[#e0e0e0] rounded-xl px-3 py-2 text-[13px] text-[#1e1e1e] focus:border-[#007CBA] focus:outline-none transition-colors">
            </div>

            <div v-if="selectedMedia.mime_type.startsWith('image/')">
              <label class="block text-[11px] font-semibold text-[#757575] mb-1.5 uppercase tracking-wider">Teks Alternatif (Alt)</label>
              <input type="text" v-model="editingAlt" placeholder="Deskripsi untuk pembaca layar..." class="w-full border border-[#e0e0e0] rounded-xl px-3 py-2 text-[13px] text-[#1e1e1e] focus:border-[#007CBA] focus:outline-none transition-colors">
            </div>

            <div>
              <label class="block text-[11px] font-semibold text-[#757575] mb-1.5 uppercase tracking-wider">Keterangan (Caption)</label>
              <textarea v-model="editingCaption" rows="2" class="w-full border border-[#e0e0e0] rounded-xl px-3 py-2 text-[13px] text-[#1e1e1e] focus:border-[#007CBA] focus:outline-none transition-colors resize-none"></textarea>
            </div>

            <div>
              <label class="block text-[11px] font-semibold text-[#757575] mb-1.5 uppercase tracking-wider">Tautan Gambar (URL)</label>
              <div class="flex gap-2">
                <input type="text" readonly :value="selectedMedia.source_url" class="flex-1 bg-[#f0f0f1] border border-[#e0e0e0] rounded-xl px-3 py-2 text-[12px] text-[#757575] focus:outline-none truncate">
                <button @click="copyURL" class="px-3 bg-white border border-[#e0e0e0] text-[#1e1e1e] hover:bg-[#f0f0f1] text-[12px] font-medium rounded-xl transition-all flex items-center justify-center">
                  Copy
                </button>
              </div>
            </div>

            <div class="pt-2 space-y-2">
              <button @click="saveMetadata" :disabled="savingMeta" class="btn-primary w-full text-[13px] py-2 flex items-center justify-center gap-2">
                <svg v-if="savingMeta" class="w-4 h-4 animate-spin text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                {{ savingMeta ? 'Menyimpan...' : 'Simpan Metadata' }}
              </button>
              
              <button @click="deleteMedia" :disabled="deletingMedia" class="w-full text-[13px] py-2 text-red-600 border border-red-200 rounded-xl hover:bg-red-50 transition-colors flex items-center justify-center gap-2">
                <svg v-if="deletingMedia" class="w-4 h-4 animate-spin text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                {{ deletingMedia ? 'Menghapus...' : 'Hapus Gambar Secara Permanen' }}
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>
