<template>
  <Head title="Manajemen Unit" />

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
                 <h3 class="text-lg font-bold text-slate-900">Hapus Unit Kerja</h3>
                 <p class="text-sm text-slate-500 mt-1 leading-relaxed">
                    Yakin hapus unit <strong>{{ deleteModal.unit?.alias }}</strong>? Data stok dan user yang terkait mungkin akan error.
                 </p>
              </div>
           </div>
           <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3">
              <button @click="confirmDeleteAction" class="px-5 py-2.5 bg-red-600 text-white rounded-xl font-bold text-sm hover:bg-red-700 shadow-sm transition-all">Hapus Unit</button>
              <button @click="closeDeleteModal" class="px-5 py-2.5 bg-white text-slate-700 border border-slate-300 rounded-xl font-bold text-sm hover:bg-slate-50 transition-all">Batal</button>
           </div>
        </div>
      </div>
    </div>

    <div class="py-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-6">
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-800">Kelola Units</h1>
            <p class="mt-2 text-base text-slate-500">
              Kelola Data Master Unit & Lokasi (Ref: <code>c_master_unit</code>).
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
          
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
              <BuildingOfficeIcon class="h-5 w-5 text-slate-400" />
              Daftar Unit Kerja
            </h2>

            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
              <div class="relative w-full sm:w-64">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                  <MagnifyingGlassIcon class="h-4 w-4 text-slate-400" />
                </div>
                <input 
                  v-model="search"
                  @input="debouncedSearch"
                  type="text" 
                  class="block w-full rounded-xl border-0 h-10 py-1.5 pl-10 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 transition-shadow" 
                  placeholder="Cari unit atau alias..."
                >
              </div>

              <button 
                @click="openModal(null)"
                class="inline-flex items-center justify-center rounded-xl bg-blue-600 h-10 px-4 text-sm font-bold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/20 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 active:scale-95 whitespace-nowrap w-full sm:w-auto"
              >
                <PlusIcon class="mr-1.5 h-4 w-4" />
                Tambah Unit
              </button>
            </div>
          </div>

          <div class="overflow-hidden rounded-xl border border-slate-200">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                  <tr>
                    <th scope="col" class="w-[30%] py-3.5 pl-4 pr-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500 sm:pl-6">Identitas Unit</th>
                    <th scope="col" class="w-[20%] px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Kode & Referensi</th>
                    <th scope="col" class="w-[30%] px-3 py-3.5 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Kontak & Lokasi</th>
                    <th scope="col" class="w-[10%] px-3 py-3.5 text-center text-xs font-bold uppercase tracking-wide text-slate-500">Status</th>
                    <th scope="col" class="w-[10%] relative py-3.5 pl-3 pr-4 text-center text-xs font-bold uppercase tracking-wide text-slate-500 sm:pr-6">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                  
                  <tr v-if="units.data.length === 0">
                    <td colspan="5" class="py-12 text-center text-sm text-slate-500">
                      <div class="flex flex-col items-center justify-center">
                        <div class="h-12 w-12 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                          <BuildingOfficeIcon class="h-6 w-6 text-slate-300" />
                        </div>
                        <p>Tidak ada unit yang cocok dengan pencarian.</p>
                      </div>
                    </td>
                  </tr>

                  <tr v-for="unit in units.data" :key="unit.id" class="group hover:bg-slate-50/80 transition-colors">
                    
                    <td class="py-4 pl-4 pr-3 sm:pl-6">
                      <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100 border border-blue-200 text-blue-700 font-bold text-xs shadow-sm">
                           {{ unit.alias ? unit.alias.substring(0, 3).toUpperCase() : 'UNK' }}
                        </div>
                        <div class="flex flex-col min-w-0">
                          <div class="text-sm font-bold text-slate-800 truncate">{{ unit.alias }}</div>
                          <div class="text-xs text-slate-500 mt-0.5 line-clamp-2" :title="unit.name">{{ unit.name }}</div>
                        </div>
                      </div>
                    </td>

                    <td class="px-3 py-4 text-sm">
                       <div class="flex flex-col gap-1.5 items-start">
                          <span class="inline-flex items-center gap-1 rounded-md bg-slate-50 px-2 py-0.5 text-[10px] font-medium text-slate-600 border border-slate-200 font-mono">
                             REF: {{ unit.ref_id || '-' }}
                          </span>
                          <span v-if="unit.parent_id && unit.parent_id !== 0" class="inline-flex items-center gap-1 rounded-md bg-yellow-50 px-2 py-0.5 text-[10px] font-medium text-yellow-700 border border-yellow-200">
                             Sub-Unit
                          </span>
                          <span v-else class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700 border border-blue-200">
                             Induk
                          </span>
                       </div>
                    </td>

                    <td class="px-3 py-4">
                      <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-1.5 text-sm text-slate-700 font-medium">
                          <PhoneIcon class="h-3.5 w-3.5 text-slate-400" />
                          {{ unit.phone || '-' }}
                        </div>
                        <div class="flex items-start gap-1.5 text-xs text-slate-500 mt-0.5">
                          <MapPinIcon class="h-3.5 w-3.5 text-slate-400 flex-shrink-0" />
                          <span class="line-clamp-1" :title="unit.address">{{ unit.address }}</span>
                        </div>
                      </div>
                    </td>

                    <td class="px-3 py-4 text-center">
                      <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset"
                        :class="unit.is_active 
                          ? 'bg-green-50 text-green-700 ring-green-600/20' 
                          : 'bg-red-50 text-red-700 ring-red-600/20'"
                      >
                        <span class="h-1.5 w-1.5 rounded-full mr-1.5" :class="unit.is_active ? 'bg-green-600' : 'bg-red-500'"></span>
                        {{ unit.is_active ? 'Aktif' : 'Non-Aktif' }}
                      </span>
                    </td>

                    <td class="py-4 pl-3 pr-4 text-center text-xs font-medium sm:pr-6">
                      <div class="flex items-center justify-center gap-2">
                        <button 
                          @click="openModal(unit)" 
                          class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-all font-semibold shadow-sm border border-blue-200"
                        >
                          Edit
                        </button>
                        <button 
                          @click="handleDelete(unit)" 
                          class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-all font-semibold shadow-sm border border-red-200"
                        >
                          Hapus
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-if="units.data.length > 0" class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-3 sm:px-6">
              <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-slate-700">
                    Menampilkan <span class="font-medium">{{ units.from }}</span> sampai <span class="font-medium">{{ units.to }}</span> dari <span class="font-medium">{{ units.total }}</span>
                  </p>
                </div>
                <div>
                  <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    <Link v-for="link in units.links" :key="link.label" :href="link.url || '#'" 
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

    <UnitFormModal
      :show="showModal"
      :unit-to-edit="selectedUnit"
      @close="closeModal"
      @save="handleSave"
    />
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UnitFormModal from '../../Components/UnitFormModal.vue'; 
import { debounce } from 'lodash';

import { 
  PlusIcon, CalendarDaysIcon, BuildingOfficeIcon, MapPinIcon, PhoneIcon,
  CheckCircleIcon, XCircleIcon, XMarkIcon, NoSymbolIcon, 
  MagnifyingGlassIcon, ChevronLeftIcon, ChevronRightIcon 
} from '@heroicons/vue/24/outline';

// PROPS FROM BACKEND
const props = defineProps({
    units: Object,
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

// --- SEARCH ---
const search = ref(props.filters.search || '');
const debouncedSearch = debounce(() => {
    router.get(route('units.index'), { search: search.value }, { preserveState: true, replace: true });
}, 500);

// --- MODAL CRUD ---
const showModal = ref(false);
const selectedUnit = ref(null); 

const openModal = (unit) => { selectedUnit.value = unit; showModal.value = true; };
const closeModal = () => { showModal.value = false; selectedUnit.value = null; };

const handleSave = (unitData) => {
    if (unitData.id) {
        // Update
        router.put(route('units.update', unitData.id), unitData, {
            onSuccess: () => closeModal(),
            onError: (err) => triggerToast(Object.values(err)[0], 'error')
        });
    } else {
        // Create
        router.post(route('units.store'), unitData, {
            onSuccess: () => closeModal(),
            onError: (err) => triggerToast(Object.values(err)[0], 'error')
        });
    }
};

// --- DELETE ---
const deleteModal = ref({ show: false, unit: null });
const handleDelete = (unit) => { deleteModal.value = { show: true, unit }; };
const closeDeleteModal = () => { deleteModal.value.show = false; };

const confirmDeleteAction = () => {
    router.delete(route('units.destroy', deleteModal.value.unit.id), {
        onSuccess: () => closeDeleteModal()
    });
};

// TIME
const currentTime = ref('');
onMounted(() => setInterval(() => currentTime.value = new Date().toLocaleTimeString('id-ID'), 1000));
</script>

<style scoped>
.toast-slide-top-enter-active{ transition: all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1); }
.toast-slide-top-leave-active { transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
.toast-slide-top-enter-from, .toast-slide-top-leave-to { opacity: 0; transform: translateY(-20px) translateX(-50%); }
</style>