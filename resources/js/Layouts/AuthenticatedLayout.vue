<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { 
  ArrowRightOnRectangleIcon, 
  UserIcon, 
  Bars3Icon, 
  XMarkIcon 
} from '@heroicons/vue/24/outline';

// Data User dari Laravel (Inertia Share)
const page = usePage();
const user = computed(() => page.props.auth.user);

// --- STATE SCROLL & NAVBAR ---
const isScrolled = ref(false);
const handleScroll = () => {
  isScrolled.value = window.scrollY > 20;
};

// --- NAVIGATION MENU ---
// Menggunakan route() dari Ziggy/Laravel untuk link
const navigation = computed(() => [
  { name: 'Beranda', href: route('dashboard'), active: route().current('dashboard') },
  { name: 'Pengguna', href: route('users.index'), active: route().current('users.index') || route().current('users.*') },
  { name: 'Unit Kerja', href: route('units.index'), active: route().current('units.index') },
  { name: 'Katalog Barang', href: route('items.index'), active: route().current('items.index') },
  { name: 'Stok Barang', href: route('stocks.index'), active: route().current('stocks.index') },
]);

// --- LOGOUT MODAL STATE ---
const isLogoutModalOpen = ref(false);
const openLogoutModal = () => isLogoutModalOpen.value = true;
const closeLogoutModal = () => isLogoutModalOpen.value = false;

// Fungsi Logout Real (Inertia)
const confirmLogout = () => {
  router.post(route('logout'));
  isLogoutModalOpen.value = false;
};

// --- MOBILE MENU STATE ---
const showingNavigationDropdown = ref(false);

// --- LIFECYCLE ---
onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
  <div class="min-h-screen bg-slate-50/50 font-inter text-slate-600 selection:bg-cyan-500 selection:text-white">
    
    <Transition name="modal-backdrop">
      <div v-if="isLogoutModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeLogoutModal"></div>
        
        <Transition name="modal-content">
          <div v-if="isLogoutModalOpen" class="relative w-full max-w-sm overflow-hidden rounded-3xl bg-white p-8 shadow-2xl ring-1 ring-white/50">
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-red-500 to-orange-500"></div>

            <div class="flex flex-col items-center text-center">
              <div class="group flex h-16 w-16 items-center justify-center rounded-full bg-red-50 ring-8 ring-red-50/50 mb-6 transition-transform duration-500 hover:rotate-12">
                <ArrowRightOnRectangleIcon class="h-7 w-7 text-red-600 transition-transform group-hover:translate-x-1" />
              </div>
              
              <h3 class="text-xl font-bold text-slate-900 tracking-tight">Keluar Aplikasi?</h3>
              <p class="mt-3 text-sm text-slate-500 leading-relaxed px-2">
                Anda akan diarahkan kembali ke halaman login. Pastikan pekerjaan Anda aman.
              </p>

              <div class="mt-8 flex w-full gap-3">
                <button 
                  @click="closeLogoutModal"
                  class="flex-1 rounded-xl border border-slate-200 bg-white py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300 hover:shadow-sm transition-all duration-200"
                >
                  Batal
                </button>
                <button 
                  @click="confirmLogout"
                  class="flex-1 rounded-xl bg-gradient-to-br from-red-600 to-red-700 py-3 text-sm font-semibold text-white shadow-lg shadow-red-600/30 hover:shadow-red-600/50 hover:scale-[1.02] active:scale-95 transition-all duration-200"
                >
                  Ya, Keluar
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <nav 
      class="fixed z-50 h-20 transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] flex items-center w-full"
      :class="[
        isScrolled 
          ? 'top-0 border-b border-white/60 bg-gray-200/80 backdrop-blur-xl shadow-lg shadow-slate-200/20' 
          : 'top-0 border-b border-transparent bg-slate-50/0 shadow-none'
      ]"
    >
      <div class="w-full h-full px-4 sm:px-6 lg:px-8">
        <div class="flex h-full items-center justify-between">

          <div class="flex shrink-0 items-center gap-3 cursor-pointer group" @click="router.visit(route('dashboard'))">
            <div class="relative flex h-12 w-12 items-center justify-center rounded-xl overflow-hidden shadow-sm ring-1 ring-slate-900/5 bg-white p-1.5 transition-all duration-500 group-hover:shadow-md">
              <img 
                src="/pln-click.png" 
                alt="Logo PLN" 
                class="w-full h-full object-contain scale-[1.8]"
                onerror="this.onerror=null; this.src='https://placehold.co/100x100/ffffff/000000?text=PLN'"
              >
            </div>
            <div class="flex flex-col">
               <span class="text-lg font-extrabold text-blue-600 leading-tight tracking-tight">PLN</span>
               <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest leading-none">Icon Plus</span>
            </div>
          </div>

          <div 
            class="hidden lg:flex items-center gap-1 p-1.5 rounded-full border transition-all duration-500"
            :class="isScrolled ? 'bg-slate-100/80 border-slate-200/50 shadow-inner' : 'bg-transparent border-transparent'"
          >
            <template v-for="(item, index) in navigation" :key="item.name">
              <Link
                :href="item.href"
                class="relative rounded-full px-5 py-2 text-xs font-bold transition-all duration-300 flex items-center justify-center"
                :class="item.active 
                  ? 'bg-[#009BDB] text-white shadow-md shadow-cyan-500/20 ring-1 ring-black/10 scale-105' 
                  : 'text-slate-500 hover:text-[#009BDB] hover:bg-white/50'"
              >
                {{ item.name }}
              </Link>
              <div v-if="index < navigation.length - 1" class="h-4 w-px bg-slate-300/50 mx-1"></div>
            </template>
          </div>

          <div class="flex items-center gap-5">
            
            <div class="hidden sm:flex items-center gap-3 pr-1 cursor-default select-none">
              <div class="flex flex-col items-end">
                 <span class="text-xs font-bold text-slate-700 leading-none">{{ user?.full_name || user?.name }}</span>
                 <span class="text-[10px] font-medium text-slate-400 leading-none mt-0.5 tracking-wide">{{ user?.role || 'User' }}</span>
              </div>
              <div class="h-9 w-9 rounded-full bg-gray-200 border border-white shadow-sm flex items-center justify-center text-gray-500 overflow-hidden">
                 <img v-if="user?.url_photo" :src="user.url_photo" class="h-full w-full object-cover">
                 <UserIcon v-else class="h-5 w-5 text-slate-400" />
              </div>
            </div>

            <div class="hidden sm:block h-8 w-px bg-gradient-to-b from-transparent via-slate-200 to-transparent"></div>

            <button 
              @click="openLogoutModal"
              class="hidden sm:flex group relative h-9 w-9 items-center justify-center rounded-full bg-red-50 border border-red-100 text-red-500 hover:bg-red-600 hover:border-red-600 hover:text-white transition-all duration-300 shadow-sm hover:shadow-md hover:shadow-red-600/20"
              title="Keluar Sistem"
            >
              <ArrowRightOnRectangleIcon class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" />
            </button>

            <div class="flex items-center lg:hidden">
                <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                    <component :is="showingNavigationDropdown ? XMarkIcon : Bars3Icon" class="h-6 w-6" />
                </button>
            </div>

          </div>
        </div>
      </div>

      <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="lg:hidden absolute top-20 left-0 w-full bg-white border-b border-gray-200 shadow-xl">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <Link 
                v-for="item in navigation" 
                :key="item.name"
                :href="item.href"
                class="block w-full pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out rounded-r-md"
                :class="item.active 
                    ? 'border-[#009BDB] text-[#009BDB] bg-blue-50' 
                    : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300'"
            >
                {{ item.name }}
            </Link>
        </div>
        <div class="pt-4 pb-4 border-t border-gray-200 bg-slate-50">
            <div class="px-4 flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                    <img v-if="user?.url_photo" :src="user.url_photo" class="h-full w-full object-cover">
                    <UserIcon v-else class="h-6 w-6 text-slate-400" />
                </div>
                <div>
                    <div class="font-medium text-base text-gray-800">{{ user?.full_name || user?.name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ user?.email }}</div>
                </div>
            </div>
            <div class="mt-3 space-y-1 px-4">
                <Link :href="route('profile.edit')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Profil</Link>
                <button @click="openLogoutModal" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md font-bold">Keluar</button>
            </div>
        </div>
      </div>
    </nav>

    <main class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-24 pb-12">
      <div class="min-h-[600px]">
        <slot />
      </div>
    </main>

  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
.font-inter { font-family: 'Inter', sans-serif; }

/* --- ANIMASI MODAL --- */
.modal-backdrop-enter-active, .modal-backdrop-leave-active { transition: opacity 0.3s ease; }
.modal-backdrop-enter-from, .modal-backdrop-leave-to { opacity: 0; }

.modal-content-enter-active { transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
.modal-content-leave-active { transition: all 0.2s ease-in; }
.modal-content-enter-from { opacity: 0; transform: scale(0.9) translateY(20px); }
.modal-content-leave-to { opacity: 0; transform: scale(0.95) translateY(10px); }
</style>