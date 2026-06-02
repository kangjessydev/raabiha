<script setup>
import { useToast } from '../composables/useToast'
import { useConfirm } from '../composables/useConfirm'

const { showToast } = useToast()
const { triggerConfirm } = useConfirm()

import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { Editor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'

const props = defineProps({
  config: { type: Object, required: true },
  initialView: { type: String, default: 'all_products' }
})
const emit = defineEmits(['set-view'])

const activeTab = ref(props.initialView)

// Watch for external view changes from App.vue sidebar
watch(() => props.initialView, (newVal) => {
  activeTab.value = newVal
  if (newVal === 'all_products') fetchProducts()
  else if (newVal === 'product_categories') fetchCategories()
  else if (newVal === 'product_tags') fetchTags()
  else if (newVal === 'product_attributes') fetchAttributes()
  else if (newVal === 'product_reviews') fetchReviews()
  else if (newVal === 'products') {
    resetForm()
    fetchCategories()
  }
})

// HTTP Client configured for WooCommerce REST API (Cookie Auth via Nonce)
const api = axios.create({
  baseURL: props.config.rest_url + 'wc/v3/',
  headers: {
    'X-WP-Nonce': props.config.rest_nonce
  }
})

// ── State: Categories ─────────────────────────────────────────
const categories = ref([])
const loadingCategories = ref(false)
const newCategoryName = ref('')

async function fetchCategories() {
  loadingCategories.value = true
  try {
    const res = await api.get('products/categories?per_page=100&hide_empty=false')
    categories.value = res.data
  } catch (err) {
    console.error('Error fetching categories', err)
  } finally {
    loadingCategories.value = false
  }
}

async function addCategory() {
  if (!newCategoryName.value.trim()) return
  try {
    await api.post('products/categories', { name: newCategoryName.value })
    newCategoryName.value = ''
    fetchCategories()
  } catch (err) {
    console.error('Error adding category', err)
    showToast('error', 'Gagal menambahkan kategori.')
  }
}

function deleteCategory(id) {
  triggerConfirm(
    'Hapus Kategori',
    'Apakah Anda yakin ingin menghapus kategori ini?',
    async () => {
      try {
        await api.delete(`products/categories/${id}?force=true`)
        fetchCategories()
      } catch (err) {
        console.error('Error deleting category', err)
        showToast('error', 'Gagal menghapus kategori.')
      }
    }
  )
}

// ── State: Tags ───────────────────────────────────────────────
const tags = ref([])
const loadingTags = ref(false)
const newTagName = ref('')

async function fetchTags() {
  loadingTags.value = true
  try {
    const res = await api.get('products/tags?per_page=100')
    tags.value = res.data
  } catch (err) {
    console.error('Error fetching tags', err)
  } finally {
    loadingTags.value = false
  }
}

async function addTag() {
  if (!newTagName.value.trim()) return
  try {
    await api.post('products/tags', { name: newTagName.value })
    newTagName.value = ''
    fetchTags()
  } catch (err) {
    console.error('Error adding tag', err)
    showToast('error', 'Gagal menambahkan tag.')
  }
}

function deleteTag(id) {
  triggerConfirm(
    'Hapus Tag',
    'Apakah Anda yakin ingin menghapus tag ini?',
    async () => {
      try {
        await api.delete(`products/tags/${id}?force=true`)
        fetchTags()
      } catch (err) {
        console.error('Error deleting tag', err)
        showToast('error', 'Gagal menghapus tag.')
      }
    }
  )
}

// ── State: Attributes ──────────────────────────────────────────
const attributes = ref([])
const loadingAttributes = ref(false)
const newAttributeName = ref('')

async function fetchAttributes() {
  loadingAttributes.value = true
  try {
    const res = await api.get('products/attributes')
    attributes.value = res.data
  } catch (err) {
    console.error('Error fetching attributes', err)
  } finally {
    loadingAttributes.value = false
  }
}

async function addAttribute() {
  if (!newAttributeName.value.trim()) return
  try {
    await api.post('products/attributes', { name: newAttributeName.value })
    newAttributeName.value = ''
    fetchAttributes()
  } catch (err) {
    console.error('Error adding attribute', err)
    showToast('error', 'Gagal menambahkan atribut.')
  }
}

function deleteAttribute(id) {
  triggerConfirm(
    'Hapus Atribut',
    'Apakah Anda yakin ingin menghapus atribut ini?',
    async () => {
      try {
        await api.delete(`products/attributes/${id}?force=true`)
        fetchAttributes()
      } catch (err) {
        console.error('Error deleting attribute', err)
        showToast('error', 'Gagal menghapus atribut.')
      }
    }
  )
}

// ── State: Reviews ──────────────────────────────────────────────
const reviews = ref([])
const loadingReviews = ref(false)

async function fetchReviews() {
  loadingReviews.value = true
  try {
    const res = await api.get('products/reviews')
    reviews.value = res.data
  } catch (err) {
    console.error('Error fetching reviews', err)
  } finally {
    loadingReviews.value = false
  }
}

function deleteReview(id) {
  triggerConfirm(
    'Hapus Ulasan',
    'Apakah Anda yakin ingin menghapus ulasan ini?',
    async () => {
      try {
        await api.delete(`products/reviews/${id}?force=true`)
        fetchReviews()
      } catch (err) {
        console.error('Error deleting review', err)
        showToast('error', 'Gagal menghapus ulasan.')
      }
    }
  )
}

async function approveReview(id, currentStatus) {
  const newStatus = currentStatus === 'approved' ? 'hold' : 'approved'
  try {
    await api.put(`products/reviews/${id}`, { status: newStatus })
    fetchReviews()
  } catch (err) {
    console.error('Error updating review status', err)
    showToast('error', 'Gagal memperbarui status ulasan.')
  }
}

// ── State: Products ───────────────────────────────────────────
const products = ref([])
const loadingProducts = ref(false)

const selectedProducts = ref([])
const selectAllProducts = computed({
  get: () => products.value.length > 0 && selectedProducts.value.length === products.value.length,
  set: (val) => {
    selectedProducts.value = val ? products.value.map(p => p.id) : []
  }
})

async function fetchProducts() {
  loadingProducts.value = true
  try {
    const res = await api.get('products?per_page=20')
    products.value = res.data
  } catch (err) {
    console.error('Error fetching products', err)
  } finally {
    loadingProducts.value = false
  }
}

function deleteProduct(id) {
  triggerConfirm(
    'Hapus Produk',
    'Apakah Anda yakin ingin menghapus produk ini?',
    async () => {
      try {
        await api.delete(`products/${id}?force=true`)
        fetchProducts()
      } catch (err) {
        console.error('Error deleting product', err)
        showToast('error', 'Gagal menghapus produk.')
      }
    }
  )
}

// ── State: Form (Add/Edit Product) ────────────────────────────
const form = ref({
  id: null,
  name: '',
  description: '',
  regular_price: '',
  sale_price: '',
  manage_stock: false,
  stock_quantity: '',
  status: 'publish',
  categories: []
})
const saving = ref(false)
let editor = null

function initEditor() {
  if (editor) editor.destroy()
  editor = new Editor({
    content: form.value.description,
    extensions: [ StarterKit ],
    editorProps: {
      attributes: {
        class: 'prose prose-sm  focus:outline-none min-h-[200px] p-4 text-[#1e1e1e]'
      }
    },
    onUpdate: ({ editor }) => {
      form.value.description = editor.getHTML()
    }
  })
}

function resetForm() {
  form.value = { 
    id: null, name: '', description: '', regular_price: '', sale_price: '', 
    manage_stock: false, stock_quantity: '', status: 'publish', categories: [] 
  }
  if (editor) editor.commands.setContent('')
}

function editProduct(prod) {
  form.value = {
    id: prod.id,
    name: prod.name,
    description: prod.description,
    regular_price: prod.regular_price,
    sale_price: prod.sale_price,
    manage_stock: prod.manage_stock,
    stock_quantity: prod.stock_quantity,
    status: prod.status,
    categories: prod.categories.map(c => c.id)
  }
  emit('set-view', 'products')
  setTimeout(() => {
    if (!editor) initEditor()
    else editor.commands.setContent(form.value.description)
  }, 100)
}

async function saveProduct() {
  if (!form.value.name.trim()) return showToast('error', 'Nama produk tidak boleh kosong')
  saving.value = true
  try {
    const payload = {
      name: form.value.name,
      description: form.value.description,
      regular_price: form.value.regular_price.toString(),
      sale_price: form.value.sale_price ? form.value.sale_price.toString() : '',
      manage_stock: form.value.manage_stock,
      stock_quantity: form.value.manage_stock ? parseInt(form.value.stock_quantity) || 0 : null,
      status: form.value.status,
      categories: form.value.categories.map(id => ({ id }))
    }

    if (form.value.id) {
      await api.put(`products/${form.value.id}`, payload)
    } else {
      await api.post('products', payload)
    }
    showToast('success', 'Produk berhasil disimpan!')
    emit('set-view', 'all_products')
  } catch (err) {
    console.error('Error saving product', err)
    showToast('error', 'Gagal menyimpan produk.')
  } finally {
    saving.value = false
  }
}

// ── Lifecycle ─────────────────────────────────────────────────
onMounted(() => {
  if (activeTab.value === 'all_products') fetchProducts()
  else if (activeTab.value === 'product_categories') fetchCategories()
  else if (activeTab.value === 'product_tags') fetchTags()
  else if (activeTab.value === 'product_attributes') fetchAttributes()
  else if (activeTab.value === 'product_reviews') fetchReviews()
  
  if (activeTab.value === 'products') {
    fetchCategories()
    initEditor()
  }
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6" v-if="activeTab === 'all_products'">
      <div>
        <h2 class="font-sans text-[28px] md:text-[32px] font-bold text-[#1e1e1e] mb-2 leading-tight">Manajemen Katalog Produk</h2>
        <p class="text-[13px] text-[#757575]">Kelola inventaris dan kategori WooCommerce secara native via REST API.</p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="fetchProducts" :disabled="loadingProducts" class="btn-secondary flex items-center gap-2">
          <svg class="w-4 h-4" :class="{'animate-spin': loadingProducts}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          Sync Data
        </button>
        <button @click="emit('set-view', 'products')" class="btn-primary flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
          Tambah Produk
        </button>
      </div>
    </div>
    <!-- Sub-headers for other tabs just in case -->
    <div class="flex items-center justify-between mb-6" v-if="activeTab !== 'all_products'">
      <div>
        <h2 class="font-sans text-[28px] md:text-[32px] font-bold text-[#1e1e1e] mb-2 leading-tight">Manajemen Katalog Produk</h2>
        <p class="text-[13px] text-[#757575]">Kelola inventaris dan kategori WooCommerce secara native via REST API.</p>
      </div>
    </div>

    <!-- ── View: List Products ── -->
    <div v-if="activeTab === 'all_products'" class="raabiha-card overflow-hidden">
      <!-- Card Tabs -->
      <div class="px-5 pt-4 border-b border-[#f1f1f1] flex gap-6 text-[13px] font-medium bg-white">
         <button class="text-[#007CBA] border-b-2 border-[#007CBA] pb-3 px-1">Semua <span class="text-[#757575] font-normal ml-1">({{ products.length }})</span></button>
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
           <select class="w-auto flex-shrink-0 text-[13px] text-[#1e1e1e] bg-white border border-[#cccccc] rounded px-2.5 py-1.5 focus:border-[#007CBA] outline-none">
             <option>Pilih Kategori</option>
           </select>
           <button class="flex-shrink-0 border border-[#cccccc] text-[#1e1e1e] hover:bg-[#f0f0f1] rounded px-3 py-1.5 text-[13px] font-medium transition-colors">
             Saring
           </button>
        </div>
        
        <div class="flex items-center gap-4 w-full sm:w-auto">
           <!-- Search -->
           <div class="relative w-full sm:w-48 flex items-center bg-white border border-[#cccccc] rounded px-2.5 py-1.5 focus-within:border-[#007CBA] focus-within:ring-1 focus-within:ring-[#007CBA] transition-all">
             <input type="text" placeholder="Cari Produk" class="bg-transparent border-none outline-none text-[13px] w-full placeholder-[#757575] text-[#1e1e1e] font-sans">
           </div>
           <!-- Item Count -->
           <span class="text-[13px] text-[#757575] font-medium whitespace-nowrap">{{ products.length }} item</span>
        </div>
      </div>

      <div v-if="loadingProducts" class="p-5 space-y-3">
        <div v-for="n in 3" :key="n" class="h-12 bg-[#E5E1D8] animate-pulse rounded-lg"></div>
      </div>
      
      <div v-else-if="products.length === 0" class="text-center py-10">
        <p class="text-[#757575] text-[13px]">Belum ada produk di etalase Anda.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-[13px]">
          <thead class="bg-[#f9f9fa] border-b border-[#cccccc]">
            <tr class="text-[#1e1e1e]">
              <th class="py-3 pl-5 w-12"><input type="checkbox" v-model="selectAllProducts" class="rounded border-[#8c8f94] text-[#007CBA] focus:ring-[#007CBA] cursor-pointer"></th>
              <th class="py-3 pl-3 font-semibold text-[13px] min-w-[200px]">Nama Produk</th>
              <th class="py-3 font-semibold text-[13px] min-w-[100px]">Harga</th>
              <th class="py-3 font-semibold text-[13px] min-w-[100px]">Stok</th>
              <th class="py-3 font-semibold text-[13px] min-w-[100px]">Status</th>
              <th class="py-3 pr-5 text-right font-semibold text-[13px] min-w-[120px]">Aksi</th>
            </tr>
          </thead>
          <tbody class="text-[#1e1e1e]">
            <tr v-for="prod in products" :key="prod.id" class="border-b border-[#f1f1f1] hover:bg-[#fcfcfc] transition-colors group">
              <td class="py-3 pl-5"><input type="checkbox" v-model="selectedProducts" :value="prod.id" class="rounded border-[#8c8f94] text-[#007CBA] focus:ring-[#007CBA] cursor-pointer"></td>
              <td class="py-3 pl-3 text-[13px] font-bold text-[#007CBA] cursor-pointer hover:underline">{{ prod.name }}</td>
              <td class="py-3 pl-3">
                <div v-if="prod.sale_price" class="flex flex-col">
                  <span class="text-[#1e1e1e] font-bold" v-html="prod.price_html"></span>
                </div>
                <div v-else v-html="prod.price_html"></div>
              </td>
              <td class="py-3 text-[13px] text-[#757575]">
                <span v-if="prod.manage_stock">{{ prod.stock_quantity }} unit</span>
                <span v-else class="text-[#007CBA]">In Stock</span>
              </td>
              <td class="py-3">
                <span class="px-2.5 py-1 rounded-xl text-[11px] font-medium border"
                      :class="prod.status === 'publish' ? 'bg-indigo-500/10 text-[#007CBA] border-indigo-500/20' : 'bg-[#fef3c7] text-[#92400e] border-[#fde68a]'">
                  {{ prod.status === 'publish' ? 'Published' : 'Draft' }}
                </span>
              </td>
              <td class="py-3 pr-5 text-right">
                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                  <a :href="prod.permalink" target="_blank" class="p-1.5 text-[#757575] hover:text-[#007CBA] hover:bg-blue-50 rounded-lg transition-colors" title="Lihat">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  </a>
                  <button @click="editProduct(prod)" class="p-1.5 text-[#757575] hover:text-[#1e1e1e] hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  </button>
                  <button @click="deleteProduct(prod.id)" class="p-1.5 text-[#757575] hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
          <tfoot class="bg-[#f9f9fa] border-t border-[#cccccc]">
            <tr>
              <th class="py-3 pl-5 w-12"><input type="checkbox" v-model="selectAllProducts" class="rounded border-[#8c8f94] text-[#007CBA] focus:ring-[#007CBA] cursor-pointer"></th>
              <th class="py-3 pl-3 font-semibold text-[13px] text-[#1e1e1e] min-w-[200px]">Nama Produk</th>
              <th class="py-3 font-semibold text-[13px] text-[#1e1e1e] min-w-[100px]">Harga</th>
              <th class="py-3 font-semibold text-[13px] text-[#1e1e1e] min-w-[100px]">Stok</th>
              <th class="py-3 font-semibold text-[13px] text-[#1e1e1e] min-w-[100px]">Status</th>
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
             <option>Edit</option>
             <option>Pindahkan ke Sampah</option>
           </select>
           <button class="flex-shrink-0 border border-[#007CBA] text-[#007CBA] hover:bg-[#007CBA] hover:text-white rounded px-3 py-1.5 text-[13px] font-medium transition-colors">
             Terapkan
           </button>
        </div>
        <span class="text-[13px] text-[#757575] font-medium">{{ products.length }} item</span>
      </div>
    </div>

    <!-- ── View: Add/Edit Product (Woo API) ── -->
    <div v-if="activeTab === 'products'" class="space-y-5">
      <div class="raabiha-card p-6">
        <h3 class="text-sm font-bold text-[#1e1e1e] mb-4">{{ form.id ? 'Edit Produk' : 'Tambah Produk Baru' }}</h3>
        
        <div class="space-y-5">
          <!-- Name & Status -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
              <label class="block text-xs font-bold text-[#757575] mb-1.5">Nama Produk</label>
              <input v-model="form.name" type="text" placeholder="Masukkan nama..." class="form-input text-sm w-full bg-white text-[#1e1e1e] border-[#e0e0e0] font-medium" />
            </div>
            <div>
              <label class="block text-xs font-bold text-[#757575] mb-1.5">Status Tampil</label>
              <select v-model="form.status" class="form-input text-xs w-full bg-white text-[#1e1e1e] border-[#e0e0e0]">
                <option value="publish">Publish ke Toko</option>
                <option value="draft">Simpan Draft</option>
              </select>
            </div>
          </div>

          <!-- Pricing & Stock -->
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-[#f0f0f1] rounded-xl border border-[#e0e0e0]">
            <div>
              <label class="block text-xs font-bold text-[#757575] mb-1.5">Harga Normal (Rp)</label>
              <input v-model="form.regular_price" type="number" placeholder="Contoh: 150000" class="form-input text-xs w-full bg-white text-[#1e1e1e] border-[#e0e0e0]" />
            </div>
            <div>
              <label class="block text-xs font-bold text-[#757575] mb-1.5">Harga Sale (Rp)</label>
              <input v-model="form.sale_price" type="number" placeholder="Opsional" class="form-input text-xs w-full bg-white text-[#1e1e1e] border-[#e0e0e0]" />
            </div>
            <div class="flex items-center pt-5">
              <label class="flex items-center gap-2 text-xs text-[#1e1e1e] cursor-pointer">
                <input v-model="form.manage_stock" type="checkbox" class="rounded border-[#e0e0e0] bg-white text-[#007CBA] focus:ring-[#007CBA]" />
                Kelola Stok
              </label>
            </div>
            <div v-if="form.manage_stock">
              <label class="block text-xs font-bold text-[#757575] mb-1.5">Jumlah Stok</label>
              <input v-model="form.stock_quantity" type="number" placeholder="0" class="form-input text-xs w-full bg-white text-[#1e1e1e] border-[#e0e0e0]" />
            </div>
          </div>

          <!-- Category -->
          <div>
            <label class="block text-xs font-bold text-[#757575] mb-1.5">Kategori Produk</label>
            <div class="relative flex items-center gap-2">
              <select v-model="form.categories" multiple class="form-input text-xs w-full bg-white text-[#1e1e1e] border-[#e0e0e0] h-20">
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
              <p class="absolute -bottom-5 left-0 text-[10px] text-[#757575]">Tahan CTRL/CMD untuk pilih multi-kategori.</p>
            </div>
          </div>

          <!-- Description Editor -->
          <div class="pt-4">
            <label class="block text-xs font-bold text-[#757575] mb-1.5">Deskripsi Panjang (Rich Text)</label>
            <div class="border border-[#e0e0e0] rounded-lg overflow-hidden bg-white">
              <!-- Toolbar -->
              <div class="bg-[#f0f0f1] border-b border-[#e0e0e0] p-2 flex items-center gap-1 flex-wrap" v-if="editor">
                <button @click="editor.chain().focus().toggleBold().run()" :class="{ 'bg-[#E5E1D8]': editor.isActive('bold') }" class="p-1.5 rounded hover:bg-[#E5E1D8] text-[#1e1e1e]" title="Bold">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"/></svg>
                </button>
                <button @click="editor.chain().focus().toggleItalic().run()" :class="{ 'bg-[#E5E1D8]': editor.isActive('italic') }" class="p-1.5 rounded hover:bg-[#E5E1D8] text-[#1e1e1e]" title="Italic">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </button>
                <div class="w-px h-4 bg-[#cccccc] mx-1"></div>
                <button @click="editor.chain().focus().toggleHeading({ level: 2 }).run()" :class="{ 'bg-[#E5E1D8]': editor.isActive('heading', { level: 2 }) }" class="px-2 py-1 text-[11px] font-bold rounded hover:bg-[#E5E1D8] text-[#1e1e1e]">H2</button>
                <button @click="editor.chain().focus().toggleHeading({ level: 3 }).run()" :class="{ 'bg-[#E5E1D8]': editor.isActive('heading', { level: 3 }) }" class="px-2 py-1 text-[11px] font-bold rounded hover:bg-[#E5E1D8] text-[#1e1e1e]">H3</button>
                <div class="w-px h-4 bg-[#cccccc] mx-1"></div>
                <button @click="editor.chain().focus().toggleBulletList().run()" :class="{ 'bg-[#E5E1D8]': editor.isActive('bulletList') }" class="p-1.5 rounded hover:bg-[#E5E1D8] text-[#1e1e1e]">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
              </div>
              <editor-content :editor="editor" class="w-full" />
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-3">
        <button @click="emit('set-view', 'all_products')" class="btn-secondary text-xs px-5 py-2">Batal</button>
        <button @click="saveProduct" :disabled="saving" class="btn-primary text-xs px-6 py-2 flex items-center gap-2">
          <svg v-if="saving" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 3v4"/></svg>
          {{ saving ? 'Menyimpan...' : 'Simpan Produk' }}
        </button>
      </div>
    </div>

    <!-- ── View: Categories ── -->
    <div v-if="activeTab === 'product_categories'" class="raabiha-card p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-[14px] font-bold text-[#1e1e1e]">Kategori Produk WooCommerce</h3>
        <button @click="fetchCategories" :disabled="loadingCategories" class="btn-secondary !py-1 !px-2.5 text-xs flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" :class="{'animate-spin': loadingCategories}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          Sync Kategori
        </button>
      </div>
      
      <div class="flex gap-3 mb-6">
        <input v-model="newCategoryName" @keyup.enter="addCategory" type="text" placeholder="Nama Kategori Baru" class="form-input text-xs flex-1 bg-white text-[#1e1e1e] border-[#e0e0e0]" />
        <button @click="addCategory" class="btn-primary text-xs px-4">Tambah Kategori</button>
      </div>

      <div v-if="loadingCategories" class="space-y-2">
        <div v-for="n in 2" :key="n" class="h-10 bg-[#E5E1D8] animate-pulse rounded"></div>
      </div>
      
      <table v-else class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="border-b border-[#e0e0e0] text-[#757575]">
            <th class="py-3 font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Nama Kategori</th>
            <th class="py-3 font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Jumlah Produk</th>
            <th class="py-3 pr-5 text-right font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#e0e0e0] text-[#1e1e1e]">
          <tr v-for="cat in categories" :key="cat.id" class="hover:hover:bg-white/50">
            <td class="py-3 font-medium">{{ cat.name }}</td>
            <td class="py-3 text-[#757575]">{{ cat.count }} item</td>
            <td class="py-3 pr-5 text-right">
              <button v-if="cat.slug !== 'uncategorized'" @click="deleteCategory(cat.id)" class="text-red-600 hover:text-red-800">Hapus</button>
              <span v-else class="text-[10px] text-[#949494]">Default</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ── View: Tags ── -->
    <div v-if="activeTab === 'product_tags'" class="raabiha-card p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-[14px] font-bold text-[#1e1e1e]">Tag Produk WooCommerce</h3>
        <button @click="fetchTags" :disabled="loadingTags" class="btn-secondary !py-1 !px-2.5 text-xs flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" :class="{'animate-spin': loadingTags}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          Sync Tag
        </button>
      </div>
      
      <div class="flex gap-3 mb-6">
        <input v-model="newTagName" @keyup.enter="addTag" type="text" placeholder="Nama Tag Baru" class="form-input text-xs flex-1 bg-white text-[#1e1e1e] border-[#e0e0e0]" />
        <button @click="addTag" class="btn-primary text-xs px-4">Tambah Tag</button>
      </div>

      <div v-if="loadingTags" class="space-y-2">
        <div v-for="n in 2" :key="n" class="h-10 bg-[#E5E1D8] animate-pulse rounded"></div>
      </div>
      
      <table v-else class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="border-b border-[#e0e0e0] text-[#757575]">
            <th class="py-3 font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Nama Tag</th>
            <th class="py-3 font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Slug</th>
            <th class="py-3 font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Jumlah Produk</th>
            <th class="py-3 pr-5 text-right font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#e0e0e0] text-[#1e1e1e]">
          <tr v-for="tag in tags" :key="tag.id" class="hover:hover:bg-white/50">
            <td class="py-3 font-medium">{{ tag.name }}</td>
            <td class="py-3 text-[#757575]">{{ tag.slug }}</td>
            <td class="py-3 text-[#757575]">{{ tag.count }} item</td>
            <td class="py-3 pr-5 text-right">
              <button @click="deleteTag(tag.id)" class="text-red-600 hover:text-red-800">Hapus</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ── View: Attributes ── -->
    <div v-if="activeTab === 'product_attributes'" class="raabiha-card p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-[14px] font-bold text-[#1e1e1e]">Atribut Produk WooCommerce</h3>
        <button @click="fetchAttributes" :disabled="loadingAttributes" class="btn-secondary !py-1 !px-2.5 text-xs flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" :class="{'animate-spin': loadingAttributes}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          Sync Atribut
        </button>
      </div>
      
      <div class="flex gap-3 mb-6">
        <input v-model="newAttributeName" @keyup.enter="addAttribute" type="text" placeholder="Nama Atribut Baru (mis: Warna, Ukuran)" class="form-input text-xs flex-1 bg-white text-[#1e1e1e] border-[#e0e0e0]" />
        <button @click="addAttribute" class="btn-primary text-xs px-4">Tambah Atribut</button>
      </div>

      <div v-if="loadingAttributes" class="space-y-2">
        <div v-for="n in 2" :key="n" class="h-10 bg-[#E5E1D8] animate-pulse rounded"></div>
      </div>
      
      <table v-else class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="border-b border-[#e0e0e0] text-[#757575]">
            <th class="py-3 font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Nama Atribut</th>
            <th class="py-3 font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Slug</th>
            <th class="py-3 font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Tipe</th>
            <th class="py-3 pr-5 text-right font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#e0e0e0] text-[#1e1e1e]">
          <tr v-for="attr in attributes" :key="attr.id" class="hover:hover:bg-white/50">
            <td class="py-3 font-medium">{{ attr.name }}</td>
            <td class="py-3 text-[#757575]">{{ attr.slug }}</td>
            <td class="py-3 text-[#757575]">{{ attr.type }}</td>
            <td class="py-3 pr-5 text-right">
              <button @click="deleteAttribute(attr.id)" class="text-red-600 hover:text-red-800">Hapus</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ── View: Reviews ── -->
    <div v-if="activeTab === 'product_reviews'" class="raabiha-card p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-[14px] font-bold text-[#1e1e1e]">Ulasan Produk WooCommerce</h3>
        <button @click="fetchReviews" :disabled="loadingReviews" class="btn-secondary !py-1 !px-2.5 text-xs flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" :class="{'animate-spin': loadingReviews}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          Sync Ulasan
        </button>
      </div>
      
      <div v-if="loadingReviews" class="space-y-2">
        <div v-for="n in 3" :key="n" class="h-14 bg-[#E5E1D8] animate-pulse rounded"></div>
      </div>
      
      <table v-else class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="border-b border-[#e0e0e0] text-[#757575]">
            <th class="py-3 font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Penulis</th>
            <th class="py-3 font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Ulasan</th>
            <th class="py-3 font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Rating</th>
            <th class="py-3 font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Status</th>
            <th class="py-3 pr-5 text-right font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#e0e0e0] text-[#1e1e1e]">
          <tr v-for="rev in reviews" :key="rev.id" class="hover:hover:bg-white/50">
            <td class="py-3 font-medium">{{ rev.reviewer }}<br><span class="text-[#757575] text-[10px]">{{ rev.reviewer_email }}</span></td>
            <td class="py-3 text-[#757575] max-w-xs" v-html="rev.review"></td>
            <td class="py-3 text-[#757575]">⭐ {{ rev.rating }}</td>
            <td class="py-3">
              <span :class="rev.status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">{{ rev.status }}</span>
            </td>
            <td class="py-3 text-right space-x-3">
              <button @click="approveReview(rev.id, rev.status)" class="text-[#007CBA] hover:text-[#135e96] font-medium">{{ rev.status === 'approved' ? 'Tahan' : 'Setujui' }}</button>
              <button @click="deleteReview(rev.id)" class="text-red-600 hover:text-red-800">Hapus</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>
