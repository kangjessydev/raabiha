<script setup>
import { useToast } from '../composables/useToast'
import { useConfirm } from '../composables/useConfirm'

const { showToast } = useToast()
const { triggerConfirm } = useConfirm()

import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const props = defineProps({ config: Object, initialView: String })
const emit = defineEmits(['set-view'])

const activeTab = ref(props.initialView)
watch(() => props.initialView, v => { activeTab.value = v })

const loading = ref(true)
const saving = ref(false)
const logoId = ref(0)
const logoUrl = ref('')
const menuItems = ref([])

// --- Media Modal ---
const isMediaModalOpen = ref(false)
const mediaTab = ref('library')
const mediaItems = ref([])
const loadingMedia = ref(false)
const uploadingImage = ref(false)
const isDragging = ref(false)
const fileInput = ref(null)

// Selected + detail panel
const selectedMedia = ref(null)
const editingTitle = ref('')
const editingAlt = ref('')
const editingCaption = ref('')
const savingMeta = ref(false)
const deletingMedia = ref(false)

async function fetchSettings() {
  loading.value = true
  try {
    const res = await axios.get(`${props.config.rest_url}raabiha/v1/ui-settings`, {
      headers: { 'X-WP-Nonce': props.config.rest_nonce }
    })
    logoId.value = res.data.logo.id || 0
    logoUrl.value = res.data.logo.url || ''
    menuItems.value = res.data.header_menu || []
  } catch (err) {
    console.error('Error fetching UI settings', err)
  } finally {
    loading.value = false
  }
}

async function saveSettings() {
  saving.value = true
  try {
    await axios.post(`${props.config.rest_url}raabiha/v1/ui-settings`, {
      logo_id: logoId.value,
      header_menu: menuItems.value
    }, { headers: { 'X-WP-Nonce': props.config.rest_nonce } })
    showToast('success', 'Pengaturan UI berhasil disimpan.')
  } catch (err) {
    showToast('error', 'Gagal menyimpan pengaturan.')
  } finally {
    saving.value = false
  }
}

function triggerLogoUpload() {
  isMediaModalOpen.value = true
  mediaTab.value = 'library'
  selectedMedia.value = null
  fetchMediaLibrary()
}

async function fetchMediaLibrary() {
  loadingMedia.value = true
  try {
    const res = await axios.get(`${props.config.rest_url}wp/v2/media?media_type=image&per_page=40`, {
      headers: { 'X-WP-Nonce': props.config.rest_nonce }
    })
    mediaItems.value = res.data
  } catch (err) {
    console.error('Gagal mengambil pustaka media', err)
  } finally {
    loadingMedia.value = false
  }
}

async function uploadToWP(file) {
  if (!file || !file.type.startsWith('image/')) return
  uploadingImage.value = true
  const formData = new FormData()
  formData.append('file', file)
  try {
    // Do NOT set Content-Type manually — let browser set multipart boundary
    const res = await axios.post(`${props.config.rest_url}wp/v2/media`, formData, {
      headers: {
        'X-WP-Nonce': props.config.rest_nonce
      }
    })
    mediaItems.value.unshift(res.data)
    selectMedia(res.data)
    mediaTab.value = 'library'
  } catch (err) {
    const msg = err?.response?.data?.message || 'Gagal mengunggah. Cek hak akses akun Anda.'
    showToast('error', msg)
  } finally {
    uploadingImage.value = false
    if (fileInput.value) fileInput.value.value = ''
  }
}

function handleFileInputChange(e) { uploadToWP(e.target.files[0]) }
function handleDrop(e) {
  e.preventDefault()
  isDragging.value = false
  uploadToWP(e.dataTransfer.files[0])
}

function selectMedia(media) {
  selectedMedia.value = media
  editingTitle.value = media.title?.rendered || ''
  editingAlt.value = media.alt_text || ''
  editingCaption.value = media.caption?.rendered || ''
}

async function saveMediaMeta() {
  if (!selectedMedia.value) return
  savingMeta.value = true
  try {
    const res = await axios.post(
      `${props.config.rest_url}wp/v2/media/${selectedMedia.value.id}`,
      { title: editingTitle.value, alt_text: editingAlt.value, caption: editingCaption.value },
      { headers: { 'X-WP-Nonce': props.config.rest_nonce } }
    )
    // Update in list
    const idx = mediaItems.value.findIndex(m => m.id === res.data.id)
    if (idx !== -1) mediaItems.value[idx] = res.data
    selectedMedia.value = res.data
  } catch (err) {
    showToast('error', 'Gagal menyimpan metadata.')
  } finally {
    savingMeta.value = false
  }
}

function deleteMedia() {
  if (!selectedMedia.value) return
  triggerConfirm(
    'Hapus Gambar',
    `Apakah Anda yakin ingin menghapus gambar "${editingTitle.value}"? Tindakan ini tidak dapat dibatalkan.`,
    async () => {
      deletingMedia.value = true
      try {
        await axios.delete(
          `${props.config.rest_url}wp/v2/media/${selectedMedia.value.id}?force=true`,
          { headers: { 'X-WP-Nonce': props.config.rest_nonce } }
        )
        mediaItems.value = mediaItems.value.filter(m => m.id !== selectedMedia.value.id)
        if (logoId.value === selectedMedia.value.id) { logoId.value = 0; logoUrl.value = '' }
        selectedMedia.value = null
      } catch (err) {
        showToast('error', 'Gagal menghapus gambar.')
      } finally {
        deletingMedia.value = false
      }
    }
  )
}

function confirmMediaSelection() {
  if (selectedMedia.value) {
    logoId.value = selectedMedia.value.id
    logoUrl.value = selectedMedia.value.source_url
    isMediaModalOpen.value = false
  }
}

function removeLogo() { logoId.value = 0; logoUrl.value = '' }
function addMenuItem() { menuItems.value.push({ title: 'Menu Baru', url: '#' }) }
function removeMenuItem(i) { menuItems.value.splice(i, 1) }
function moveMenuItem(i, dir) {
  if (dir === 'up' && i > 0) { [menuItems.value[i], menuItems.value[i-1]] = [menuItems.value[i-1], menuItems.value[i]] }
  else if (dir === 'down' && i < menuItems.value.length - 1) { [menuItems.value[i], menuItems.value[i+1]] = [menuItems.value[i+1], menuItems.value[i]] }
}

onMounted(fetchSettings)
</script>

<template>
  <div class="h-full flex flex-col">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="font-sans text-[28px] md:text-[32px] font-bold text-[#222523] mb-2 leading-tight">UI Setting</h2>
        <p class="text-[13px] text-[#888888]">Sesuaikan tampilan antarmuka halaman publik situs Anda.</p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="fetchSettings" :disabled="loading" class="btn-secondary flex items-center gap-2">
          <svg class="w-4 h-4" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          Sync Data
        </button>
        <button @click="saveSettings" :disabled="saving" class="btn-primary flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
        </button>
      </div>
    </div>

    <div class="raabiha-card overflow-hidden">
      <div class="px-5 pt-4 border-b border-[#f1f1f1] flex gap-6 text-[13px] font-medium bg-white overflow-x-auto">
        <button @click="activeTab = 'header_setting'; emit('set-view', 'header_setting')" :class="activeTab === 'header_setting' ? 'text-[#007CBA] border-b-2 border-[#007CBA]' : 'text-[#888888] hover:text-[#222523]'" class="pb-3 px-1 transition-colors whitespace-nowrap">Header & Navigasi</button>
        <button @click="activeTab = 'footer_setting'; emit('set-view', 'footer_setting')" :class="activeTab === 'footer_setting' ? 'text-[#007CBA] border-b-2 border-[#007CBA]' : 'text-[#888888] hover:text-[#222523]'" class="pb-3 px-1 transition-colors whitespace-nowrap">Footer (Akan Datang)</button>
      </div>

      <div class="p-6 md:p-8">
        <div v-if="loading" class="space-y-4">
          <div class="h-8 w-1/4 bg-[#f7f7f5] animate-pulse rounded"></div>
          <div class="h-32 bg-[#f7f7f5] animate-pulse rounded"></div>
        </div>

        <div v-else-if="activeTab === 'header_setting'" class="space-y-10 max-w-3xl">
          <!-- Logo -->
          <section>
            <h3 class="text-base font-semibold text-[#222523] mb-4">Logo Situs</h3>
            <div class="flex items-start gap-6">
              <div class="w-40 h-40 border-2 border-dashed border-[#e5e5e5] rounded-lg flex items-center justify-center bg-[#fcfcfc] overflow-hidden">
                <img v-if="logoUrl" :src="logoUrl" class="w-full h-full object-contain p-2" />
                <div v-else class="text-center p-4">
                  <svg class="w-8 h-8 text-[#e5e5e5] mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  <span class="text-[11px] text-[#888888]">Belum ada logo</span>
                </div>
              </div>
              <div class="space-y-3">
                <button @click="triggerLogoUpload" class="btn-secondary text-[13px]">Pilih Gambar Logo</button>
                <button v-if="logoId" @click="removeLogo" class="block text-red-600 hover:underline text-[13px]">Hapus Logo</button>
                <p class="text-[11px] text-[#888888] max-w-xs mt-2">Gambar otomatis dikonversi ke WebP & dikompresi saat diunggah.</p>
              </div>
            </div>
          </section>

          <hr class="border-[#f1f1f1]" />

          <!-- Menu -->
          <section>
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-base font-semibold text-[#222523]">Menu Utama (Navbar)</h3>
              <button @click="addMenuItem" class="text-[13px] text-[#007CBA] font-medium hover:underline">+ Tambah Item</button>
            </div>
            <div class="bg-[#fcfcfc] border border-[#e5e5e5] rounded-lg p-1">
              <div v-if="menuItems.length === 0" class="text-center py-8 text-[#888888] text-[13px]">Menu masih kosong.</div>
              <div v-for="(item, index) in menuItems" :key="index" class="bg-white border border-[#e5e5e5] rounded p-4 mb-2 shadow-sm flex flex-col sm:flex-row sm:items-start gap-4">
                <div class="flex flex-col gap-1 items-center justify-center text-[#e5e5e5]">
                  <button @click="moveMenuItem(index, 'up')" :disabled="index === 0" class="hover:text-[#222523] disabled:opacity-30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                  </button>
                  <button @click="moveMenuItem(index, 'down')" :disabled="index === menuItems.length - 1" class="hover:text-[#222523] disabled:opacity-30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                  </button>
                </div>
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-[11px] font-medium text-[#888888] mb-1">Label Navigasi</label>
                    <input type="text" v-model="item.title" class="w-full bg-white border border-[#e5e5e5] text-[#222523] px-2.5 py-1.5 text-[13px] focus:border-[#007CBA] focus:outline-none transition-colors rounded">
                  </div>
                  <div>
                    <label class="block text-[11px] font-medium text-[#888888] mb-1">Tautan (URL)</label>
                    <input type="text" v-model="item.url" class="w-full bg-white border border-[#e5e5e5] text-[#222523] px-2.5 py-1.5 text-[13px] focus:border-[#007CBA] focus:outline-none transition-colors rounded">
                  </div>
                </div>
                <div class="flex items-center">
                  <button @click="removeMenuItem(index)" class="text-red-500 hover:text-red-700 p-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>
              </div>
            </div>
          </section>
        </div>

        <div v-else-if="activeTab === 'footer_setting'" class="text-center py-20">
          <h2 class="text-lg font-sans text-[#222523] mb-2">Module Not Ready</h2>
          <p class="text-[13px] text-[#888888]">Pengaturan Footer sedang dalam tahap pengembangan.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Media Library Modal -->
  <div v-if="isMediaModalOpen" class="fixed inset-0 z-[9999] bg-black/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl h-[85vh] flex flex-col overflow-hidden">

      <!-- Modal Header -->
      <div class="flex justify-between items-center px-6 py-4 border-b border-[#f1f1f1]">
        <h3 class="text-[17px] font-bold text-[#222523] font-sans">Pustaka Media</h3>
        <button @click="isMediaModalOpen = false" class="text-[#aaa] hover:text-red-500 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <!-- Tabs -->
      <div class="flex border-b border-[#f1f1f1] px-6 text-[13px]">
        <button @click="mediaTab = 'upload'" :class="mediaTab === 'upload' ? 'border-b-2 border-[#007CBA] text-[#007CBA] font-semibold' : 'text-[#888] hover:text-[#222523]'" class="px-2 py-3 mr-6 transition-colors">Unggah Berkas</button>
        <button @click="mediaTab = 'library'" :class="mediaTab === 'library' ? 'border-b-2 border-[#007CBA] text-[#007CBA] font-semibold' : 'text-[#888] hover:text-[#222523]'" class="px-2 py-3 transition-colors">Pustaka Media</button>
      </div>

      <!-- Body: split left/right when item selected in library -->
      <div class="flex-1 overflow-hidden flex">

        <!-- Left: grid/upload -->
        <div class="flex-1 overflow-y-auto p-5 bg-[#FAF9F7]">

          <!-- Upload Tab -->
          <div v-if="mediaTab === 'upload'"
               class="h-full min-h-[300px] flex flex-col items-center justify-center border-2 border-dashed rounded-xl bg-white transition-colors cursor-pointer"
               :class="isDragging ? 'border-[#007CBA] bg-[#f0f7ff]' : 'border-[#e0e0e0]'"
               @dragover.prevent="isDragging = true"
               @dragleave.prevent="isDragging = false"
               @drop="handleDrop"
               @click="fileInput.click()">
            <input type="file" ref="fileInput" class="hidden" @change="handleFileInputChange" accept="image/*">
            <div v-if="!uploadingImage" class="text-center p-8 pointer-events-none">
              <svg class="w-12 h-12 mx-auto mb-3 text-[#ccc]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
              <p class="text-[14px] font-medium text-[#555] mb-1">Seret & letakkan gambar di sini</p>
              <p class="text-[12px] text-[#aaa]">atau klik untuk memilih berkas</p>
              <p class="mt-4 text-[11px] text-[#ccc] bg-[#f9f9f9] px-3 py-1.5 rounded-full inline-block">✓ Otomatis dikonversi ke WebP & dikompresi 80%</p>
            </div>
            <div v-else class="text-center pointer-events-none">
              <svg class="w-10 h-10 animate-spin text-[#007CBA] mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
              <p class="text-[13px] text-[#007CBA] font-semibold">Mengunggah & mengonversi ke WebP...</p>
            </div>
          </div>

          <!-- Library Grid -->
          <div v-if="mediaTab === 'library'">
            <p v-if="loadingMedia" class="text-center py-12 text-[#888] text-[13px]">Memuat Pustaka Media...</p>
            <div v-else class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
              <div v-for="media in mediaItems" :key="media.id"
                   @click="selectMedia(media)"
                   :class="selectedMedia && selectedMedia.id === media.id ? 'ring-2 ring-[#007CBA] ring-offset-2' : 'border border-[#e5e5e5] hover:border-[#007CBA]'"
                   class="relative aspect-square bg-white cursor-pointer rounded-lg overflow-hidden transition-all group">
                <img :src="media.media_details?.sizes?.thumbnail?.source_url || media.source_url" class="w-full h-full object-cover group-hover:opacity-90 transition-opacity" />
                <div v-if="selectedMedia && selectedMedia.id === media.id" class="absolute top-1.5 right-1.5 bg-[#007CBA] text-white rounded-full p-0.5 shadow">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </div>
              </div>
              <p v-if="!loadingMedia && mediaItems.length === 0" class="col-span-full text-center py-12 text-[#888] text-[13px]">Belum ada gambar yang diunggah.</p>
            </div>
          </div>
        </div>

        <!-- Right: detail panel (only shown when item selected) -->
        <div v-if="mediaTab === 'library' && selectedMedia" class="w-72 border-l border-[#f1f1f1] bg-white overflow-y-auto flex flex-col">
          <div class="p-4 border-b border-[#f9f9f9]">
            <img :src="selectedMedia.source_url" class="w-full h-40 object-contain bg-[#FAF9F7] rounded-lg mb-3" />
            <p class="text-[11px] text-[#aaa] text-center truncate">{{ selectedMedia.source_url.split('/').pop() }}</p>
          </div>
          <div class="p-4 space-y-4 flex-1">
            <div>
              <label class="block text-[11px] font-semibold text-[#888] mb-1 uppercase tracking-wide">Judul</label>
              <input type="text" v-model="editingTitle" class="w-full border border-[#e5e5e5] rounded px-2.5 py-1.5 text-[13px] text-[#222523] focus:border-[#007CBA] focus:outline-none">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-[#888] mb-1 uppercase tracking-wide">Alt Text</label>
              <input type="text" v-model="editingAlt" class="w-full border border-[#e5e5e5] rounded px-2.5 py-1.5 text-[13px] text-[#222523] focus:border-[#007CBA] focus:outline-none" placeholder="Deskripsi gambar...">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-[#888] mb-1 uppercase tracking-wide">Caption</label>
              <textarea v-model="editingCaption" rows="2" class="w-full border border-[#e5e5e5] rounded px-2.5 py-1.5 text-[13px] text-[#222523] focus:border-[#007CBA] focus:outline-none resize-none"></textarea>
            </div>
            <button @click="saveMediaMeta" :disabled="savingMeta" class="btn-primary w-full text-[13px] py-2 disabled:opacity-60">
              {{ savingMeta ? 'Menyimpan...' : 'Simpan Metadata' }}
            </button>
            <button @click="deleteMedia" :disabled="deletingMedia" class="w-full text-[13px] py-2 text-red-600 border border-red-200 rounded hover:bg-red-50 transition-colors disabled:opacity-60">
              {{ deletingMedia ? 'Menghapus...' : 'Hapus Gambar Ini' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="px-6 py-4 border-t border-[#f1f1f1] flex justify-end gap-3 bg-white">
        <button @click="isMediaModalOpen = false" class="px-5 py-2 text-[#888] hover:text-[#222523] text-[13px] font-medium transition-colors">Batal</button>
        <button @click="confirmMediaSelection" :disabled="!selectedMedia" class="btn-primary px-6 py-2 text-[13px] disabled:opacity-50">Gunakan Gambar Ini</button>
      </div>
    </div>
  </div>
</template>
