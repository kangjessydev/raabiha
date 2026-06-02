<script setup>
/**
 * ProductForm.vue — Raabiha Dashboard
 * Premium product creation form with real-time SEO analysis.
 * Mimics Laravel Filament's dark card UI aesthetic.
 *
 * Features:
 * - Real-time meta description character counter (120–160 optimal range)
 * - Focus keyword presence check inside product name
 * - Axios POST to WP admin-ajax.php with nonce authentication
 * - Success/error toast notification system
 */
import { ref, computed } from 'vue'
import axios from 'axios'

// ── Props ──────────────────────────────────────────────────────
const props = defineProps({
  config: {
    type: Object,
    default: () => window.RaabihaConfig || {}
  }
})

// ── Emit ───────────────────────────────────────────────────────
const emit = defineEmits(['product-saved'])

// ── Form State ─────────────────────────────────────────────────
const form = ref({
  product_name:  '',
  price:         '',
  description:   '',
  focus_keyword: '',
  meta_desc:     ''
})

// ── UI State ───────────────────────────────────────────────────
const isSubmitting  = ref(false)
const toast         = ref({ show: false, type: 'success', message: '' })
const savedProduct  = ref(null)

// ── SEO Analysis — Meta Description ────────────────────────────
const metaDescLength = computed(() => form.value.meta_desc.length)

const metaDescStatus = computed(() => {
  const len = metaDescLength.value
  if (len === 0)          return 'neutral'
  if (len < 50)           return 'error'
  if (len >= 120 && len <= 160) return 'good'
  if (len > 160 && len <= 320)  return 'warning'
  if (len > 320)          return 'error'
  return 'warning' // 50–119
})

const metaDescLabel = computed(() => {
  const s = metaDescStatus.value
  const len = metaDescLength.value
  if (s === 'neutral') return 'Belum diisi'
  if (s === 'error' && len < 50)  return 'Terlalu pendek'
  if (s === 'error' && len > 320) return 'Terlalu panjang'
  if (s === 'good')    return 'Optimal ✓'
  if (s === 'warning' && len < 120) return `Perlu +${120 - len} karakter`
  if (s === 'warning' && len > 160) return `${len - 160} karakter berlebih`
  return 'Baik'
})

const metaDescProgress = computed(() => {
  const len = metaDescLength.value
  if (len === 0) return 0
  // Fill bar from 0 → 320 chars, highlight 120–160 as 'full'
  return Math.min(100, Math.round((len / 320) * 100))
})

const metaDescProgressColor = computed(() => {
  const s = metaDescStatus.value
  if (s === 'good')    return 'bg-emerald-500'
  if (s === 'warning') return 'bg-amber-500'
  if (s === 'error')   return 'bg-red-500'
  return 'bg-[#cccccc]'
})

// ── SEO Analysis — Focus Keyword in Title ──────────────────────
const keywordInTitle = computed(() => {
  const kw    = form.value.focus_keyword.trim().toLowerCase()
  const title = form.value.product_name.trim().toLowerCase()
  if (!kw || !title) return null
  return title.includes(kw)
})

const keywordStatus = computed(() => {
  if (keywordInTitle.value === null) return 'neutral'
  return keywordInTitle.value ? 'good' : 'warning'
})

const keywordLabel = computed(() => {
  if (keywordInTitle.value === null)   return 'Isi nama produk & keyword'
  if (keywordInTitle.value === true)   return 'Keyword ditemukan di judul ✓'
  return 'Keyword tidak ada di judul'
})

// ── Form Validation ────────────────────────────────────────────
const formErrors = computed(() => {
  const errors = {}
  if (!form.value.product_name.trim()) errors.product_name = 'Nama produk wajib diisi.'
  const p = parseFloat(form.value.price)
  if (!form.value.price || isNaN(p) || p < 0) errors.price = 'Harga harus berupa angka valid.'
  return errors
})

const isFormValid = computed(() => Object.keys(formErrors.value).length === 0)

// ── Toast Helper ───────────────────────────────────────────────
function showToast(type, message) {
  toast.value = { show: true, type, message }
  setTimeout(() => { toast.value.show = false }, 4000)
}

// ── Form Submit ────────────────────────────────────────────────
async function handleSubmit() {
  if (!isFormValid.value || isSubmitting.value) return

  isSubmitting.value = true

  const cfg      = props.config
  const ajaxUrl  = cfg.ajax_url || '/wp-admin/admin-ajax.php'
  const nonce    = cfg.nonce    || ''

  const payload = new URLSearchParams({
    action:        'raabiha_save_product',
    nonce:         nonce,
    product_name:  form.value.product_name,
    price:         form.value.price,
    description:   form.value.description,
    focus_keyword: form.value.focus_keyword,
    meta_desc:     form.value.meta_desc,
  })

  try {
    const { data } = await axios.post(ajaxUrl, payload, {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })

    if (data.success) {
      savedProduct.value = data.data
      showToast('success', data.data.message || 'Produk berhasil disimpan!')
      emit('product-saved', data.data)
      resetForm()
    } else {
      showToast('error', data.data?.message || 'Gagal menyimpan produk.')
    }
  } catch (err) {
    const msg = err.response?.data?.data?.message || err.message || 'Terjadi kesalahan jaringan.'
    showToast('error', msg)
  } finally {
    isSubmitting.value = false
  }
}

function resetForm() {
  form.value = { product_name: '', price: '', description: '', focus_keyword: '', meta_desc: '' }
}
</script>

<template>
  <div class="raabiha-card p-6 md:p-8 animate-[fadeIn_0.3s_ease-in-out]">

    <!-- ── Card Header ── -->
    <div class="flex items-center gap-3 mb-8 pb-6 border-b border-[#e0e0e0]">
      <div class="w-10 h-10 rounded-lg bg-white border border-[#cccccc] flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-[#007CBA]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
      </div>
      <div>
        <h2 class="text-base font-bold text-[#1e1e1e]">Tambah Produk Baru</h2>
        <p class="text-xs text-[#757575] mt-0.5">Data akan disimpan ke WooCommerce dan dioptimasi untuk SEO</p>
      </div>
    </div>

    <!-- ── Toast Notification ── -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      leave-active-class="transition-all duration-200 ease-in"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div
        v-if="toast.show"
        :class="[
          'flex items-start gap-3 px-4 py-3 rounded-lg border mb-6 text-sm',
          toast.type === 'success'
            ? 'bg-emerald-500/10 border-emerald-500/30 text-[#007CBA]'
            : 'bg-red-500/10 border-red-500/30 text-red-400'
        ]"
      >
        <svg v-if="toast.type === 'success'" class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
        </svg>
        <svg v-else class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
        </svg>
        <span>{{ toast.message }}</span>
      </div>
    </Transition>

    <!-- ── Form ── -->
    <form @submit.prevent="handleSubmit" novalidate class="space-y-6">

      <!-- Product Name & Price Row -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Product Name -->
        <div class="md:col-span-2 form-group">
          <label for="product-name" class="form-label">
            Nama Produk <span class="text-red-400">*</span>
          </label>
          <input
            id="product-name"
            v-model="form.product_name"
            type="text"
            class="form-input"
            :class="{ 'border-red-500 focus:ring-red-500': formErrors.product_name }"
            placeholder="cth. Abaya Raabiha Classic Premium"
            autocomplete="off"
          />
          <p v-if="formErrors.product_name" class="mt-1.5 text-xs text-red-400">
            {{ formErrors.product_name }}
          </p>
        </div>

        <!-- Price -->
        <div class="form-group">
          <label for="product-price" class="form-label">
            Harga (IDR) <span class="text-red-400">*</span>
          </label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#757575] text-sm font-medium pointer-events-none">Rp</span>
            <input
              id="product-price"
              v-model="form.price"
              type="number"
              min="0"
              step="1000"
              class="form-input pl-10"
              :class="{ 'border-red-500 focus:ring-red-500': formErrors.price }"
              placeholder="250000"
            />
          </div>
          <p v-if="formErrors.price" class="mt-1.5 text-xs text-red-400">
            {{ formErrors.price }}
          </p>
        </div>
      </div>

      <!-- Description -->
      <div class="form-group">
        <label for="product-desc" class="form-label">Deskripsi Produk</label>
        <textarea
          id="product-desc"
          v-model="form.description"
          class="form-textarea"
          placeholder="Deskripsikan produk secara detail — bahan, ukuran, keunggulan, cara perawatan..."
          rows="4"
        />
      </div>

      <!-- SEO Section -->
      <div class="rounded-xl border border-[#e0e0e0] bg-white p-5">
        <div class="flex items-center gap-2 mb-5">
          <svg class="w-4 h-4 text-[#1e1e1e]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
          </svg>
          <h3 class="text-sm font-bold text-[#1e1e1e]">Optimasi SEO (Yoast)</h3>
        </div>

        <div class="space-y-5">
          <!-- Focus Keyword -->
          <div class="form-group">
            <label for="focus-keyword" class="form-label">Focus Keyphrase</label>
            <input
              id="focus-keyword"
              v-model="form.focus_keyword"
              type="text"
              class="form-input"
              placeholder="cth. abaya hitam premium wanita muslimah"
              autocomplete="off"
            />
            <!-- Keyword in Title Indicator -->
            <div class="flex items-center gap-2 mt-2">
              <span :class="['seo-badge', `seo-badge--${keywordStatus}`]">
                <span :class="[
                  'inline-block w-1.5 h-1.5 rounded-full',
                  keywordStatus === 'good'    ? 'bg-emerald-400' :
                  keywordStatus === 'warning' ? 'bg-amber-400' : 'bg-slate-500'
                ]"></span>
                {{ keywordLabel }}
              </span>
            </div>
          </div>

          <!-- Meta Description -->
          <div class="form-group">
            <div class="flex items-center justify-between mb-1.5">
              <label for="meta-desc" class="form-label mb-0">Meta Description</label>
              <div class="flex items-center gap-2">
                <span :class="['seo-badge', `seo-badge--${metaDescStatus}`]">
                  {{ metaDescLabel }}
                </span>
                <span class="text-xs text-[#757575] font-mono">
                  {{ metaDescLength }}<span class="text-[#949494]">/320</span>
                </span>
              </div>
            </div>
            <textarea
              id="meta-desc"
              v-model="form.meta_desc"
              class="form-textarea min-h-[80px]"
              placeholder="Deskripsi singkat 120–160 karakter yang muncul di hasil pencarian Google..."
              rows="3"
              maxlength="320"
            />
            <!-- Progress Bar -->
            <div class="seo-progress mt-2">
              <div
                :class="['seo-progress__fill', metaDescProgressColor]"
                :style="{ width: `${metaDescProgress}%` }"
              ></div>
            </div>
            <p class="text-xs text-[#949494] mt-1.5">Optimal: 120–160 karakter. Saat ini: <span :class="metaDescStatus === 'good' ? 'text-[#007CBA] font-bold' : 'text-[#757575]'">{{ metaDescLength }}</span> karakter.</p>
          </div>
        </div>
      </div>

      <!-- Submit Row -->
      <div class="flex items-center justify-between pt-2 border-t border-[#e0e0e0]">
        <button
          type="button"
          @click="resetForm"
          class="btn-secondary !py-2.5 !px-4 text-xs"
          :disabled="isSubmitting"
        >
          Reset Form
        </button>
        <button
          type="submit"
          class="btn-primary"
          :disabled="!isFormValid || isSubmitting"
        >
          <svg v-if="!isSubmitting" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/>
          </svg>
          <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <span>{{ isSubmitting ? 'Menyimpan…' : 'Simpan Produk' }}</span>
        </button>
      </div>

    </form>

    <!-- ── Success Result Card ── -->
    <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 translate-y-4">
      <div v-if="savedProduct" class="mt-6 p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
        <p class="text-sm font-bold text-[#007CBA] mb-2">✓ Produk Tersimpan</p>
        <div class="grid grid-cols-2 gap-2 text-xs text-[#757575]">
          <div>ID: <span class="text-[#1e1e1e] font-mono">{{ savedProduct.product_id }}</span></div>
          <div>Nama: <span class="text-[#1e1e1e]">{{ savedProduct.product_name }}</span></div>
        </div>
        <div class="flex gap-3 mt-3">
          <a :href="savedProduct.edit_url" target="_blank" class="text-xs text-[#007CBA] hover:text-[#007CBA] underline underline-offset-2">Edit di WP →</a>
          <a :href="savedProduct.permalink" target="_blank" class="text-xs text-[#007CBA] hover:text-[#007CBA] underline underline-offset-2">Lihat Produk →</a>
        </div>
      </div>
    </Transition>

  </div>
</template>
