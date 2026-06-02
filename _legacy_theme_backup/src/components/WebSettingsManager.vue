<script setup>
import { useToast } from '../composables/useToast'
const { showToast } = useToast()

import { ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  config: { type: Object, required: true }
})

const loading = ref(true)
const saving = ref(false)

const form = ref({
  header_promo_text: '',
  footer_about: '',
  footer_email: '',
  footer_phone: '',
  social_ig: '',
  social_tiktok: ''
})

async function fetchSettings() {
  loading.value = true
  try {
    const params = new URLSearchParams()
    params.append('nonce', props.config.nonce || '')

    const res = await axios.get(props.config.rest_url + 'raabiha/v1/web-settings', {
      headers: { 'X-WP-Nonce': props.config.rest_nonce }
    })
    form.value = res.data
  } catch (err) {
    console.error('Error fetching settings', err)
  } finally {
    loading.value = false
  }
}

async function saveSettings() {
  saving.value = true
  try {
    await axios.post(props.config.rest_url + 'raabiha/v1/web-settings', form.value, {
      headers: { 'X-WP-Nonce': props.config.rest_nonce }
    })
    showToast('success', 'Pengaturan Tampilan Web berhasil disimpan!')
  } catch (err) {
    console.error('Error saving settings', err)
    showToast('error', 'Gagal menyimpan pengaturan.')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchSettings()
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="font-sans text-[28px] md:text-[32px] font-bold text-[#1e1e1e] mb-2 leading-tight">Tampilan Web</h2>
        <p class="text-[13px] text-[#757575]">Kelola konten Header, Footer, dan Informasi Kontak toko.</p>
      </div>
      <button @click="saveSettings" :disabled="saving" class="btn-primary text-xs px-6 py-2 flex items-center gap-2">
        <svg v-if="saving" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 3v4"/></svg>
        {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
      </button>
    </div>

    <div v-if="loading" class="space-y-4">
      <div v-for="n in 3" :key="n" class="h-32 bg-[#E5E1D8] animate-pulse rounded-lg"></div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Header Section -->
      <div class="raabiha-card p-6 space-y-4">
        <h3 class="text-sm font-bold text-[#1e1e1e] border-b border-[#e0e0e0] pb-2 mb-4">Header / Topbar</h3>
        <div>
          <label class="block text-xs font-bold text-[#757575] mb-1.5">Teks Promo Topbar</label>
          <input v-model="form.header_promo_text" type="text" class="form-input text-xs w-full bg-white text-[#1e1e1e] border-[#e0e0e0]" placeholder="Misal: GRATIS ONGKIR SELURUH INDONESIA" />
          <p class="text-[10px] text-[#757575] mt-1">Teks berjalan (marquee) di bagian paling atas web.</p>
        </div>
      </div>

      <!-- Footer Info Section -->
      <div class="raabiha-card p-6 space-y-4 md:row-span-2">
        <h3 class="text-sm font-bold text-[#1e1e1e] border-b border-[#e0e0e0] pb-2 mb-4">Informasi Perusahaan (Footer)</h3>
        <div>
          <label class="block text-xs font-bold text-[#757575] mb-1.5">Tentang Toko</label>
          <textarea v-model="form.footer_about" rows="4" class="form-input text-xs w-full bg-white text-[#1e1e1e] border-[#e0e0e0]" placeholder="Deskripsi singkat toko..."></textarea>
        </div>
        <div>
          <label class="block text-xs font-bold text-[#757575] mb-1.5">Email Kontak</label>
          <input v-model="form.footer_email" type="email" class="form-input text-xs w-full bg-white text-[#1e1e1e] border-[#e0e0e0]" />
        </div>
        <div>
          <label class="block text-xs font-bold text-[#757575] mb-1.5">Nomor Telepon / WA</label>
          <input v-model="form.footer_phone" type="text" class="form-input text-xs w-full bg-white text-[#1e1e1e] border-[#e0e0e0]" />
        </div>
      </div>

      <!-- Social Media Section -->
      <div class="raabiha-card p-6 space-y-4">
        <h3 class="text-sm font-bold text-[#1e1e1e] border-b border-[#e0e0e0] pb-2 mb-4">Sosial Media</h3>
        <div>
          <label class="block text-xs font-bold text-[#757575] mb-1.5">Instagram URL</label>
          <input v-model="form.social_ig" type="url" class="form-input text-xs w-full bg-white text-[#1e1e1e] border-[#e0e0e0]" placeholder="https://instagram.com/..." />
        </div>
        <div>
          <label class="block text-xs font-bold text-[#757575] mb-1.5">TikTok URL</label>
          <input v-model="form.social_tiktok" type="url" class="form-input text-xs w-full bg-white text-[#1e1e1e] border-[#e0e0e0]" placeholder="https://tiktok.com/..." />
        </div>
      </div>
    </div>
  </div>
</template>
