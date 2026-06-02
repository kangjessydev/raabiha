<script setup>
import { useToast } from '../composables/useToast'
import { useConfirm } from '../composables/useConfirm'

const { showToast } = useToast()
const { triggerConfirm } = useConfirm()

import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  config: { type: Object, required: true },
  initialView: { type: String, default: 'users_list' }
})
const emit = defineEmits(['set-view'])

const activeTab = ref(props.initialView)

// Watch for external view changes from App.vue sidebar
watch(() => props.initialView, (newVal) => {
  activeTab.value = newVal
  if (newVal === 'users_list') {
    fetchUsers()
  }
})

// HTTP Client configured for WP REST API
const api = axios.create({
  baseURL: props.config.rest_url + 'wp/v2/',
  headers: {
    'X-WP-Nonce': props.config.rest_nonce
  }
})

// ── State: Users ──────────────────────────────────────────────
const users = ref([])
const loadingUsers = ref(false)

const selectedItems = ref([])
const selectAll = computed({
  get: () => users.value.length > 0 && selectedItems.value.length === users.value.length,
  set: (val) => {
    selectedItems.value = val ? users.value.map(u => u.id) : []
  }
})

async function fetchUsers() {
  loadingUsers.value = true
  try {
    const res = await api.get('users?context=edit&per_page=50')
    users.value = res.data
  } catch (err) {
    console.error('Error fetching users', err)
    if (err.response && err.response.status === 403) {
      showToast('error', 'Anda tidak memiliki izin untuk melihat daftar pengguna.')
    }
  } finally {
    loadingUsers.value = false
  }
}

function deleteUser(id) {
  triggerConfirm(
    'Hapus Pengguna',
    'Apakah Anda yakin ingin menghapus pengguna ini? (Data terkait pengguna ini mungkin juga akan terhapus!)',
    async () => {
      try {
        // Requires reassign param in WP REST API if user has posts. For safety, reassigning to current user.
        await api.delete(`users/${id}?reassign=${props.config.user.id}&force=true`)
        fetchUsers()
      } catch (err) {
        console.error('Error deleting user', err)
        showToast('error', 'Gagal menghapus pengguna.')
      }
    }
  )
}

onMounted(() => {
  if (activeTab.value === 'users_list') {
    fetchUsers()
  }
})

const newUser = ref({
  username: '',
  email: '',
  first_name: '',
  last_name: '',
  password: '',
  roles: ['customer']
})
const savingUser = ref(false)

async function saveUser() {
  if (!newUser.value.username || !newUser.value.email || !newUser.value.password) {
    showToast('error', 'Username, Email, dan Password harus diisi!')
    return
  }
  
  savingUser.value = true
  try {
    await api.post('users', newUser.value)
    showToast('success', 'Pengguna berhasil ditambahkan!')
    emit('set-view', 'users_list')
    newUser.value = {
      username: '', email: '', first_name: '', last_name: '', password: '', roles: ['customer']
    }
  } catch (err) {
    console.error('Error creating user', err)
    if (err.response && err.response.data && err.response.data.message) {
      showToast('error', `Gagal: ${err.response.data.message}`)
    } else {
      showToast('error', 'Gagal membuat pengguna baru. Pastikan Anda memiliki izin yang cukup.')
    }
  } finally {
    savingUser.value = false
  }
}

function formatRole(roles) {
  if (!roles || roles.length === 0) return 'Tanpa Peran'
  const roleNames = {
    'administrator': 'Administrator',
    'raabiha_owner': 'Owner',
    'raabiha_co_admin': 'Co-Administrator',
    'raabiha_cashier': 'Admin Kasir',
    'raabiha_blogger': 'Blogger',
    'customer': 'Pelanggan',
    'subscriber': 'Pelanggan',
    'editor': 'Editor',
    'author': 'Penulis',
    'contributor': 'Kontributor',
    'shop_manager': 'Manajer Toko'
  }
  return roleNames[roles[0]] || roles[0]
}

</script>

<template>
  <div class="h-full flex flex-col">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6" v-if="activeTab === 'users_list'">
      <div>
        <h2 class="font-sans text-[28px] md:text-[32px] font-bold text-[#1e1e1e] mb-2 leading-tight">Pengguna</h2>
        <p class="text-[13px] text-[#757575]">Kelola akun, peran, dan hak akses staf toko Anda.</p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="fetchUsers" :disabled="loadingUsers" class="btn-secondary flex items-center gap-2">
          <svg class="w-4 h-4" :class="{'animate-spin': loadingUsers}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          Sync Data
        </button>
        <button @click="emit('set-view', 'add_user')" class="btn-primary flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
          Tambah Pengguna
        </button>
      </div>
    </div>

    <div v-if="activeTab === 'users_list'" class="raabiha-card overflow-hidden">
      
      <!-- Tabs -->
      <div class="px-5 pt-4 border-b border-[#f1f1f1] flex gap-6 text-[13px] font-medium bg-white overflow-x-auto">
         <button class="text-[#007CBA] border-b-2 border-[#007CBA] pb-3 px-1 whitespace-nowrap">Semua <span class="text-[#757575] font-normal ml-1">({{ users.length }})</span></button>
      </div>

      <!-- Card Toolbar Top -->
      <div v-if="activeTab === 'users_list'" class="px-5 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border-b border-[#f1f1f1]">
        <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto pb-2 sm:pb-0">
           <select class="w-auto flex-shrink-0 text-[13px] text-[#1e1e1e] bg-white border border-[#cccccc] rounded px-2.5 py-1.5 focus:border-[#007CBA] outline-none">
             <option>Tindakan Massal</option>
             <option>Hapus</option>
           </select>
           <button class="flex-shrink-0 border border-[#007CBA] text-[#007CBA] hover:bg-[#007CBA] hover:text-white rounded px-3 py-1.5 text-[13px] font-medium transition-colors">
             Terapkan
           </button>
           
           <div class="w-px h-5 bg-[#e0e0e0] mx-2 hidden sm:block"></div>
           
           <select class="w-auto flex-shrink-0 text-[13px] text-[#1e1e1e] bg-white border border-[#cccccc] rounded px-2.5 py-1.5 focus:border-[#007CBA] outline-none">
             <option>Ubah peran menjadi...</option>
             <option>Administrator</option>
             <option>Owner</option>
             <option>Admin Kasir</option>
             <option>Pelanggan</option>
           </select>
           <button class="flex-shrink-0 border border-[#cccccc] text-[#1e1e1e] hover:bg-[#f0f0f1] rounded px-3 py-1.5 text-[13px] font-medium transition-colors">
             Ubah
           </button>
        </div>
        
        <div class="flex items-center gap-4 w-full sm:w-auto">
           <div class="relative w-full sm:w-48 flex items-center bg-white border border-[#cccccc] rounded px-2.5 py-1.5 focus-within:border-[#007CBA] focus-within:ring-1 focus-within:ring-[#007CBA] transition-all">
             <input type="text" placeholder="Cari Pengguna" class="bg-transparent border-none outline-none text-[13px] w-full placeholder-[#757575] text-[#1e1e1e] font-sans">
           </div>
           <span class="text-[13px] text-[#757575] font-medium whitespace-nowrap">{{ users.length }} item</span>
        </div>
      </div>

      <div v-if="activeTab === 'users_list' && loadingUsers" class="p-5 space-y-3">
        <div v-for="n in 3" :key="n" class="h-12 bg-[#E5E1D8] animate-pulse rounded-lg"></div>
      </div>
      
      <div v-else-if="activeTab === 'users_list' && users.length === 0" class="text-center py-10">
        <p class="text-[#757575] text-[13px]">Belum ada data pengguna.</p>
      </div>

      <div v-else-if="activeTab === 'users_list'" class="overflow-x-auto">
        <table class="w-full text-left text-[13px]">
          <thead class="bg-[#f9f9fa] border-b border-[#cccccc]">
            <tr class="text-[#1e1e1e]">
              <th class="py-3 pl-5 w-12"><input type="checkbox" v-model="selectAll" class="rounded border-[#8c8f94] text-[#007CBA] focus:ring-[#007CBA] cursor-pointer"></th>
              <th class="py-3 pl-3 font-semibold text-[13px] min-w-[200px]">Username</th>
              <th class="py-3 font-semibold text-[13px] min-w-[150px]">Nama</th>
              <th class="py-3 font-semibold text-[13px] min-w-[150px]">Email</th>
              <th class="py-3 font-semibold text-[13px] min-w-[120px]">Peran</th>
              <th class="py-3 pr-5 text-right font-semibold text-[13px] min-w-[150px]">Aksi</th>
            </tr>
          </thead>
          <tbody class="text-[#1e1e1e]">
            <tr v-for="u in users" :key="u.id" class="border-b border-[#f1f1f1] hover:bg-[#fcfcfc] transition-colors group">
              <td class="py-3 pl-5"><input type="checkbox" v-model="selectedItems" :value="u.id" class="rounded border-[#8c8f94] text-[#007CBA] focus:ring-[#007CBA] cursor-pointer"></td>
              <td class="py-3 pl-3 text-[13px] font-bold text-[#007CBA] pr-4 flex items-center gap-3">
                <img :src="u.avatar_urls && u.avatar_urls['48'] ? u.avatar_urls['48'] : 'https://secure.gravatar.com/avatar/?s=48&d=mm'" class="w-8 h-8 rounded-full border border-[#cccccc]" alt="Avatar">
                <span class="hover:underline cursor-pointer">{{ u.username || u.slug }}</span>
              </td>
              <td class="py-3 text-[13px]">{{ u.name }}</td>
              <td class="py-3 text-[13px] text-[#007CBA] hover:underline cursor-pointer">{{ u.email }}</td>
              <td class="py-3 text-[13px] text-[#757575]">{{ formatRole(u.roles) }}</td>
              <td class="py-3 pr-5 text-right">
                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                  <a :href="`${config.site_url}/wp-admin/user-edit.php?user_id=${u.id}`" target="_blank" class="p-1.5 text-[#757575] hover:text-[#1e1e1e] hover:bg-gray-100 rounded-lg transition-colors" title="Edit via Gutenberg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  </a>
                  <button @click="deleteUser(u.id)" class="p-1.5 text-[#757575] hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
          <tfoot class="bg-[#f9f9fa] border-t border-[#cccccc]">
            <tr>
              <th class="py-3 pl-5 w-12"><input type="checkbox" v-model="selectAll" class="rounded border-[#8c8f94] text-[#007CBA] focus:ring-[#007CBA] cursor-pointer"></th>
              <th class="py-3 pl-3 font-semibold text-[13px] text-[#1e1e1e] min-w-[200px]">Username</th>
              <th class="py-3 font-semibold text-[13px] text-[#1e1e1e] min-w-[150px]">Nama</th>
              <th class="py-3 font-semibold text-[13px] text-[#1e1e1e] min-w-[150px]">Email</th>
              <th class="py-3 font-semibold text-[13px] text-[#1e1e1e] min-w-[120px]">Peran</th>
              <th class="py-3 pr-5 text-right font-semibold text-[13px] text-[#1e1e1e] min-w-[150px]">Aksi</th>
            </tr>
          </tfoot>
        </table>
      </div>
      
      <!-- Card Toolbar Bottom Outer -->
      <div v-if="activeTab === 'users_list'" class="px-5 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-[#f9f9fa] border-t border-[#f1f1f1]">
        <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
           <select class="w-auto flex-shrink-0 text-[13px] text-[#1e1e1e] bg-white border border-[#cccccc] rounded px-2.5 py-1.5 focus:border-[#007CBA] outline-none">
             <option>Tindakan Massal</option>
             <option>Hapus</option>
           </select>
           <button class="flex-shrink-0 border border-[#007CBA] text-[#007CBA] hover:bg-[#007CBA] hover:text-white rounded px-3 py-1.5 text-[13px] font-medium transition-colors">
             Terapkan
           </button>
        </div>
        <span class="text-[13px] text-[#757575] font-medium">{{ users.length }} item</span>
      </div>
    </div>

    <!-- Tambah Pengguna Form -->
    <div v-if="activeTab === 'add_user'">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
          <h2 class="font-sans text-[28px] md:text-[32px] font-bold text-[#1e1e1e] mb-2 leading-tight">Tambah Pengguna</h2>
          <p class="text-[13px] text-[#757575]">Buat pengguna baru dan tambahkan mereka ke situs ini.</p>
        </div>
        <div class="flex items-center gap-3">
          <button @click="emit('set-view', 'users_list')" class="btn-secondary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar
          </button>
        </div>
      </div>

      <div class="raabiha-card overflow-hidden p-6 md:p-8">
        
        <form @submit.prevent="saveUser" class="max-w-2xl space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-[13px] font-medium text-[#1e1e1e] md:text-right">Nama Pengguna (wajib)</label>
            <div class="md:col-span-2">
              <input type="text" v-model="newUser.username" required class="w-full sm:max-w-md bg-white border border-[#8c8f94] text-[#1e1e1e] px-3 py-1.5 text-[13px] focus:border-[#007CBA] focus:ring-1 focus:ring-[#007CBA] outline-none rounded">
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-[13px] font-medium text-[#1e1e1e] md:text-right">Email (wajib)</label>
            <div class="md:col-span-2">
              <input type="email" v-model="newUser.email" required class="w-full sm:max-w-md bg-white border border-[#8c8f94] text-[#1e1e1e] px-3 py-1.5 text-[13px] focus:border-[#007CBA] focus:ring-1 focus:ring-[#007CBA] outline-none rounded">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-[13px] font-medium text-[#1e1e1e] md:text-right">Nama Depan</label>
            <div class="md:col-span-2">
              <input type="text" v-model="newUser.first_name" class="w-full sm:max-w-md bg-white border border-[#8c8f94] text-[#1e1e1e] px-3 py-1.5 text-[13px] focus:border-[#007CBA] focus:ring-1 focus:ring-[#007CBA] outline-none rounded">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-[13px] font-medium text-[#1e1e1e] md:text-right">Nama Belakang</label>
            <div class="md:col-span-2">
              <input type="text" v-model="newUser.last_name" class="w-full sm:max-w-md bg-white border border-[#8c8f94] text-[#1e1e1e] px-3 py-1.5 text-[13px] focus:border-[#007CBA] focus:ring-1 focus:ring-[#007CBA] outline-none rounded">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
            <label class="text-[13px] font-medium text-[#1e1e1e] md:text-right md:mt-2">Kata Sandi (wajib)</label>
            <div class="md:col-span-2 space-y-2">
              <input type="text" v-model="newUser.password" required class="w-full sm:max-w-md bg-white border border-[#8c8f94] text-[#1e1e1e] px-3 py-1.5 text-[13px] focus:border-[#007CBA] focus:ring-1 focus:ring-[#007CBA] outline-none rounded">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <label class="text-[13px] font-medium text-[#1e1e1e] md:text-right">Peranan</label>
            <div class="md:col-span-2">
              <select v-model="newUser.roles[0]" class="w-full sm:max-w-xs bg-white border border-[#8c8f94] text-[#1e1e1e] px-2.5 py-1.5 text-[13px] focus:border-[#007CBA] focus:ring-1 focus:ring-[#007CBA] outline-none rounded">
                <option value="administrator">Administrator</option>
                <option value="raabiha_owner">Owner</option>
                <option value="raabiha_co_admin">Co-Administrator</option>
                <option value="raabiha_cashier">Admin Kasir</option>
                <option value="raabiha_blogger">Blogger</option>
                <option value="editor">Editor</option>
                <option value="customer">Pelanggan</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center pt-4">
            <div class="md:col-start-2 md:col-span-2">
              <button type="submit" :disabled="savingUser" class="btn-primary">
                {{ savingUser ? 'Menyimpan...' : 'Simpan Pengguna Baru' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>
