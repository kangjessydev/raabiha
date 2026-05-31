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
  initialView: { type: String, default: 'blog_posts' }
})
const emit = defineEmits(['set-view'])

const activeTab = ref(props.initialView)

// Watch for external view changes from App.vue sidebar
watch(() => props.initialView, (newVal) => {
  activeTab.value = newVal
  if (newVal === 'blog_posts') fetchPosts()
  else if (newVal === 'blog_categories') fetchCategories()
  else if (newVal === 'blog_tags') fetchTags()
  else if (newVal === 'add_blog_post') {
    resetForm()
    fetchCategories()
  }
})

// HTTP Client configured for WP REST API
const api = axios.create({
  baseURL: props.config.rest_url + 'wp/v2/',
  headers: {
    'X-WP-Nonce': props.config.rest_nonce
  }
})

// ── State: Posts ──────────────────────────────────────────────
const posts = ref([])
const loadingPosts = ref(false)

const selectedPosts = ref([])
const selectAllPosts = computed({
  get: () => posts.value.length > 0 && selectedPosts.value.length === posts.value.length,
  set: (val) => {
    selectedPosts.value = val ? posts.value.map(p => p.id) : []
  }
})

async function fetchPosts() {
  loadingPosts.value = true
  try {
    const res = await api.get('posts?_embed&per_page=20')
    posts.value = res.data
  } catch (err) {
    console.error('Error fetching posts', err)
  } finally {
    loadingPosts.value = false
  }
}

function deletePost(id) {
  triggerConfirm(
    'Hapus Artikel',
    'Apakah Anda yakin ingin menghapus artikel ini?',
    async () => {
      try {
        await api.delete(`posts/${id}`)
        fetchPosts()
      } catch (err) {
        console.error('Error deleting post', err)
        showToast('error', 'Gagal menghapus artikel.')
      }
    }
  )
}

// ── State: Categories ─────────────────────────────────────────
const categories = ref([])
const loadingCategories = ref(false)
const newCategoryName = ref('')

async function fetchCategories() {
  loadingCategories.value = true
  try {
    const res = await api.get('categories?hide_empty=false&per_page=100')
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
    await api.post('categories', { name: newCategoryName.value })
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
        // default category cannot be deleted easily, force=true for wp rest
        await api.delete(`categories/${id}?force=true`)
        fetchCategories()
      } catch (err) {
        console.error('Error deleting category', err)
        showToast('error', 'Gagal menghapus kategori. Kategori default tidak bisa dihapus.')
      }
    }
  )
}

// ── State: Tags ────────────────────────────────────────────────
const tags = ref([])
const loadingTags = ref(false)
const newTagName = ref('')

async function fetchTags() {
  loadingTags.value = true
  try {
    const res = await api.get('tags?hide_empty=false&per_page=100')
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
    await api.post('tags', { name: newTagName.value })
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
        await api.delete(`tags/${id}?force=true`)
        fetchTags()
      } catch (err) {
        console.error('Error deleting tag', err)
        showToast('error', 'Gagal menghapus tag.')
      }
    }
  )
}

// ── State: Form (Add/Edit Post) ───────────────────────────────
const form = ref({
  id: null,
  title: '',
  content: '',
  status: 'publish',
  categories: []
})
const saving = ref(false)
let editor = null

function initEditor() {
  if (editor) editor.destroy()
  editor = new Editor({
    content: form.value.content,
    extensions: [ StarterKit ],
    editorProps: {
      attributes: {
        class: 'prose prose-sm  focus:outline-none min-h-[250px] p-4 text-[#1e1e1e]'
      }
    },
    onUpdate: ({ editor }) => {
      form.value.content = editor.getHTML()
    }
  })
}

function resetForm() {
  form.value = { id: null, title: '', content: '', status: 'publish', categories: [] }
  if (editor) editor.commands.setContent('')
}

function editPost(post) {
  form.value = {
    id: post.id,
    title: post.title.rendered,
    content: post.content.rendered,
    status: post.status,
    categories: post.categories || []
  }
  emit('set-view', 'add_blog_post')
  setTimeout(() => {
    if (!editor) initEditor()
    else editor.commands.setContent(form.value.content)
  }, 100)
}

async function savePost() {
  if (!form.value.title.trim()) return showToast('error', 'Judul tidak boleh kosong')
  saving.value = true
  try {
    const payload = {
      title: form.value.title,
      content: form.value.content,
      status: form.value.status,
      categories: form.value.categories
    }

    if (form.value.id) {
      await api.post(`posts/${form.value.id}`, payload)
    } else {
      await api.post('posts', payload)
    }
    showToast('success', 'Artikel berhasil disimpan!')
    emit('set-view', 'blog_posts')
  } catch (err) {
    console.error('Error saving post', err)
    showToast('error', 'Gagal menyimpan artikel.')
  } finally {
    saving.value = false
  }
}

// ── Lifecycle ─────────────────────────────────────────────────
onMounted(() => {
  if (activeTab.value === 'blog_posts') fetchPosts()
  else if (activeTab.value === 'blog_categories') fetchCategories()
  else if (activeTab.value === 'blog_tags') fetchTags()
  
  if (activeTab.value === 'add_blog_post') {
    fetchCategories()
    initEditor()
  }
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6" v-if="activeTab === 'blog_posts'">
      <div>
        <h2 class="font-sans text-[28px] md:text-[32px] font-bold text-[#1e1e1e] mb-2 leading-tight">Manajemen Blog & Artikel</h2>
        <p class="text-[13px] text-[#757575]">Kelola konten berita dan artikel edukasi Anda melalui native REST API WordPress.</p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="fetchPosts" :disabled="loadingPosts" class="btn-secondary flex items-center gap-2">
          <svg class="w-4 h-4" :class="{'animate-spin': loadingPosts}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          Sync Data
        </button>
        <button @click="emit('set-view', 'add_blog_post')" class="btn-primary flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
          Tulis Artikel
        </button>
      </div>
    </div>
    <!-- Sub-headers for other tabs just in case -->
    <div class="flex items-center justify-between mb-6" v-if="activeTab !== 'blog_posts'">
      <div>
        <h2 class="font-sans text-[28px] md:text-[32px] font-bold text-[#1e1e1e] mb-2 leading-tight">Manajemen Blog & Artikel</h2>
        <p class="text-[13px] text-[#757575]">Kelola konten berita dan artikel edukasi Anda melalui native REST API WordPress.</p>
      </div>
    </div>

    <!-- ── View: List Posts ── -->
    <div v-if="activeTab === 'blog_posts'" class="raabiha-card overflow-hidden">
      <!-- Card Tabs -->
      <div class="px-5 pt-4 border-b border-[#f1f1f1] flex gap-6 text-[13px] font-medium bg-white">
         <button class="text-[#007CBA] border-b-2 border-[#007CBA] pb-3 px-1">Semua <span class="text-[#757575] font-normal ml-1">({{ posts.length }})</span></button>
         <button class="text-[#757575] hover:text-[#1e1e1e] pb-3 px-1 transition-colors">Telah Terbit</button>
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
             <option>Seluruh Kategori</option>
             <option>Tak Berkategori</option>
           </select>
           <button class="flex-shrink-0 border border-[#cccccc] text-[#1e1e1e] hover:bg-[#f0f0f1] rounded px-3 py-1.5 text-[13px] font-medium transition-colors">
             Saring
           </button>
        </div>
        
        <div class="flex items-center gap-4 w-full sm:w-auto">
           <!-- Search -->
           <div class="relative w-full sm:w-48 flex items-center bg-white border border-[#cccccc] rounded px-2.5 py-1.5 focus-within:border-[#007CBA] focus-within:ring-1 focus-within:ring-[#007CBA] transition-all">
             <input type="text" placeholder="Cari Pos" class="bg-transparent border-none outline-none text-[13px] w-full placeholder-[#757575] text-[#1e1e1e] font-sans">
           </div>
           <!-- Item Count -->
           <span class="text-[13px] text-[#757575] font-medium whitespace-nowrap">{{ posts.length }} item</span>
        </div>
      </div>

      <div v-if="loadingPosts" class="p-5 space-y-3">
        <div v-for="n in 3" :key="n" class="h-12 bg-[#E5E1D8] animate-pulse rounded-lg"></div>
      </div>
      
      <div v-else-if="posts.length === 0" class="text-center py-10">
        <p class="text-[#757575] text-[13px]">Belum ada artikel yang diterbitkan.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-[13px]">
          <thead class="bg-[#f9f9fa] border-b border-[#cccccc]">
            <tr class="text-[#1e1e1e]">
              <th class="py-3 pl-5 w-12"><input type="checkbox" v-model="selectAllPosts" class="rounded border-[#8c8f94] text-[#007CBA] focus:ring-[#007CBA] cursor-pointer"></th>
              <th class="py-3 pl-3 font-semibold text-[13px] min-w-[200px]">Judul</th>
              <th class="py-3 font-semibold text-[13px] min-w-[100px]">Penulis</th>
              <th class="py-3 font-semibold text-[13px] min-w-[120px]">Kategori</th>
              <th class="py-3 font-semibold text-[13px] min-w-[120px]">Tanggal</th>
              <th class="py-3 pr-5 text-right font-semibold text-[13px] min-w-[150px]">Aksi</th>
            </tr>
          </thead>
          <tbody class="text-[#1e1e1e]">
            <tr v-for="post in posts" :key="post.id" class="border-b border-[#f1f1f1] hover:bg-[#fcfcfc] transition-colors group">
              <td class="py-3 pl-5"><input type="checkbox" v-model="selectedPosts" :value="post.id" class="rounded border-[#8c8f94] text-[#007CBA] focus:ring-[#007CBA] cursor-pointer"></td>
              <td class="py-3 pl-3 text-[13px] font-bold text-[#007CBA] pr-4 hover:underline cursor-pointer" v-html="post.title.rendered || '(Tanpa Judul)'"></td>
              <td class="py-3 text-[13px] text-[#007CBA] hover:underline cursor-pointer">admin</td>
              <td class="py-3 text-[13px] text-[#007CBA] hover:underline cursor-pointer">Tak Berkategori</td>
              <td class="py-3 text-[13px] text-[#757575]">
                <div class="font-medium text-[#1e1e1e]">{{ post.status === 'publish' ? 'Telah Terbit' : 'Draft' }}</div>
                <div class="text-[11px]">{{ new Date(post.date).toLocaleDateString('id-ID', {year: 'numeric', month: '2-digit', day: '2-digit'}) }}</div>
              </td>
              <td class="py-3 pr-5 text-right">
                <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                  <a :href="post.link" target="_blank" class="p-1.5 text-[#757575] hover:text-[#007CBA] hover:bg-blue-50 rounded-lg transition-colors" title="Lihat">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  </a>
                  <button @click="editPost(post)" class="p-1.5 text-[#757575] hover:text-[#1e1e1e] hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  </button>
                  <button @click="deletePost(post.id)" class="p-1.5 text-[#757575] hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
          <!-- Card Toolbar Bottom inside table wrapper -->
          <tfoot class="bg-[#f9f9fa] border-t border-[#cccccc]">
            <tr>
              <th class="py-3 pl-5 w-12"><input type="checkbox" v-model="selectAllPosts" class="rounded border-[#8c8f94] text-[#007CBA] focus:ring-[#007CBA] cursor-pointer"></th>
              <th class="py-3 pl-3 font-semibold text-[13px] text-[#1e1e1e] min-w-[200px]">Judul</th>
              <th class="py-3 font-semibold text-[13px] text-[#1e1e1e] min-w-[100px]">Penulis</th>
              <th class="py-3 font-semibold text-[13px] text-[#1e1e1e] min-w-[120px]">Kategori</th>
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
        <span class="text-[13px] text-[#757575] font-medium">{{ posts.length }} item</span>
      </div>
    </div>

    <!-- ── View: Add/Edit Post (Tiptap) ── -->
    <div v-if="activeTab === 'add_blog_post'" class="space-y-5">
      <div class="raabiha-card p-6">
        <h3 class="text-sm font-bold text-[#1e1e1e] mb-4">{{ form.id ? 'Edit Artikel' : 'Tulis Artikel Baru' }}</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-[#757575] mb-1.5">Judul Artikel</label>
            <input v-model="form.title" type="text" placeholder="Masukkan judul..." class="form-input text-sm w-full bg-white text-[#1e1e1e] border-[#e0e0e0] font-medium" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-[#757575] mb-1.5">Status Publikasi</label>
              <select v-model="form.status" class="form-input text-xs w-full bg-white text-[#1e1e1e] border-[#e0e0e0]">
                <option value="publish">Publish Langsung</option>
                <option value="draft">Simpan sebagai Draft</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-[#757575] mb-1.5">Kategori Blog</label>
              <div class="relative flex items-center gap-2">
                <select v-model="form.categories" multiple class="form-input text-xs w-full bg-white text-[#1e1e1e] border-[#e0e0e0] h-20">
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
                <p class="absolute -bottom-5 left-0 text-[10px] text-[#757575]">Tahan CTRL/CMD untuk pilih lebih dari satu.</p>
              </div>
            </div>
          </div>

          <!-- Tiptap Editor UI -->
          <div class="pt-4">
            <label class="block text-xs font-bold text-[#757575] mb-1.5">Konten Artikel</label>
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
              <!-- Editor Content -->
              <editor-content :editor="editor" class="w-full" />
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end gap-3">
        <button @click="emit('set-view', 'blog_posts')" class="btn-secondary text-xs px-5 py-2">Batal</button>
        <button @click="savePost" :disabled="saving" class="btn-primary text-xs px-6 py-2 flex items-center gap-2">
          <svg v-if="saving" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 3v4"/></svg>
          {{ saving ? 'Menyimpan...' : 'Simpan Artikel' }}
        </button>
      </div>
    </div>

    <!-- ── View: Categories ── -->
    <div v-if="activeTab === 'blog_categories'" class="raabiha-card p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-[14px] font-bold text-[#1e1e1e]">Kategori Blog</h3>
        <button @click="fetchCategories" :disabled="loadingCategories" class="btn-secondary !py-1 !px-2.5 text-xs flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" :class="{'animate-spin': loadingCategories}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          Sync Kategori
        </button>
      </div>
      
      <div class="flex gap-3 mb-6">
        <input v-model="newCategoryName" @keyup.enter="addCategory" type="text" placeholder="Nama Kategori Baru" class="form-input text-xs flex-1 bg-white text-[#1e1e1e] border-[#e0e0e0]" />
        <button @click="addCategory" class="btn-primary text-xs px-4">Tambah</button>
      </div>

      <div v-if="loadingCategories" class="space-y-2">
        <div v-for="n in 2" :key="n" class="h-10 bg-[#E5E1D8] animate-pulse rounded"></div>
      </div>
      
      <table v-else class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="border-b border-[#e0e0e0] text-[#757575]">
            <th class="py-3 font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Nama Kategori</th>
            <th class="py-3 font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Slug</th>
            <th class="py-3 pr-5 text-right font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#e0e0e0] text-[#1e1e1e]">
          <tr v-for="cat in categories" :key="cat.id" class="hover:hover:bg-white/50">
            <td class="py-3 font-medium">{{ cat.name }}</td>
            <td class="py-3 text-[#757575]">{{ cat.slug }}</td>
            <td class="py-3 pr-5 text-right">
              <button v-if="cat.id !== 1" @click="deleteCategory(cat.id)" class="text-red-600 hover:text-red-800">Hapus</button>
              <span v-else class="text-[10px] text-[#949494]">Default</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ── View: Tags ── -->
    <div v-if="activeTab === 'blog_tags'" class="raabiha-card p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-[14px] font-bold text-[#1e1e1e]">Tag Artikel</h3>
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
            <th class="py-3 pr-5 text-right font-semibold text-[#757575] uppercase tracking-wider text-[11px]">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#e0e0e0] text-[#1e1e1e]">
          <tr v-for="tag in tags" :key="tag.id" class="hover:hover:bg-white/50">
            <td class="py-3 font-medium">{{ tag.name }}</td>
            <td class="py-3 text-[#757575]">{{ tag.slug }}</td>
            <td class="py-3 pr-5 text-right">
              <button @click="deleteTag(tag.id)" class="text-red-600 hover:text-red-800">Hapus</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>

<style>
/* Tiptap default prose overrides for dark mode */
.ProseMirror p.is-editor-empty:first-child::before {
  content: attr(data-placeholder);
  float: left;
  color: #64748b;
  pointer-events: none;
  height: 0;
}
.ProseMirror h2 { font-size: 1.5em; font-weight: bold; margin-top: 1em; margin-bottom: 0.5em; color: #f1f5f9; }
.ProseMirror h3 { font-size: 1.17em; font-weight: bold; margin-top: 1em; margin-bottom: 0.5em; color: #f1f5f9; }
.ProseMirror ul { list-style-type: disc; padding-left: 1.5em; margin-bottom: 1em; }
.ProseMirror strong { color: #f8fafc; font-weight: 700; }
.ProseMirror em { font-style: italic; }
</style>
