<template>
  <Head title="Dashboard Admin" />

  <AuthenticatedLayout>
    <div class="space-y-6 pb-20 animate-fade-in-up">

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
            <div class="ml-3 flex-1">
              <p class="text-sm font-semibold">{{ toast.message }}</p>
            </div>
            <button @click="toast.show = false" class="ml-4 flex-shrink-0 text-slate-400 hover:text-slate-600 transition-colors">
              <XMarkIcon class="h-5 w-5" />
            </button>
          </div>
        </div>
      </Transition>

      <Transition name="modal-fade">
        <div v-if="approvalModal.show" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeApprovalModal"></div>

          <div class="relative w-full max-w-4xl h-auto max-h-[85vh] transform overflow-hidden rounded-3xl bg-white shadow-2xl transition-all flex flex-col animate-scale-up">
            
            <div class="relative bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-6 shrink-0 border-b border-white/10">
              <div class="flex items-start justify-between text-white">
                <div>
                  <h3 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                    <ClipboardDocumentCheckIcon class="h-7 w-7 text-blue-200" />
                    Review Permintaan
                  </h3>
                  <div class="flex flex-wrap gap-3 mt-3">
                     <div class="flex items-center gap-2 bg-white/20 backdrop-blur-md px-3 py-1 rounded-full border border-white/10 text-xs font-semibold text-white">
                        <UserIcon class="h-3.5 w-3.5 opacity-80" /> {{ approvalModal.data?.user }}
                     </div>
                     <div class="flex items-center gap-2 bg-white/20 backdrop-blur-md px-3 py-1 rounded-full border border-white/10 text-xs font-semibold text-white">
                        <BuildingOfficeIcon class="h-3.5 w-3.5 opacity-80" /> {{ approvalModal.data?.unit }}
                     </div>
                  </div>
                </div>
                <button @click="closeApprovalModal" class="rounded-full bg-white/10 p-2 hover:bg-white/20 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-300">
                  <XMarkIcon class="h-6 w-6 text-white" />
                </button>
              </div>
            </div>

            <div class="p-8 overflow-y-auto custom-scrollbar bg-slate-50 flex-1">
              <div class="space-y-5">
                
                <div class="bg-white rounded-2xl border p-5 shadow-sm transition-all flex flex-col md:flex-row gap-6 items-start md:items-center group hover:shadow-md"
                  :class="{
                    'border-red-200 bg-red-50/20': approvalModal.form.action === 'reject',
                    'border-emerald-200 bg-emerald-50/20': approvalModal.form.action === 'approve',
                    'border-slate-200': !approvalModal.form.action
                  }"
                >
                  <div class="flex items-start gap-5 flex-1 w-full md:w-auto">
                     <div class="h-20 w-20 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0 relative shadow-sm">
                        <img v-if="approvalModal.data?.url_photo" :src="'/storage/' + approvalModal.data.url_photo" class="h-full w-full object-cover" />
                        <CubeIcon v-else class="h-10 w-10 text-slate-300" />
                     </div>
                     
                     <div class="min-w-0 flex-1">
                        <p class="text-base font-bold text-slate-800">{{ approvalModal.data?.itemName }}</p>
                        <p class="text-xs text-slate-500 font-mono bg-slate-100 w-fit px-2 py-0.5 rounded mt-1">
                          Permintaan: <span class="font-bold text-blue-600">{{ approvalModal.data?.itemCount }} Pcs</span>
                        </p>
                     </div>
                  </div>

                  <div class="flex flex-col sm:flex-row gap-5 w-full md:w-auto items-stretch sm:items-start">
                      
                      <div class="flex flex-col gap-2 shrink-0">
                          <button 
                            @click="approvalModal.form.action = 'approve'"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 border"
                            :class="approvalModal.form.action === 'approve' 
                              ? 'bg-emerald-600 text-white border-emerald-600 shadow-lg shadow-emerald-200' 
                              : 'bg-white text-slate-400 border-slate-200 hover:border-emerald-300 hover:text-emerald-500'"
                          >
                            <CheckCircleIcon class="h-4 w-4" /> Terima
                          </button>
                          <button 
                            @click="approvalModal.form.action = 'reject'"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 border"
                            :class="approvalModal.form.action === 'reject' 
                              ? 'bg-red-600 text-white border-red-600 shadow-lg shadow-red-200' 
                              : 'bg-white text-slate-400 border-slate-200 hover:border-red-300 hover:text-red-500'"
                          >
                            <NoSymbolIcon class="h-4 w-4" /> Tolak
                          </button>
                      </div>

                      <div class="flex-1 min-w-[200px]">
                          <div v-if="approvalModal.form.action === 'approve'" class="bg-white p-4 rounded-xl border border-emerald-200 shadow-sm animate-fade-in-up h-full flex flex-col justify-center">
                              <p class="text-[10px] font-bold text-emerald-600 uppercase mb-1">Konfirmasi Stok</p>
                              <p class="text-xs text-slate-500">Stok akan ditambahkan ke user: <strong>{{ approvalModal.data?.user }}</strong></p>
                          </div>

                          <div v-else class="bg-white p-4 rounded-xl border border-red-200 shadow-sm animate-fade-in-up h-full flex flex-col justify-center">
                              <p class="text-[10px] font-bold text-red-600 uppercase mb-1">Konfirmasi Tolak</p>
                              <p class="text-xs text-slate-500">Permintaan ini akan dibatalkan.</p>
                          </div>
                      </div>
                  </div>
                </div>

              </div>
            </div>

            <div class="bg-white border-t border-slate-100 p-6 shrink-0 flex justify-end gap-3 z-20">
                <button @click="closeApprovalModal" class="px-6 py-3 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 border border-slate-200 transition-all">
                  Batal
                </button>
                <button 
                  @click="submitApproval"
                  :disabled="processingId !== null"
                  class="px-8 py-3 rounded-xl text-sm font-bold text-white shadow-lg transition-all flex items-center justify-center gap-2 transform active:scale-95"
                  :class="approvalModal.form.action === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-200' : 'bg-red-600 hover:bg-red-700 shadow-red-200'"
                >
                  <svg v-if="processingId === approvalModal.data?.id" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                  <CheckBadgeIcon v-else class="h-5 w-5" />
                  {{ approvalModal.form.action === 'approve' ? 'Setujui Permintaan' : 'Tolak Permintaan' }}
                </button>
            </div>

          </div>
        </div>
      </Transition>

      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-6">
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-800">Beranda</h1>
          <p class="mt-2 text-base text-slate-500">Rangkuman sistem ATK <span class="font-bold text-blue-500">Icon Plus</span>.</p>
        </div>
        <div class="hidden sm:flex flex-col items-end justify-center">
          <div class="flex items-center gap-2 text-sm font-bold text-slate-700">
            <CalendarDaysIcon class="h-4 w-4 text-slate-400" />
            <span>{{ currentDate }}</span>
          </div>
          <div class="flex items-center gap-1.5 mt-1">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
            </span>
            <span class="text-xs font-mono font-medium text-slate-500 tracking-wide">{{ currentTime }} WIB</span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-slate-100 transition-all hover:-translate-y-1 hover:shadow-lg hover:border-yellow-400">
          <div class="flex items-center justify-between">
            <div class="relative z-10">
               <p class="text-sm font-bold text-slate-400 uppercase tracking-wider group-hover:text-yellow-600 transition-colors">Unit Aktif</p>
               <p class="mt-1 text-3xl font-black text-slate-800 tracking-tight">{{ stats.activeUnits }}</p>
            </div>
            <div class="relative z-10 flex h-12 w-12 items-center justify-center rounded-2xl bg-yellow-50 text-yellow-600 transition-all group-hover:scale-110">
              <BoltSolidIcon class="h-7 w-7" />
            </div>
          </div>
        </div>
        <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-slate-100 transition-all hover:-translate-y-1 hover:shadow-lg hover:border-violet-400">
          <div class="flex items-center justify-between">
            <div class="relative z-10">
              <p class="text-sm font-bold text-slate-400 uppercase tracking-wider group-hover:text-violet-600 transition-colors">Katalog Barang</p>
              <p class="mt-1 text-3xl font-black text-slate-800 tracking-tight">{{ stats.totalATK }}</p>
            </div>
            <div class="relative z-10 flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 transition-all group-hover:scale-110">
              <ClipboardDocumentListIcon class="h-6 w-6" />
            </div>
          </div>
        </div>
        <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-slate-100 transition-all hover:-translate-y-1 hover:shadow-lg hover:border-emerald-400">
          <div class="flex items-center justify-between">
            <div class="relative z-10">
              <p class="text-sm font-bold text-slate-400 uppercase tracking-wider group-hover:text-emerald-600 transition-colors">Total Stok</p>
              <p class="mt-1 text-3xl font-black text-slate-800 tracking-tight">{{ statsDisplay.totalStock }}</p>
            </div>
            <div class="relative z-10 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 transition-all group-hover:scale-110">
              <ArchiveBoxIcon class="h-6 w-6" />
            </div>
          </div>
        </div>
        <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-slate-100 transition-all hover:-translate-y-1 hover:shadow-lg hover:border-rose-400">
          <div class="flex items-center justify-between">
            <div class="relative z-10">
              <p class="text-sm font-bold text-slate-400 uppercase tracking-wider group-hover:text-rose-600 transition-colors">Stok Menipis</p>
              <p class="mt-1 text-3xl font-black text-rose-600 tracking-tight">{{ stats.lowStockCount }}</p>
            </div>
            <div class="relative z-10 flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 transition-all group-hover:scale-110">
              <ExclamationTriangleIcon class="h-6 w-6" />
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-7 xl:col-span-8">
          <div class="h-full rounded-2xl bg-white p-6 shadow-sm border border-slate-100">
            <div class="mb-6">
              <h3 class="text-lg font-bold text-slate-800">Tren Permintaan</h3>
              <p class="text-sm text-slate-500">Statistik permintaan barang per bulan.</p>
            </div>
            <div class="-ml-2">
                <apexchart type="bar" height="350" :options="barChartOptions" :series="barChartSeries"></apexchart>
            </div>
          </div>
        </div>

        <div class="col-span-12 lg:col-span-5 xl:col-span-4">
          <div class="h-full rounded-2xl bg-white p-6 shadow-sm border border-slate-100 flex flex-col">
            <div class="mb-4">
              <h3 class="text-lg font-bold text-slate-800">Kategori Stok</h3>
              <p class="text-sm text-slate-500">Distribusi stok berdasarkan kategori.</p>
            </div>
            <div class="flex-1 flex flex-col items-center justify-center py-4">
              <apexchart ref="donutChartRef" type="donut" width="300" :options="donutChartOptions" :series="donutChartSeries"></apexchart>
            </div>
          </div>
        </div>

        <div class="col-span-12 lg:col-span-7">
          <div class="h-full rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-100">
              <div class="flex items-center justify-between">
                <div>
                  <h2 class="text-lg font-bold text-slate-800">Permintaan Persetujuan</h2>
                  <p class="text-sm text-slate-500">Menunggu aksi dari Administrator.</p>
                </div>
                <span v-if="pendingApprovals.length" class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700 border border-blue-100">
                  {{ pendingApprovals.length }} pending
                </span>
              </div>
            </div>

            <div class="flex-1 overflow-hidden p-0">
              <div class="custom-scrollbar h-[400px] overflow-y-auto p-6 pt-0">
                <ul role="list" class="space-y-4 mt-6">
                  
                  <li v-if="pendingApprovals.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center mb-4">
                      <DocumentCheckIcon class="h-8 w-8 text-slate-300" />
                    </div>
                    <p class="text-slate-500 font-medium">Tidak ada permintaan baru.</p>
                  </li>

                  <li v-for="trx in pendingApprovals" :key="trx.id" class="group flex flex-col sm:flex-row sm:items-start justify-between gap-4 rounded-2xl border border-slate-100 bg-white p-5 transition-all hover:border-blue-200 hover:bg-blue-50/20 hover:shadow-md">
                    <div class="flex items-start gap-4 flex-1">
                      <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-black text-lg shadow-md shadow-blue-500/20">
                        {{ trx.user_initial || (trx.user ? trx.user.charAt(0) : '?') }}
                      </div>
                      <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between">
                           <p class="text-sm font-bold text-slate-900">{{ trx.user }}</p>
                           <span class="text-[10px] text-slate-400 font-mono">{{ trx.created_at ? formatTime(trx.created_at) : '-' }}</span>
                        </div>
                        <p class="text-xs text-slate-500 font-semibold mb-2">{{ trx.unit }}</p>
                        
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl relative group-hover:bg-white transition-colors">
                          <div class="absolute left-0 top-3 bottom-3 w-1 bg-blue-500 rounded-r-full"></div>
                          <p class="text-xs font-bold text-slate-700 pl-3 flex items-center gap-2">
                             <ArchiveBoxIcon class="h-3.5 w-3.5 text-slate-400" />
                             {{ trx.itemName }} <span class="text-blue-600">({{ trx.itemCount }} Pcs)</span>
                          </p>
                        </div>
                      </div>
                    </div>
                    
                    <div class="flex flex-col justify-center gap-2 sm:ml-auto w-full sm:w-auto pt-1">
                      <button @click="openApprovalModal(trx)" type="button" class="w-full sm:w-auto rounded-xl bg-blue-600 px-5 py-3 text-xs font-bold text-white shadow-lg shadow-blue-600/20 hover:bg-blue-700 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                          <ClipboardDocumentCheckIcon class="h-4 w-4" /> Review
                      </button>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <div class="col-span-12 lg:col-span-5 flex flex-col gap-6">
            
            <div class="rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden flex-1 max-h-[400px] flex flex-col">
                <div class="p-6 border-b border-slate-100">
                  <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                    </span>
                    Peringatan Stok Rendah
                  </h2>
                </div>
                <div class="flex-1 overflow-y-auto custom-scrollbar p-6 pt-0">
                    <ul role="list" class="divide-y divide-slate-100 mt-2">
                      <li v-if="lowStockItems.length === 0" class="py-12 text-center">
                        <div class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-green-50 text-green-600 mb-3">
                          <CheckCircleIcon class="h-6 w-6" />
                        </div>
                        <p class="text-sm text-slate-500">Stok aman.</p>
                      </li>
                      <li v-for="item in lowStockItems" :key="item.id" class="py-4 flex items-center justify-between group hover:bg-slate-50 -mx-2 px-2 rounded-xl transition-colors">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                          <div class="h-10 w-10 rounded-lg bg-slate-50 border border-slate-200 flex-shrink-0 overflow-hidden flex items-center justify-center">
                             <img v-if="item.url_photo" :src="'/storage/' + item.url_photo" class="h-full w-full object-cover">
                             <ExclamationTriangleIcon v-else class="h-5 w-5 text-red-600" />
                          </div>
                          <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-slate-900">{{ item.name }}</p>
                            <p class="truncate text-xs text-slate-500 font-medium">{{ item.unit }}</p>
                          </div>
                        </div>
                        <div class="text-right pl-4">
                          <p class="text-sm font-black text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-100">{{ item.stock }}</p>
                        </div>
                      </li>
                    </ul>
                </div>
            </div>

            <div class="rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden flex-1 max-h-[400px] flex flex-col">
                <div class="p-6 border-b border-slate-100">
                   <h3 class="text-lg font-bold text-slate-800">Aktivitas Terbaru</h3>
                </div>
                <div class="flex-1 overflow-y-auto custom-scrollbar p-6 pt-0">
                   <div v-if="recentActivity.length === 0" class="py-8 text-center text-sm text-slate-500">Belum ada aktivitas.</div>
                   <div class="relative border-l border-slate-200 ml-3 mt-6 space-y-6">
                      <div v-for="activity in recentActivity" :key="activity.id" class="relative pl-6 group">
                        <span class="absolute -left-[9px] top-1 h-4 w-4 rounded-full border-2 border-white shadow-sm" :class="activity.type === 'IN' ? 'bg-green-500' : 'bg-amber-500'"></span>
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1">
                          <div>
                            <p class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">
                                {{ activity.itemName }} 
                                <span class="font-extrabold" :class="activity.type === 'IN' ? 'text-green-600' : 'text-amber-600'">
                                    ({{ activity.type === 'IN' ? '+' : '-' }}{{ activity.qty }})
                                </span>
                            </p>
                            <p class="text-xs text-slate-500 mt-0.5 font-medium">{{ activity.actor }} • {{ activity.type === 'IN' ? 'Masuk' : 'Keluar' }}</p>
                          </div>
                          <span class="text-[10px] font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-full whitespace-nowrap border border-slate-100">
                              {{ formatTimeAgo(activity.created_at) }}
                          </span>
                        </div>
                      </div>
                   </div>
                </div>
            </div>

        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'; 
import { Head, router, usePage } from '@inertiajs/vue3'; 
import VueApexCharts from 'vue3-apexcharts';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; 

import {
  ClipboardDocumentListIcon, ClockIcon, CalendarDaysIcon, ArchiveBoxIcon,
  ExclamationTriangleIcon, DocumentCheckIcon, CheckCircleIcon, XCircleIcon, 
  XMarkIcon, NoSymbolIcon, BuildingOfficeIcon, UserIcon, CubeIcon, 
  CheckBadgeIcon, ClipboardDocumentCheckIcon
} from '@heroicons/vue/24/outline';
import { BoltIcon as BoltSolidIcon } from '@heroicons/vue/24/solid';

// =======================================================================
// PROPS (DATA DARI BACKEND LARAVEL/INERTIA)
// =======================================================================
const props = defineProps({
  stats: Object,            // { activeUnits, totalATK, totalStock, lowStockCount }
  lowStockItems: Array,     // [{ id, name, unit, stock, url_photo }, ...]
  pendingApprovals: Array,  // [{ id, user, user_initial, unit, itemName, itemCount, created_at, url_photo }, ...]
  categoryStats: Array,     // [{ name: 'Kertas', value: 10 }, ...]
  recentActivity: Array,    // [{ id, type, itemName, qty, actor, created_at }, ...]
});

// =======================================================================
// STATE UTILS (TOAST, TIME)
// =======================================================================
const toast = ref({ show: false, message: '', type: 'success' });
const processingId = ref(null); 
const currentTime = ref('');
const currentDate = ref('');
let timeInterval = null;

const triggerToast = (message, type = 'success') => {
  toast.value = { message, type, show: true };
  setTimeout(() => { toast.value.show = false; }, 3000); 
};

// Listen to Flash Messages from Laravel
const page = usePage();
watch(() => page.props.flash, (flash) => {
    if(flash?.success) triggerToast(flash.success, 'success');
    if(flash?.error) triggerToast(flash.error, 'error');
}, { deep: true });

const updateTime = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
  currentDate.value = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};

onMounted(() => { updateTime(); timeInterval = setInterval(updateTime, 1000); });
onUnmounted(() => clearInterval(timeInterval));

const formatTime = (isoString) => {
    if (!isoString) return '-';
    return new Date(isoString).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace(/\./g, ':');
};

const formatTimeAgo = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const diff = Math.floor((new Date() - date) / 1000);
    if (diff < 60) return 'Baru saja';
    if (diff < 3600) return `${Math.floor(diff/60)} menit lalu`;
    if (diff < 86400) return `${Math.floor(diff/3600)} jam lalu`;
    return date.toLocaleDateString('id-ID');
};

const statsDisplay = computed(() => {
  const s = props.stats || { totalStock: 0 };
  return { ...s, totalStock: s.totalStock > 1000 ? (s.totalStock/1000).toFixed(1) + 'k' : s.totalStock };
});

// =======================================================================
// MODAL APPROVAL LOGIC
// =======================================================================
const approvalModal = ref({
    show: false,
    data: null, // Data item yang diklik
    form: {
        action: 'approve', // approve | reject
        reason: ''
    }
});

const openApprovalModal = (req) => {
    approvalModal.value.data = req;
    approvalModal.value.form = { action: 'approve', reason: '' };
    approvalModal.value.show = true;
};

const closeApprovalModal = () => {
    approvalModal.value.show = false;
    approvalModal.value.data = null;
};

const submitApproval = () => {
    const req = approvalModal.value.data;
    const action = approvalModal.value.form.action;
    const reason = approvalModal.value.form.reason;

    if (!req) return;
    
    // Validasi Reject wajib alasan (Opsional, sesuaikan kebutuhan)
    // if (action === 'reject' && !reason.trim()) {
    //     triggerToast('Alasan penolakan wajib diisi!', 'error');
    //     return;
    // }

    processingId.value = req.id;
    
    // SESUAIKAN DENGAN ROUTE NAME DI LARAVEL
    const routeName = action === 'approve' ? 'approvals.approve' : 'approvals.reject';
    const payload = action === 'reject' ? { note: reason } : {};

    router.post(route(routeName, req.id), payload, {
        preserveScroll: true,
        onFinish: () => {
            processingId.value = null;
            closeApprovalModal();
        },
        onError: (err) => triggerToast(Object.values(err)[0] || 'Gagal memproses.', 'error')
    });
};

// =======================================================================
// CHART CONFIGURATION
// =======================================================================
const chartColors = ['#2563EB', '#22C55E', '#EAB308', '#EF4444', '#A855F7']; 

// Donut Chart (Categories)
const donutChartSeries = computed(() => (props.categoryStats || []).map(cat => Number(cat.value)));
const donutChartOptions = computed(() => ({
  chart: { type: 'donut', fontFamily: 'Inter, sans-serif' },
  labels: (props.categoryStats || []).map(cat => cat.name),
  colors: chartColors,
  plotOptions: { pie: { donut: { size: '75%', labels: { show: true, total: { show: true, label: 'Total', formatter: () => props.stats?.totalStock || 0 } } } } },
  legend: { show: false }, 
  dataLabels: { enabled: false },
}));

// Bar Chart (Monthly Trends - Dummy Data for Visuals as BE doesn't provide yet)
// Note: You can replace this with props.monthlyTrends if backend provides it later
const barChartSeries = [{ name: 'Permintaan', data: [30, 40, 35, 50, 49, 60] }];
const barChartOptions = {
  chart: { type: 'bar', height: 350, toolbar: { show: false } },
  plotOptions: { bar: { borderRadius: 4, horizontal: false, columnWidth: '55%' } },
  dataLabels: { enabled: false },
  xaxis: { categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'], axisBorder: { show: false }, axisTicks: { show: false } },
  grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
  colors: ['#3b82f6']
};

</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.toast-slide-top-enter-active{ transition: all 0.5s ease-out; }
.toast-slide-top-enter-from { opacity: 0; transform: translateY(-20px) translateX(-50%); }

/* Animations */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.animate-scale-up { animation: scaleUp 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes scaleUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.animate-fade-in-up { animation: fadeInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>