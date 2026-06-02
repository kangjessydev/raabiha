<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  config: Object
})

const loading = ref(false)
const saving = ref(false)
const message = ref('')

const form = ref({
  store_name: '',
  store_email: '',
  footer_about: '',
  footer_phone: '',
  social_ig: '',
  social_tiktok: '',
  enable_xendit: 'no',
  enable_rajaongkir: 'no'
})

async function loadSettings() {
  loading.value = true
  message.value = ''
  try {
    const response = await axios.get(`${props.config.rest_url}raabiha/v1/settings`, {
      headers: { 'X-WP-Nonce': props.config.rest_nonce }
    })
    if (response.data) {
      form.value = { ...form.value, ...response.data }
    }
  } catch (error) {
    message.value = 'Failed to load settings.'
    console.error(error)
  } finally {
    loading.value = false
  }
}

async function saveSettings() {
  saving.value = true
  message.value = ''
  try {
    const response = await axios.post(`${props.config.rest_url}raabiha/v1/settings`, form.value, {
      headers: { 'X-WP-Nonce': props.config.rest_nonce }
    })
    if (response.data && response.data.success) {
      message.value = 'Settings saved successfully.'
      setTimeout(() => message.value = '', 3000)
    }
  } catch (error) {
    message.value = 'Failed to save settings.'
    console.error(error)
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadSettings()
})
</script>

<template>
  <div class="space-y-10">
    <div v-if="message" class="p-4 rounded-sm bg-[#007CBA]/10 border border-[#007CBA]/20 text-[#007CBA] text-xs font-bold uppercase tracking-wider flex items-center gap-3">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
      {{ message }}
    </div>

    <div v-if="loading" class="space-y-10 animate-pulse">
      <section>
        <div class="h-4 w-40 bg-[#e0e0e0] mb-5"></div>
        <div class="grid grid-cols-2 gap-8">
          <div><div class="h-3 w-20 bg-[#e0e0e0] mb-2"></div><div class="h-10 w-full bg-[#f0f0f1] border border-[#e0e0e0]"></div></div>
          <div><div class="h-3 w-20 bg-[#e0e0e0] mb-2"></div><div class="h-10 w-full bg-[#f0f0f1] border border-[#e0e0e0]"></div></div>
        </div>
      </section>
      <section>
        <div class="h-4 w-48 bg-[#e0e0e0] mb-5"></div>
        <div class="space-y-6">
          <div><div class="h-3 w-24 bg-[#e0e0e0] mb-2"></div><div class="h-24 w-full bg-[#f0f0f1] border border-[#e0e0e0]"></div></div>
          <div class="grid grid-cols-3 gap-8">
            <div><div class="h-3 w-20 bg-[#e0e0e0] mb-2"></div><div class="h-10 w-full bg-[#f0f0f1] border border-[#e0e0e0]"></div></div>
            <div><div class="h-3 w-20 bg-[#e0e0e0] mb-2"></div><div class="h-10 w-full bg-[#f0f0f1] border border-[#e0e0e0]"></div></div>
            <div><div class="h-3 w-20 bg-[#e0e0e0] mb-2"></div><div class="h-10 w-full bg-[#f0f0f1] border border-[#e0e0e0]"></div></div>
          </div>
        </div>
      </section>
      <section>
        <div class="h-4 w-40 bg-[#e0e0e0] mb-5"></div>
        <div class="grid grid-cols-2 gap-8">
          <div class="h-16 bg-[#f0f0f1] border border-[#e0e0e0] p-5"></div>
          <div class="h-16 bg-[#f0f0f1] border border-[#e0e0e0] p-5"></div>
        </div>
      </section>
      <div class="pt-4 flex justify-end">
        <div class="h-10 w-48 bg-[#e0e0e0]"></div>
      </div>
    </div>

    <template v-else>
      <!-- General Info Section -->
      <section>
        <h3 class="text-[10px] uppercase font-bold tracking-wider text-[#757575] mb-5 border-b border-[#e0e0e0] pb-2">General Information</h3>
        <div class="grid grid-cols-2 gap-8">
          <div>
            <label class="block text-[9px] uppercase font-bold tracking-wider text-[#1e1e1e] mb-2">Store Name</label>
            <input v-model="form.store_name" type="text" class="w-full bg-[#f0f0f1] border border-[#e0e0e0] text-xs px-4 py-3 outline-none focus:border-[#1e1e1e] transition-colors" placeholder="Raabiha">
          </div>
          <div>
            <label class="block text-[9px] uppercase font-bold tracking-wider text-[#1e1e1e] mb-2">Admin Email</label>
            <input v-model="form.store_email" type="email" class="w-full bg-[#f0f0f1] border border-[#e0e0e0] text-xs px-4 py-3 outline-none focus:border-[#1e1e1e] transition-colors" placeholder="admin@raabiha.com">
          </div>
        </div>
      </section>

      <!-- Footer & Contact Section -->
      <section>
        <h3 class="text-[10px] uppercase font-bold tracking-wider text-[#757575] mb-5 border-b border-[#e0e0e0] pb-2">Footer & Contact Details</h3>
        <div class="space-y-6">
          <div>
            <label class="block text-[9px] uppercase font-bold tracking-wider text-[#1e1e1e] mb-2">Footer About Text</label>
            <textarea v-model="form.footer_about" rows="3" class="w-full bg-[#f0f0f1] border border-[#e0e0e0] text-xs px-4 py-3 outline-none focus:border-[#1e1e1e] transition-colors font-sans leading-relaxed"></textarea>
          </div>
          <div class="grid grid-cols-3 gap-8">
             <div>
                <label class="block text-[9px] uppercase font-bold tracking-wider text-[#1e1e1e] mb-2">Phone / WhatsApp</label>
                <input v-model="form.footer_phone" type="text" class="w-full bg-[#f0f0f1] border border-[#e0e0e0] text-xs px-4 py-3 outline-none focus:border-[#1e1e1e] transition-colors" placeholder="+62 812...">
             </div>
             <div>
                <label class="block text-[9px] uppercase font-bold tracking-wider text-[#1e1e1e] mb-2">Instagram URL</label>
                <input v-model="form.social_ig" type="url" class="w-full bg-[#f0f0f1] border border-[#e0e0e0] text-xs px-4 py-3 outline-none focus:border-[#1e1e1e] transition-colors" placeholder="https://instagram.com/...">
             </div>
             <div>
                <label class="block text-[9px] uppercase font-bold tracking-wider text-[#1e1e1e] mb-2">TikTok URL</label>
                <input v-model="form.social_tiktok" type="url" class="w-full bg-[#f0f0f1] border border-[#e0e0e0] text-xs px-4 py-3 outline-none focus:border-[#1e1e1e] transition-colors" placeholder="https://tiktok.com/...">
             </div>
          </div>
        </div>
      </section>

      <!-- Integrations Section -->
      <section>
        <h3 class="text-[10px] uppercase font-bold tracking-wider text-[#757575] mb-5 border-b border-[#e0e0e0] pb-2">Platform Integrations</h3>
        <div class="grid grid-cols-2 gap-8">
          <div class="flex items-start justify-between bg-[#f0f0f1] border border-[#e0e0e0] p-5">
             <div>
               <h4 class="text-[10px] uppercase font-bold tracking-wider text-[#1e1e1e] mb-1">Xendit Payment Gateway</h4>
               <p class="text-xs text-[#757575]">Enable automated payments via Xendit API.</p>
             </div>
             <label class="relative inline-flex items-center cursor-pointer mt-1">
               <input type="checkbox" v-model="form.enable_xendit" true-value="yes" false-value="no" class="sr-only peer">
               <div class="w-9 h-5 bg-[#cccccc] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#007CBA]"></div>
             </label>
          </div>
          <div class="flex items-start justify-between bg-[#f0f0f1] border border-[#e0e0e0] p-5">
             <div>
               <h4 class="text-[10px] uppercase font-bold tracking-wider text-[#1e1e1e] mb-1">RajaOngkir Shipping</h4>
               <p class="text-xs text-[#757575]">Enable live shipping rate calculation.</p>
             </div>
             <label class="relative inline-flex items-center cursor-pointer mt-1">
               <input type="checkbox" v-model="form.enable_rajaongkir" true-value="yes" false-value="no" class="sr-only peer">
               <div class="w-9 h-5 bg-[#cccccc] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#007CBA]"></div>
             </label>
          </div>
        </div>
      </section>

      <!-- Save Button -->
      <div class="pt-4 flex justify-end">
         <button @click="saveSettings" :disabled="saving" class="bg-[#1e1e1e] text-white px-8 py-3 text-[10px] uppercase tracking-wider font-bold hover:bg-[#f0f0f1]lack transition-colors disabled:opacity-50">
           {{ saving ? 'SAVING...' : 'SAVE CONFIGURATION' }}
         </button>
      </div>
    </template>
  </div>
</template>
