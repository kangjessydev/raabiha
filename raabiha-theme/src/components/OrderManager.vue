<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  config: { type: Object, required: true },
  initialView: { type: String, default: 'orders' }
})
const emit = defineEmits(['set-view'])

const activeTab = ref(props.initialView)

watch(() => props.initialView, (newVal) => {
  activeTab.value = newVal
  if (newVal === 'orders') fetchOrders()
})

const api = axios.create({
  baseURL: props.config.rest_url + 'wc/v3/',
  headers: {
    'X-WP-Nonce': props.config.rest_nonce
  }
})

const orders = ref([])
const loadingOrders = ref(false)

const selectedOrders = ref([])
const selectAllOrders = computed({
  get: () => orders.value.length > 0 && selectedOrders.value.length === orders.value.length,
  set: (val) => {
    selectedOrders.value = val ? orders.value.map(o => o.id) : []
  }
})

async function fetchOrders() {
  loadingOrders.value = true
  try {
    const res = await api.get('orders?per_page=20')
    orders.value = res.data
  } catch (err) {
    console.error('Error fetching orders via wc/v3, falling back to wp/v2/orders custom endpoint or mock if needed', err)
    // Fallback if /wc/v3 needs consumer key or doesn't support nonce
    orders.value = []
  } finally {
    loadingOrders.value = false
  }
}

onMounted(() => {
  if (activeTab.value === 'orders') fetchOrders()
})

function getStatusClass(status) {
  const map = {
    completed: 'bg-[#dcfce7] text-[#166534]',
    processing: 'bg-[#e0e0e0] text-[#525252]',
    pending: 'bg-[#fef3c7] text-[#92400e]',
    on_hold: 'bg-[#fef3c7] text-[#92400e]',
    cancelled: 'bg-[#fee2e2] text-[#b91c1c]',
    refunded: 'bg-[#f3f4f6] text-[#374151]',
    failed: 'bg-[#fee2e2] text-[#b91c1c]'
  }
  return map[status] || 'bg-gray-100 text-gray-800'
}

function formatDate(dateString) {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric',
    hour: '2-digit', minute:'2-digit'
  })
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount)
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6" v-if="activeTab === 'orders'">
      <div>
        <h2 class="font-sans text-[28px] md:text-[32px] font-bold text-[#1e1e1e] mb-2 leading-tight">Manajemen Pesanan</h2>
        <p class="text-[13px] text-[#757575]">Kelola semua transaksi penjualan dari WooCommerce.</p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="fetchOrders" :disabled="loadingOrders" class="btn-secondary flex items-center gap-2">
          <svg class="w-4 h-4" :class="{'animate-spin': loadingOrders}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          Sync Pesanan
        </button>
      </div>
    </div>
    <!-- Sub-headers for other tabs just in case -->
    <div class="flex items-center justify-between mb-6" v-if="activeTab !== 'orders'">
      <div>
        <h2 class="font-sans text-[28px] md:text-[32px] font-bold text-[#1e1e1e] mb-2 leading-tight">Manajemen Pesanan</h2>
        <p class="text-[13px] text-[#757575]">Kelola semua transaksi penjualan dari WooCommerce.</p>
      </div>
    </div>

    <!-- ── View: List Orders ── -->
    <div v-if="activeTab === 'orders'" class="raabiha-card overflow-hidden">
      <!-- Card Tabs -->
      <div class="px-5 pt-4 border-b border-[#f1f1f1] flex gap-6 text-[13px] font-medium bg-white">
         <button class="text-[#007CBA] border-b-2 border-[#007CBA] pb-3 px-1">Semua <span class="text-[#757575] font-normal ml-1">({{ orders.length }})</span></button>
         <button class="text-[#757575] hover:text-[#1e1e1e] pb-3 px-1 transition-colors">Processing</button>
         <button class="text-[#757575] hover:text-[#1e1e1e] pb-3 px-1 transition-colors">Completed</button>
      </div>

      <!-- Card Toolbar Top -->
      <div class="px-5 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border-b border-[#f1f1f1]">
        <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto pb-2 sm:pb-0">
           <!-- Bulk Action -->
           <select class="w-auto flex-shrink-0 text-[13px] text-[#1e1e1e] bg-white border border-[#cccccc] rounded px-2.5 py-1.5 focus:border-[#007CBA] outline-none">
             <option>Tindakan Massal</option>
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
           <select class="w-auto flex-shrink-0 text-[13px] text-[#1e1e1e] bg-white border border-[#cccccc] rounded px-2.5 py-1.5 focus:border-[#007CBA] outline-none">
             <option>Filter by registered customer</option>
           </select>
           <button class="flex-shrink-0 border border-[#cccccc] text-[#1e1e1e] hover:bg-[#f0f0f1] rounded px-3 py-1.5 text-[13px] font-medium transition-colors">
             Saring
           </button>
        </div>
        
        <div class="flex items-center gap-4 w-full sm:w-auto">
           <!-- Search -->
           <div class="relative w-full sm:w-48 flex items-center bg-white border border-[#cccccc] rounded px-2.5 py-1.5 focus-within:border-[#007CBA] focus-within:ring-1 focus-within:ring-[#007CBA] transition-all">
             <input type="text" placeholder="Cari Pesanan" class="bg-transparent border-none outline-none text-[13px] w-full placeholder-[#757575] text-[#1e1e1e] font-sans">
           </div>
           <!-- Item Count -->
           <span class="text-[13px] text-[#757575] font-medium whitespace-nowrap">{{ orders.length }} item</span>
        </div>
      </div>

      <div v-if="loadingOrders" class="p-5 space-y-3">
        <div v-for="n in 3" :key="n" class="h-12 bg-[#E5E1D8] animate-pulse rounded-lg"></div>
      </div>
      
      <div v-else-if="orders.length === 0" class="text-center py-10">
        <p class="text-[#757575] text-[13px]">Belum ada pesanan yang masuk atau REST API tidak dapat diakses.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-[13px]">
          <thead class="bg-[#f9f9fa] border-b border-[#cccccc]">
            <tr class="text-[#1e1e1e]">
              <th class="py-3 pl-5 w-12"><input type="checkbox" v-model="selectAllOrders" class="rounded border-[#8c8f94] text-[#007CBA] focus:ring-[#007CBA] cursor-pointer"></th>
              <th class="py-3 pl-3 font-semibold text-[13px] min-w-[150px]">Pesanan</th>
              <th class="py-3 font-semibold text-[13px] min-w-[120px]">Tanggal</th>
              <th class="py-3 font-semibold text-[13px] min-w-[120px]">Status</th>
              <th class="py-3 font-semibold text-[13px] min-w-[120px]">Total</th>
              <th class="py-3 pr-5 text-right font-semibold text-[13px] min-w-[120px]">Aksi</th>
            </tr>
          </thead>
          <tbody class="text-[#1e1e1e]">
            <tr v-for="order in orders" :key="order.id" class="border-b border-[#f1f1f1] hover:bg-[#fcfcfc] transition-colors group">
              <td class="py-3 pl-5"><input type="checkbox" v-model="selectedOrders" :value="order.id" class="rounded border-[#8c8f94] text-[#007CBA] focus:ring-[#007CBA] cursor-pointer"></td>
              <td class="py-3 pl-3 text-[13px] pr-4 font-bold text-[#007CBA] max-w-[200px] truncate hover:underline cursor-pointer">
                #{{ order.id }} {{ order.billing.first_name }} {{ order.billing.last_name }}
              </td>
              <td class="py-3 text-[13px] text-[#757575]">
                <div class="text-[11px]">{{ formatDate(order.date_created) }}</div>
              </td>
              <td class="py-3">
                <span class="px-2 py-1 rounded-xl text-[11px] font-bold uppercase tracking-wider border" :class="getStatusClass(order.status) + ' bg-opacity-10 border-opacity-20'">
                  {{ order.status }}
                </span>
              </td>
              <td class="py-3 text-[13px] font-bold">{{ formatCurrency(order.total) }}</td>
              <td class="py-3 pr-5 text-right">
                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                  <a href="#" class="p-1.5 text-[#757575] hover:text-[#007CBA] hover:bg-blue-50 rounded-lg transition-colors" title="Lihat Detail">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  </a>
                </div>
              </td>
            </tr>
          </tbody>
          <tfoot class="bg-[#f9f9fa] border-t border-[#cccccc]">
            <tr>
              <th class="py-3 pl-5 w-12"><input type="checkbox" v-model="selectAllOrders" class="rounded border-[#8c8f94] text-[#007CBA] focus:ring-[#007CBA] cursor-pointer"></th>
              <th class="py-3 pl-3 font-semibold text-[13px] text-[#1e1e1e] min-w-[150px]">Pesanan</th>
              <th class="py-3 font-semibold text-[13px] text-[#1e1e1e] min-w-[120px]">Tanggal</th>
              <th class="py-3 font-semibold text-[13px] text-[#1e1e1e] min-w-[120px]">Status</th>
              <th class="py-3 font-semibold text-[13px] text-[#1e1e1e] min-w-[120px]">Total</th>
              <th class="py-3 pr-5 text-right font-semibold text-[13px] text-[#1e1e1e] min-w-[120px]">Aksi</th>
            </tr>
          </tfoot>
        </table>
      </div>
      
      <!-- Card Toolbar Bottom Outer -->
      <div class="px-5 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-[#f9f9fa] border-t border-[#f1f1f1]">
        <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
           <select class="w-auto flex-shrink-0 text-[13px] text-[#1e1e1e] bg-white border border-[#cccccc] rounded px-2.5 py-1.5 focus:border-[#007CBA] outline-none">
             <option>Tindakan Massal</option>
             <option>Pindahkan ke Sampah</option>
           </select>
           <button class="flex-shrink-0 border border-[#007CBA] text-[#007CBA] hover:bg-[#007CBA] hover:text-white rounded px-3 py-1.5 text-[13px] font-medium transition-colors">
             Terapkan
           </button>
        </div>
        <span class="text-[13px] text-[#757575] font-medium">{{ orders.length }} item</span>
      </div>
    </div>
  </div>
</template>
