<template>
  <Head title="Dashboard User" />

  <AuthenticatedLayout>
    <div class="min-h-screen bg-slate-50/50 pb-20 font-sans text-slate-600 relative overflow-x-hidden">
      
      <Transition name="toast">
        <div v-if="toast.show" 
             class="fixed top-24 right-6 z-[100] flex items-center gap-3 px-5 py-4 rounded-2xl shadow-2xl border bg-white/95 backdrop-blur-md w-max max-w-sm" 
             :class="toast.type === 'success' ? 'border-l-4 border-l-green-500' : 'border-l-4 border-l-red-500'"
        >
          <div class="h-8 w-8 rounded-full flex items-center justify-center shrink-0" :class="toast.type === 'success' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'">
            <component :is="toast.type === 'success' ? CheckIcon : XMarkIcon" class="h-5 w-5" />
          </div>
          <div>
            <h4 class="text-sm font-bold text-slate-800">{{ toast.title }}</h4>
            <p class="text-xs opacity-80">{{ toast.message }}</p>
          </div>
        </div>
      </Transition>

      <div class="bg-white px-4 py-4 md:px-8 md:py-5 shadow-sm border-b border-slate-200 sticky top-0 z-30 backdrop-blur-xl bg-white/90">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
          
          <div class="flex items-center gap-4 w-full md:w-auto">
            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-md ring-2 ring-blue-100">
              {{ getInitials(auth_user.name) }}
            </div>
            <div>
              <h1 class="text-xl font-bold text-slate-800 leading-tight">Halo, {{ getFirstName(auth_user.name) }}!</h1>
              <p class="text-xs text-slate-500 flex items-center gap-1.5 mt-0.5">
                <BuildingOfficeIcon class="h-3 w-3 text-blue-500" /> {{ auth_user.unit?.name || 'Unit Kerja' }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3 w-full md:w-auto justify-end">
            <div class="flex bg-slate-100 p-1 rounded-xl border border-slate-200">
              <button @click="activeTab = 'catalog'" class="px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-2" :class="activeTab === 'catalog' ? 'bg-white text-blue-600 shadow-sm ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-700'">
                <Squares2X2Icon class="h-4 w-4" /> Katalog
              </button>
              <button @click="activeTab = 'history'" class="px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-2" :class="activeTab === 'history' ? 'bg-white text-blue-600 shadow-sm ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-700'">
                <ClockIcon class="h-4 w-4" /> Riwayat
              </button>
            </div>

            <button v-if="activeTab === 'catalog'" @click="isCartOpen = true" class="relative p-2.5 rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-600/30 hover:bg-blue-700 transition-all flex items-center gap-2 pr-4 group ml-2">
               <ShoppingCartIcon class="h-5 w-5 group-hover:scale-110 transition-transform" />
               <span class="text-xs font-bold hidden sm:inline">Keranjang</span>
               <span v-if="cart.length > 0" class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[10px] font-bold h-5 w-5 flex items-center justify-center rounded-full border-2 border-white animate-bounce">{{ cart.length }}</span>
            </button>
          </div>

        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div v-if="activeTab === 'catalog'" class="animate-fade-in-up">
          
          <div class="mb-8 flex flex-col sm:flex-row gap-4">
            <div class="relative flex-1">
              <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" />
              <input v-model="searchQuery" type="text" placeholder="Cari barang (nama, kode)..." class="w-full pl-11 pr-4 py-3 rounded-2xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm bg-white transition-all focus:shadow-md" />
            </div>
            <select v-model="selectedCategory" class="px-4 py-3 rounded-2xl border-slate-200 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-600 bg-white min-w-[200px] cursor-pointer hover:border-blue-300 transition-colors">
              <option value="All">Semua Kategori</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.name">{{ cat.name }}</option>
            </select>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="item in filteredCatalog" :key="item.id" class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all overflow-hidden group flex flex-col relative">
              
              <div class="absolute top-3 right-3 z-10">
                  <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg shadow-sm backdrop-blur-sm border flex items-center gap-1.5"
                        :class="getStockStatus(item).class">
                      <span class="w-2 h-2 rounded-full" :class="getStockStatus(item).dot"></span>
                      {{ getStockStatus(item).label }}
                  </span>
              </div>

              <div class="h-44 bg-gradient-to-b from-slate-50 to-white relative overflow-hidden flex items-center justify-center p-6 group-hover:from-blue-50/50 transition-colors">
                  <img v-if="item.url_photo" :src="'/storage/' + item.url_photo" class="h-full object-contain drop-shadow-sm group-hover:scale-110 transition-transform duration-500 mix-blend-multiply" />
                  <div v-else class="flex flex-col items-center text-slate-300"><CubeIcon class="h-12 w-12 mb-1" /><span class="text-[10px] font-bold">No Image</span></div>
              </div>

              <div class="p-5 flex-1 flex flex-col border-t border-slate-50">
                  <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 uppercase tracking-wider">{{ item.category_name || 'Umum' }}</span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 line-clamp-2 min-h-[40px] leading-snug group-hover:text-blue-600 transition-colors">{{ item.name }}</h3>
                    <p class="text-[10px] text-slate-400 mt-1 font-mono flex items-center gap-1"><TagIcon class="h-3 w-3" /> {{ item.code }}</p>
                  </div>
                  
                  <div class="mt-4 grid grid-cols-2 gap-2 text-[10px] font-medium text-slate-500">
                     <div class="bg-slate-50 p-1.5 rounded text-center border border-slate-100">Sisa Unit: <b class="text-slate-700">{{ item.unit_stock }}</b></div>
                     <div class="bg-slate-50 p-1.5 rounded text-center border border-slate-100">Satuan: <b class="text-slate-700">{{ item.uom }}</b></div>
                  </div>
                  
                  <div class="mt-4 flex gap-2">
                      
                      <button @click="addToCart(item)" 
                              :disabled="isInCart(item.id)" 
                              class="flex-[2] py-2.5 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition-all active:scale-95 shadow-md" 
                              :class="[
                                isInCart(item.id) ? 'bg-emerald-50 text-emerald-600 cursor-not-allowed border border-emerald-100 shadow-none' 
                                : 'bg-blue-600 text-white hover:bg-blue-700 hover:shadow-blue-600/30'
                              ]">
                          <component :is="isInCart(item.id) ? CheckCircleIcon : PlusCircleIcon" class="h-4 w-4" /> 
                          <span v-if="isInCart(item.id)">Cart</span>    
                          <span v-else>Request</span>
                      </button>
                  </div>
              </div>
            </div>
          </div>

          <div v-if="filteredCatalog.length === 0" class="flex flex-col items-center justify-center py-20 text-center animate-fade-in-up">
             <div class="h-20 w-20 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-slate-100"><MagnifyingGlassIcon class="h-10 w-10 text-slate-300" /></div>
             <h3 class="text-lg font-bold text-slate-700">Barang tidak ditemukan</h3>
             <p class="text-sm text-slate-400 mt-1">Coba gunakan kata kunci lain atau ubah kategori.</p>
          </div>
        </div>

        <div v-if="activeTab === 'history'" class="animate-fade-in-up space-y-8 max-w-4xl mx-auto">
           
           <div v-if="Object.keys(groupedHistory).length === 0" class="flex flex-col items-center justify-center py-24 bg-white rounded-3xl border border-slate-200 border-dashed">
              <div class="bg-slate-50 p-6 rounded-full mb-4"><ClockIcon class="h-12 w-12 text-slate-300" /></div>
              <h3 class="text-slate-600 font-bold text-lg">Belum ada riwayat pengajuan</h3>
              <p class="text-slate-400 text-sm mt-1">Permintaan Anda akan muncul di sini.</p>
              <button @click="activeTab='catalog'" class="mt-6 text-blue-600 font-bold text-sm hover:underline">Mulai Request Baru &rarr;</button>
           </div>

           <div v-for="(group, code) in groupedHistory" :key="code" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-md transition-all">
              <div class="bg-slate-50/50 px-5 py-4 border-b border-slate-100 flex justify-between items-start md:items-center flex-col md:flex-row gap-3">
                  <div class="flex items-center gap-3">
                      <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                          <ClipboardDocumentListIcon class="h-5 w-5" />
                      </div>
                      <div>
                          <h4 class="text-sm font-bold text-slate-700">{{ code }}</h4>
                          <p class="text-xs text-slate-500">{{ formatDateTimeFull(group[0].created_at) }}</p>
                      </div>
                  </div>
                  <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase border"
                        :class="{
                            'bg-amber-100 text-amber-700 border-amber-200': group[0].status === 'Pending',
                            'bg-emerald-100 text-emerald-700 border-emerald-200': group[0].status === 'Approved',
                            'bg-red-100 text-red-700 border-red-200': group[0].status === 'Rejected'
                        }">
                      {{ group[0].status }}
                  </span>
              </div>

              <div class="divide-y divide-slate-100">
                  <div v-for="req in group" :key="req.id" class="p-5 flex items-start gap-4 hover:bg-slate-50 transition-colors">
                      <div class="h-12 w-12 rounded-lg border border-slate-200 bg-white p-1 shrink-0 flex items-center justify-center">
                          <img v-if="req.item?.url_photo" :src="'/storage/' + req.item.url_photo" class="h-full w-full object-contain mix-blend-multiply" />
                          <CubeIcon v-else class="h-6 w-6 text-slate-300" />
                      </div>
                      
                      <div class="flex-1">
                          <h5 class="text-sm font-bold text-slate-800">{{ req.item?.name }}</h5>
                          <div class="flex justify-between items-start mt-1">
                              <div>
                                  <p class="text-xs text-slate-500">Jml Request: <b class="text-slate-700">{{ req.quantity }}</b></p>
                                  <p v-if="req.note" class="text-[10px] text-slate-400 mt-1 italic max-w-xs">"{{ req.note }}"</p>
                              </div>
                              <div v-if="req.status !== group[0].status" class="text-[10px] font-bold text-slate-400 border px-1.5 py-0.5 rounded">
                                  {{ req.status }}
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
           </div>
        </div>

      </div>

      <Transition name="slide-over">
        <div v-if="isCartOpen" class="fixed inset-0 z-[100] flex justify-end">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="isCartOpen = false"></div>
            
            <div class="relative w-full max-w-md bg-white shadow-2xl h-full flex flex-col transform transition-transform animate-slide-in-right">
               <div class="p-6 border-b border-blue-100 flex justify-between items-center bg-blue-600 text-white shadow-md z-10">
                  <div>
                    <h3 class="text-lg font-bold flex items-center gap-2"><ShoppingCartIcon class="h-6 w-6 text-blue-200"/> Keranjang</h3>
                    <p class="text-blue-100 text-xs mt-0.5">{{ cart.length }} Item dipilih</p>
                  </div>
                  <button @click="isCartOpen = false" class="text-blue-200 hover:text-white transition-colors bg-white/10 p-1.5 rounded-lg"><XMarkIcon class="h-6 w-6"/></button>
               </div>

               <div class="flex-1 overflow-y-auto p-5 custom-scrollbar bg-slate-50">
                  <div v-if="cart.length === 0" class="flex flex-col items-center justify-center h-full text-slate-400">
                     <div class="bg-white p-4 rounded-full shadow-sm mb-3 border border-slate-100"><ShoppingCartIcon class="h-10 w-10 text-slate-300" /></div>
                     <p class="text-sm font-medium">Keranjang masih kosong.</p>
                  </div>

                  <TransitionGroup name="list" tag="div" class="space-y-4">
                    <div v-for="(cartItem, idx) in cart" :key="cartItem.id" class="p-4 border border-slate-200 rounded-2xl bg-white shadow-sm relative group hover:border-blue-300 transition-colors">
                        <button @click="removeFromCart(idx)" class="absolute -top-2 -right-2 bg-white border border-slate-200 text-slate-400 rounded-full p-1 hover:bg-red-500 hover:text-white hover:border-red-500 transition-colors shadow-sm z-10"><XMarkIcon class="h-4 w-4" /></button>
                        
                        <div class="flex gap-4">
                            <div class="h-14 w-14 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center shrink-0 overflow-hidden">
                               <img v-if="cartItem.url_photo" :src="'/storage/' + cartItem.url_photo" class="h-full w-full object-cover" />
                               <CubeIcon v-else class="h-6 w-6 text-slate-300" />
                            </div>
                            <div class="flex-1 min-w-0">
                               <p class="text-xs font-bold text-slate-800 line-clamp-1">{{ cartItem.name }}</p>
                               <p class="text-[10px] text-slate-500 mb-2 font-mono">{{ cartItem.code }}</p>
                               <div class="flex justify-between items-end">
                                  <div class="flex items-center border border-slate-200 rounded-lg bg-slate-50">
                                   <button @click="updateCartQty(cartItem, -1)" class="px-2.5 py-1 text-slate-500 hover:bg-blue-100 hover:text-blue-600 rounded-l-lg transition-colors font-bold">-</button>
                                   <input type="number" v-model.number="cartItem.qty" class="w-10 text-center bg-transparent border-none text-xs font-bold focus:ring-0 p-0 text-slate-700 no-spinner" />
                                   <button @click="updateCartQty(cartItem, 1)" class="px-2.5 py-1 text-slate-500 hover:bg-blue-100 hover:text-blue-600 rounded-r-lg transition-colors font-bold">+</button>
                                  </div>
                               </div>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <input type="text" v-model="cartItem.notes" placeholder="Catatan per item (opsional)..." class="w-full text-[11px] bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-slate-700 placeholder:text-slate-400" />
                        </div>
                     </div>
                  </TransitionGroup>
               </div>

               <div class="p-6 border-t border-slate-100 bg-white shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-10">
                  <label class="text-[10px] font-bold text-slate-500 uppercase mb-2 block">Keterangan Umum (Tujuan) <span class="text-red-500">*</span></label>
                  <textarea v-model="requestDescription" rows="2" class="w-full rounded-xl border-slate-200 text-xs focus:ring-blue-500 focus:border-blue-500 mb-4 p-3 bg-slate-50" placeholder="Contoh: Kebutuhan ATK bulan ini..."></textarea>
                  
                  <button @click="submitRequest" 
                          :disabled="cart.length === 0 || processing" 
                          class="w-full py-3.5 rounded-xl font-bold text-sm text-white shadow-lg shadow-blue-600/30 transition-all flex items-center justify-center gap-2" 
                          :class="(cart.length > 0 && !processing) ? 'bg-blue-600 hover:bg-blue-700 hover:-translate-y-0.5' : 'bg-slate-300 cursor-not-allowed'">
                      <PaperAirplaneIcon v-if="!processing" class="h-4 w-4" /> 
                      <span v-else>Mengirim...</span>
                      {{ processing ? '' : 'Kirim Pengajuan' }}
                  </button>
               </div>
            </div>
         </div>
      </Transition>

      <Transition name="modal-fade">
        <div v-if="consumeModal.show" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeConsumeModal"></div>
          <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden animate-scale-up ring-1 ring-white/10">
              
              <div class="bg-gradient-to-r from-orange-500 to-amber-500 p-6 flex items-start justify-between text-white shadow-lg relative z-10">
                  <div class="flex items-center gap-4">
                      <div class="h-12 w-12 rounded-2xl bg-white/20 flex items-center justify-center shrink-0 backdrop-blur-md border border-white/20 shadow-inner">
                          <BoltIcon class="h-7 w-7 text-white" />
                      </div>
                      <div>
                          <h3 class="text-xl font-black leading-tight tracking-tight">Lapor Pemakaian</h3>
                          <p class="text-xs text-orange-100 font-medium mt-0.5 opacity-90">Barang: {{ consumeModal.item?.name }}</p>
                      </div>
                  </div>
                  <button @click="closeConsumeModal" class="text-white/60 hover:text-white transition-colors bg-white/10 hover:bg-white/20 p-1.5 rounded-lg">
                      <XMarkIcon class="h-6 w-6" />
                  </button>
              </div>

              <div class="p-6 space-y-5 bg-white">
                  <div>
                      <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Jumlah Dipakai <span class="text-red-500">*</span></label>
                      <input type="number" v-model.number="consumeForm.qty" min="1" :max="consumeModal.item?.unit_stock" class="w-full pl-4 pr-4 py-3 rounded-xl border-slate-200 font-bold text-slate-800 focus:ring-2 focus:ring-orange-100 focus:border-orange-500 transition-all bg-slate-50 focus:bg-white text-lg" placeholder="0" />
                      <p class="text-[10px] text-slate-400 mt-1">Sisa Stok Unit: {{ consumeModal.item?.unit_stock }}</p>
                  </div>
                  <div>
                      <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Keperluan <span class="text-red-500">*</span></label>
                      <textarea v-model="consumeForm.reason" rows="2" class="w-full rounded-xl border-slate-200 text-sm p-4 focus:ring-2 focus:ring-orange-100 focus:border-orange-500 transition-all bg-slate-50 focus:bg-white resize-none" placeholder="Contoh: Digunakan saat rapat..."></textarea>
                  </div>
              </div>

              <div class="p-6 bg-slate-50 border-t border-slate-200 flex gap-3">
                  <button @click="closeConsumeModal" class="flex-1 py-3.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition-colors">Batal</button>
                  <button @click="submitConsume" class="flex-1 py-3.5 text-sm font-bold text-white bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 rounded-xl shadow-lg shadow-orange-500/30 transition-all">Konfirmasi</button>
              </div>
          </div>
        </div>
      </Transition>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { 
  PlusCircleIcon, CheckCircleIcon, CubeIcon, ShoppingCartIcon, 
  MagnifyingGlassIcon, XMarkIcon, Squares2X2Icon, ClockIcon, 
  BuildingOfficeIcon, CheckIcon, PaperAirplaneIcon,
  ClipboardDocumentListIcon, TagIcon, BoltIcon
} from '@heroicons/vue/24/outline';

// --- PROPS DARI CONTROLLER ---
const props = defineProps({
    items: Array,
    categories: Array,
    history: Array,
    auth_user: Object
});

// --- STATE ---
const activeTab = ref('catalog');
const isCartOpen = ref(false);
const searchQuery = ref('');
const selectedCategory = ref('All');
const cart = ref([]);
const requestDescription = ref('');
const processing = ref(false);
const toast = ref({ show: false, type: 'success', title: '', message: '' });

// State Modal Consume (Pakai Langsung)
const consumeModal = ref({ show: false, item: null });
const consumeForm = ref({ qty: 1, reason: '' });

// --- COMPUTED ---
const filteredCatalog = computed(() => {
    let result = props.items;
    if (selectedCategory.value !== 'All') {
        result = result.filter(i => i.category_name === selectedCategory.value);
    }
    if (searchQuery.value) {
        const lower = searchQuery.value.toLowerCase();
        result = result.filter(i => i.name.toLowerCase().includes(lower) || i.code.toLowerCase().includes(lower));
    }
    return result;
});

const groupedHistory = computed(() => {
    const groups = {};
    props.history.forEach(req => {
        const key = req.code || 'NO-CODE-' + req.created_at; 
        if (!groups[key]) groups[key] = [];
        groups[key].push(req);
    });
    return groups;
});

// --- METHODS CART ---
const isInCart = (itemId) => cart.value.some(c => c.id === itemId);

const addToCart = (item) => {
    if (isInCart(item.id)) return;
    cart.value.push({
        id: item.id,
        name: item.name,
        code: item.code,
        url_photo: item.url_photo,
        qty: 1,
        notes: ''
    });
    showToast('success', 'Berhasil', 'Ditambahkan ke keranjang.');
};

const removeFromCart = (index) => cart.value.splice(index, 1);

const updateCartQty = (item, amount) => {
    const newQty = item.qty + amount;
    if (newQty < 1) return;
    item.qty = newQty;
};

// --- METHODS REQUEST SUBMIT ---
const submitRequest = () => {
    if (!requestDescription.value) {
        showToast('error', 'Gagal', 'Mohon isi keterangan umum pengajuan.');
        return;
    }
    processing.value = true;
    router.post(route('stock-requests.store'), {
        description: requestDescription.value,
        details: cart.value.map(c => ({
            item_id: c.id,
            qty: c.qty,
            notes: c.notes
        }))
    }, {
        onSuccess: () => {
            cart.value = [];
            requestDescription.value = '';
            isCartOpen.value = false;
            showToast('success', 'Terkirim', 'Permintaan Anda sedang diproses.');
            activeTab.value = 'history';
        },
        onFinish: () => processing.value = false
    });
};

// --- METHODS CONSUME (PAKAI LANGSUNG) ---
const openConsumeModal = (item) => {
    consumeModal.value.item = item;
    consumeForm.value = { qty: 1, reason: '' };
    consumeModal.value.show = true;
};
const closeConsumeModal = () => { consumeModal.value.show = false; };

const submitConsume = () => {
    if(!consumeForm.value.reason) { showToast('error', 'Gagal', 'Isi keperluan.'); return; }
    
    // Kita gunakan endpoint request biasa, tapi dengan flag/keterangan khusus
    router.post(route('stock-requests.store'), {
        description: 'PEMAKAIAN LANGSUNG: ' + consumeForm.value.reason,
        details: [{
            item_id: consumeModal.value.item.id,
            qty: consumeForm.value.qty,
            notes: 'Direct Consume'
        }]
    }, {
        onSuccess: () => {
            closeConsumeModal();
            showToast('success', 'Berhasil', 'Laporan pemakaian tercatat.');
            activeTab.value = 'history';
        }
    });
};

// --- UTILS ---
const showToast = (type, title, message) => {
    toast.value = { show: true, type, title, message };
    setTimeout(() => toast.value.show = false, 3000);
};
const getStockStatus = (item) => {
    if (item.unit_stock <= 0) return { label: 'Stok Unit Habis', class: 'bg-red-100 text-red-700 border-red-200', dot: 'bg-red-500' };
    return { label: 'Tersedia', class: 'bg-emerald-100 text-emerald-700 border-emerald-200', dot: 'bg-emerald-500' };
};
const getInitials = (name) => name ? name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase() : 'U';
const getFirstName = (name) => name ? name.split(' ')[0] : 'User';
const formatDateTimeFull = (isoString) => {
    if (!isoString) return '-';
    return new Date(isoString).toLocaleString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<style scoped>
/* CSS Custom untuk Animasi & Scrollbar */
.no-spinner::-webkit-inner-spin-button, .no-spinner::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
.no-spinner { -moz-appearance: textfield; }

.animate-fade-in-up { animation: fadeInUp 0.5s ease-out; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.animate-slide-in-right { animation: slideInRight 0.3s ease-out; }
@keyframes slideInRight { from { transform: translateX(100%); } to { transform: translateX(0); } }

.list-enter-active, .list-leave-active { transition: all 0.3s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateX(20px); }

.toast-enter-active, .toast-leave-active { transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(-20px) translateX(20px); }

.slide-over-enter-active, .slide-over-leave-active { transition: opacity 0.3s ease; }
.slide-over-enter-from, .slide-over-leave-to { opacity: 0; }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

.animate-scale-up { animation: scaleUp 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes scaleUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }

.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>