<template>
  <Head title="Manajemen Stok" />

  <AuthenticatedLayout>
    
    <Transition name="toast-slide-top">
      <div v-if="toast.show" class="fixed top-24 left-1/2 z-[100] w-full max-w-sm -translate-x-1/2 transform px-4">
        <div class="flex items-center overflow-hidden rounded-2xl p-4 shadow-2xl backdrop-blur-xl ring-1 transition-all"
          :class="{ 
            'bg-white/95 text-slate-800 ring-slate-200': true, 
            'border-l-4 border-green-500': toast.type === 'success', 
            'border-l-4 border-red-500': toast.type === 'error' 
          }">
          <div class="flex-shrink-0">
            <CheckCircleIcon v-if="toast.type === 'success'" class="h-6 w-6 text-green-500" />
            <XCircleIcon v-if="toast.type === 'error'" class="h-6 w-6 text-red-500" />
          </div>
          <div class="ml-3 flex-1"><p class="text-sm font-semibold">{{ toast.message }}</p></div>
          <button @click="toast.show = false" class="ml-4 flex-shrink-0 text-slate-400 hover:text-slate-600 transition-colors"><XMarkIcon class="h-5 w-5" /></button>
        </div>
      </div>
    </Transition>

    <div v-if="deleteModal.show" class="relative z-[1100]">
      <div class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm transition-opacity" @click="closeDeleteModal"></div>
      <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl transform transition-all animate-in zoom-in-95 duration-200">
           <div class="p-6 flex gap-4 items-start">
              <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center text-red-600 shrink-0">
                 <NoSymbolIcon class="h-6 w-6" />
              </div>
              <div>
                 <h3 class="text-lg font-bold text-slate-900">Hapus Data Stok</h3>
                 <p class="text-sm text-slate-500 mt-1 leading-relaxed">
                    Yakin hapus stok <strong>{{ deleteModal.stock?.item?.name }}</strong>? Data yang dihapus akan tercatat di riwayat mutasi sebagai penghapusan (Write-off).
                 </p>
              </div>
           </div>
           <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3">
              <button @click="confirmDeleteAction" class="px-5 py-2.5 bg-red-600 text-white rounded-xl font-bold text-sm hover:bg-red-700 shadow-sm transition-all">Hapus Stok</button>
              <button @click="closeDeleteModal" class="px-5 py-2.5 bg-white text-slate-700 border border-slate-300 rounded-xl font-bold text-sm hover:bg-slate-50 transition-all">Batal</button>
           </div>
        </div>
      </div>
    </div>

    <div class="py-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-6">
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-800">Manage Stock</h1>
            <p class="mt-2 text-base text-slate-500">
              Monitoring stok fisik per unit kerja & Riwayat Mutasi (Ref: <code>eatk_item_unit</code>).
            </p>
          </div>
          <div class="hidden sm:flex flex-col items-end justify-center">
            <div class="flex items-center gap-2 text-sm font-bold text-slate-700 bg-white px-4 py-2 rounded-full shadow-sm border border-slate-100">
              <CalendarDaysIcon class="h-4 w-4 text-blue-500" />
              <span>{{ currentTime }} WIB</span>
            </div>
          </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] border border-slate-100">
          
          <div class="mb-8 border-b border-slate-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
              <button 
                @click="activeTab = 'stock'"
                class="group inline-flex items-center border-b-2 py-4 px-1 text-sm font-bold transition-all duration-200 ease-in-out whitespace-nowrap outline-none"
                :class="activeTab === 'stock' 
                  ? 'border-blue-600 text-blue-700' 
                  : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
              >
                <ArchiveBoxIcon class="mr-2 h-5 w-5" :class="activeTab === 'stock' ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-500'" />
                Stok Fisik (Aktif)
              </button>
              <button 
                @click="activeTab = 'history'"
                class="group inline-flex items-center border-b-2 py-4 px-1 text-sm font-bold transition-all duration-200 ease-in-out whitespace-nowrap outline-none"
                :class="activeTab === 'history' 
                  ? 'border-blue-600 text-blue-700' 
                  : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
              >
                <ClockIcon class="mr-2 h-5 w-5" :class="activeTab === 'history' ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-500'" />
                Riwayat Mutasi (Log)
              </button>
            </nav>
          </div>

          <div v-if="activeTab === 'stock'">
            
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
              <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <ArchiveBoxIcon class="h-5 w-5 text-slate-400" />
                Daftar Stok Unit
              </h2>

              <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto items-center">
                <div class="relative w-full sm:w-64">
                  <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><MagnifyingGlassIcon class="h-4 w-4 text-slate-400" /></div>
                  <input 
                    v-model="search"
                    @input="debouncedSearch"
                    type="text" 
                    class="block w-full rounded-xl border-0 h-10 py-1.5 pl-10 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 transition-shadow" 
                    placeholder="Cari barang / kode..."
                  >
                </div>

                <div class="relative w-full sm:w-48">
                   <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><MapPinIcon class="h-4 w-4 text-slate-400" /></div>
                   <select v-model="filters.unit" @change="applyFilters" class="block w-full rounded-xl border-0 h-10 py-1.5 pl-9 pr-8 text-slate-900 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 cursor-pointer">
                      <option value="">Semua Unit</option>
                      <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.alias }}</option>
                   </select>
                </div>

                <button 
                  @click="openModal(null)"
                  class="inline-flex items-center justify-center rounded-xl bg-blue-600 h-10 px-4 text-sm font-bold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/20 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 active:scale-95 whitespace-nowrap w-full sm:w-auto"
                >
                  <PlusIcon class="mr-1.5 h-4 w-4" />
                  Tambah Stok
                </button>
              </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                  <thead class="bg-slate-50">
                    <tr>
                      <th class="w-[25%] py-3.5 pl-4 pr-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500 sm:pl-6">Barang</th>
                      <th class="w-[15%] px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Unit</th>
                      
                      <th class="w-[15%] px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Penanggung Jawab</th>
                      
                      <th class="w-[15%] px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Kondisi Stok</th>
                      <th class="w-[10%] px-3 py-3.5 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Harga</th>
                      <th class="w-[10%] px-3 py-3.5 text-center text-xs font-bold uppercase tracking-wide text-slate-500">Status</th>
                      <th class="w-[10%] relative py-3.5 pl-3 pr-4 text-center text-xs font-bold uppercase tracking-wide text-slate-500 sm:pr-6">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-200 bg-white">
                    
                    <tr v-if="stocks.data.length === 0">
                      <td colspan="7" class="py-12 text-center text-sm text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                          <div class="h-12 w-12 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                            <ArchiveBoxIcon class="h-6 w-6 text-slate-300" />
                          </div>
                          <p>Tidak ada data stok yang cocok.</p>
                        </div>
                      </td>
                    </tr>

                    <tr v-for="stk in stocks.data" :key="stk.id" class="group hover:bg-slate-50/80 transition-colors">
                      <td class="py-4 pl-4 pr-3 sm:pl-6">
                        <div class="flex items-center gap-4">
                           
                           <div class="h-12 w-12 rounded-lg bg-slate-100 border border-slate-200 flex-shrink-0 overflow-hidden flex items-center justify-center shadow-sm">
                              <img v-if="stk.item?.url_photo" :src="'/storage/' + stk.item.url_photo" alt="Foto Barang" class="h-full w-full object-cover">
                              <ArchiveBoxIcon v-else class="h-6 w-6 text-slate-300" />
                           </div>
                           <div class="min-w-0">
                              <div class="font-bold text-slate-800 text-sm truncate">{{ stk.item?.name }}</div>
                              <div class="flex items-center gap-2 mt-1">
                                 <span class="text-[10px] font-mono bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 text-slate-500">
                                   {{ stk.item?.code }}
                                 </span>
                                 <span class="text-[10px] text-slate-400 truncate max-w-[100px]">
                                   {{ stk.item?.category?.name }}
                                 </span>
                              </div>
                           </div>
                        </div>
                      </td>

                      <td class="px-3 py-4 text-sm text-slate-500">
                          <div class="flex items-center gap-1.5">
                             <MapPinIcon class="h-3.5 w-3.5 text-slate-400" />
                             <span class="text-sm font-semibold text-slate-700 truncate max-w-[150px]" :title="stk.unit?.alias">{{ stk.unit?.alias }}</span>
                          </div>
                      </td>

                      <td class="px-3 py-4 text-sm text-slate-500">
                          <div class="flex items-center gap-2">
                             <div class="h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center text-[10px] font-bold text-blue-600 border border-blue-200">
                                {{ (stk.user?.full_name || stk.user?.name || '?').charAt(0).toUpperCase() }}
                             </div>
                             <span class="font-medium text-slate-700 truncate max-w-[150px]" :title="stk.user?.full_name || stk.user?.name">
                                {{ stk.user?.full_name || stk.user?.name || 'No User' }}
                             </span>
                          </div>
                      </td>

                      <td class="px-3 py-4">
                          <div class="flex flex-col gap-1.5">
                             <div class="flex justify-between items-end">
                                <span class="text-sm font-bold text-slate-800">{{ stk.stock }} <span class="text-[10px] font-normal text-slate-400">{{ stk.item?.uom }}</span></span>
                                
                                <span v-if="stk.stock <= 0" class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-red-50 text-[9px] font-bold text-red-600 border border-red-100 uppercase tracking-wide">HABIS</span>
                                <span v-else-if="stk.stock <= stk.stock_min" class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-orange-50 text-[9px] font-bold text-orange-700 border border-orange-100 uppercase tracking-wide">MENIPIS</span>
                                <span v-else class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-emerald-50 text-[9px] font-bold text-emerald-700 border border-emerald-100 uppercase tracking-wide">AMAN</span>
                             </div>
                             <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500 relative" 
                                     :class="stk.stock <= 0 ? 'bg-red-600' : (stk.stock <= stk.stock_min ? 'bg-orange-500' : 'bg-emerald-500')"
                                     :style="{ width: Math.min((stk.stock / (stk.stock_min * 3)) * 100, 100) + '%' }">
                                </div>
                             </div>
                             <span class="text-[10px] text-slate-400">Min: {{ stk.stock_min }}</span>
                          </div>
                      </td>

                      <td class="px-3 py-4 text-right text-sm font-medium text-slate-700 tabular-nums">
                          Rp {{ (stk.price || 0).toLocaleString('id-ID') }}
                      </td>

                      <td class="px-3 py-4 text-center">
                          <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset"
                           :class="stk.status === 'Active' ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-slate-100 text-slate-500 ring-slate-500/20'">
                             <span class="h-1.5 w-1.5 rounded-full mr-1.5" :class="stk.status === 'Active' ? 'bg-green-600' : 'bg-slate-400'"></span>
                             {{ stk.status === 'Active' ? 'Aktif' : 'Non-Aktif' }}
                          </span>
                      </td>

                      <td class="py-4 pl-3 pr-4 text-center text-xs font-medium sm:pr-6">
                        <div class="flex items-center justify-center gap-2">
                          <button @click="openModal(stk)" class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-all font-semibold shadow-sm border border-blue-200">Edit / Mutasi</button>
                          <button @click="handleDelete(stk)" class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-all font-semibold shadow-sm border border-red-200">Hapus</button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              
              <div v-if="stocks.data.length > 0" class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-3 sm:px-6">
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                  <div>
                    <p class="text-sm text-slate-700">
                      Menampilkan <span class="font-medium">{{ stocks.from }}</span> sampai <span class="font-medium">{{ stocks.to }}</span> dari <span class="font-medium">{{ stocks.total }}</span>
                    </p>
                  </div>
                  <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                      <Link v-for="link in stocks.links" :key="link.label" :href="link.url || '#'" 
                          v-html="link.label"
                          class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-slate-300 focus:z-20 focus:outline-offset-0 transition-colors"
                          :class="{ 
                              'z-10 bg-blue-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600': link.active,
                              'text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50': !link.active,
                              'rounded-l-md': link.label.includes('Previous'),
                              'rounded-r-md': link.label.includes('Next'),
                              'opacity-50 cursor-not-allowed': !link.url
                          }"
                          :preserve-state="true" />
                    </nav>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="activeTab === 'history'">
             <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
               <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                 <ClockIcon class="h-5 w-5 text-slate-400" /> Log Mutasi Barang
               </h2>
               <div class="flex items-center gap-4">
                 <div class="relative w-full sm:w-64">
                   <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><MagnifyingGlassIcon class="h-4 w-4 text-slate-400" /></div>
                   <input v-model="filters.history_search" @input="debouncedSearchHistory" type="text" class="block w-full rounded-xl border-0 h-10 py-1.5 pl-10 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 transition-shadow" placeholder="Cari riwayat...">
                 </div>
               </div>
             </div>

             <div class="overflow-x-auto rounded-xl border border-slate-200">
               <table class="min-w-full divide-y divide-slate-200">
                 <thead class="bg-slate-50">
                   <tr>
                     <th class="py-3 pl-4 text-left text-xs font-bold uppercase text-slate-500">Waktu</th>
                     <th class="px-3 py-3 text-left text-xs font-bold uppercase text-slate-500">Tipe</th>
                     <th class="px-3 py-3 text-left text-xs font-bold uppercase text-slate-500">Barang</th>
                     <th class="px-3 py-3 text-left text-xs font-bold uppercase text-slate-500">Unit</th>
                     <th class="px-3 py-3 text-left text-xs font-bold uppercase text-slate-500">Qty</th>
                     <th class="px-3 py-3 text-left text-xs font-bold uppercase text-slate-500">Keterangan</th>
                     <th class="px-3 py-3 text-left text-xs font-bold uppercase text-slate-500">Oleh</th>
                   </tr>
                 </thead>
                 <tbody class="divide-y divide-slate-200 bg-white">
                   <tr v-if="history.data.length === 0"><td colspan="7" class="py-8 text-center text-sm text-slate-500">Belum ada riwayat transaksi.</td></tr>
                   <tr v-for="log in history.data" :key="log.id" class="hover:bg-slate-50">
                     <td class="py-3 pl-4 text-xs font-mono text-slate-600">{{ new Date(log.created_at).toLocaleString() }}</td>
                     <td class="px-3 py-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="log.type == 'IN' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'">{{ log.type == 'IN' ? 'MASUK' : 'KELUAR' }}</span>
                     </td>
                     <td class="px-3 py-3 text-sm text-slate-800 font-medium">{{ log.item_name_snapshot }}</td>
                     <td class="px-3 py-3 text-sm text-slate-600">{{ log.unit?.alias || '-' }}</td>
                     <td class="px-3 py-3 text-sm font-bold" :class="log.type == 'IN' ? 'text-green-600' : 'text-orange-600'">{{ log.type == 'IN' ? '+' : '-' }}{{ log.qty }}</td>
                     <td class="px-3 py-3 text-sm text-slate-500 italic">{{ log.note }}</td>
                     <td class="px-3 py-3 text-sm text-slate-700">{{ log.actor }}</td>
                   </tr>
                 </tbody>
               </table>
             </div>
             
             <div v-if="history.data.length > 0" class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-3 sm:px-6 mt-0">
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                  <div>
                    <p class="text-sm text-slate-700">
                      Menampilkan <span class="font-medium">{{ history.from }}</span> sampai <span class="font-medium">{{ history.to }}</span> dari <span class="font-medium">{{ history.total }}</span>
                    </p>
                  </div>
                  <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                      <Link v-for="link in history.links" :key="link.label" :href="link.url || '#'" 
                          v-html="link.label"
                          class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-slate-300 focus:z-20 focus:outline-offset-0 transition-colors"
                          :class="{ 
                              'z-10 bg-blue-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600': link.active,
                              'text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50': !link.active,
                              'rounded-l-md': link.label.includes('Previous'),
                              'rounded-r-md': link.label.includes('Next'),
                              'opacity-50 cursor-not-allowed': !link.url
                          }"
                          :preserve-state="true" />
                    </nav>
                  </div>
                </div>
             </div>
          </div>

        </div>
      </div>
    </div>

    <StockFormModal 
      :show="showModal" 
      :stock-to-edit="selectedStock" 
      :atk-options="items" 
      :unit-options="units"
      :user-options="userOptions"
      @close="closeModal" 
      @save="handleSave" 
    />
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StockFormModal from '../../Components/StockFormModal.vue';
import { debounce } from 'lodash';
import { ArchiveBoxIcon, ClockIcon, CalendarDaysIcon, MagnifyingGlassIcon, PlusIcon, CheckCircleIcon, XCircleIcon, XMarkIcon, NoSymbolIcon, MapPinIcon, TagIcon, ExclamationTriangleIcon, ArrowUpTrayIcon, ArrowDownIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ 
  stocks: Object, 
  history: Object, 
  items: Array, 
  units: Array, 
  userOptions: Array, 
  filters: Object 
});

// --- TOAST ---
const toast = ref({ show: false, message: '', type: 'success' });
const triggerToast = (msg, type) => { 
  toast.value = {show:true, message:msg, type}; 
  setTimeout(()=>toast.value.show=false, 3000); 
};

// Cek Flash Message
const page = usePage();
watch(() => page.props.flash, (flash) => {
    if(flash?.success) triggerToast(flash.success, 'success');
    if(flash?.error) triggerToast(flash.error, 'error');
}, { deep: true });

// --- FILTERS & SEARCH ---
const activeTab = ref('stock');
const search = ref(props.filters.search || '');
const historySearch = ref(props.filters.history_search || '');
const unitFilter = ref(props.filters.unit || '');

const filters = ref({
    search: search.value,
    unit: unitFilter.value,
    history_search: historySearch.value
});

const applyFilters = () => {
    router.get(route('stocks.index'), filters.value, { preserveState: true, replace: true, preserveScroll: true });
};

const debouncedSearch = debounce(applyFilters, 500);
const debouncedSearchHistory = debounce(applyFilters, 500);

// --- MODAL LOGIC ---
const showModal = ref(false);
const selectedStock = ref(null);
const openModal = (stock) => { selectedStock.value = stock; showModal.value = true; };
const closeModal = () => { showModal.value = false; selectedStock.value = null; };

const handleSave = (form) => {
    if(form.id) {
       // Update
       router.put(route('stocks.update', form.id), form, {
         onSuccess: () => closeModal(),
         onError: (err) => triggerToast(Object.values(err)[0], 'error')
       });
    } else {
       // Create
       router.post(route('stocks.store'), form, {
         onSuccess: () => closeModal(),
         onError: (err) => triggerToast(Object.values(err)[0], 'error')
       });
    }
};

// --- DELETE LOGIC ---
const deleteModal = ref({ show: false, stock: null });
const handleDelete = (stk) => { deleteModal.value = { show: true, stock: stk }; };
const closeDeleteModal = () => { deleteModal.value.show = false; };

const confirmDeleteAction = () => {
    router.delete(route('stocks.destroy', deleteModal.value.stock.id), {
        onSuccess: () => closeDeleteModal()
    });
};

const currentTime = ref('');
onMounted(() => setInterval(() => currentTime.value = new Date().toLocaleTimeString('id-ID'), 1000));
</script>

<style scoped>
.toast-slide-top-enter-active{ transition: all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1); }
.toast-slide-top-leave-active { transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
.toast-slide-top-enter-from, .toast-slide-top-leave-to { opacity: 0; transform: translateY(-20px) translateX(-50%); }
</style>