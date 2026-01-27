<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
// Saya tambahkan UserIcon biar lebih cocok untuk Username
import { EnvelopeIcon, LockClosedIcon, EyeIcon, EyeSlashIcon, UserIcon } from '@heroicons/vue/24/solid';

defineProps({
    status: { type: String },
});

const form = useForm({
    // Tetap gunakan 'email' sebagai key agar cocok dengan LoginRequest.php
    email: '',    
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk Aplikasi" />

    <div class="font-inter flex min-h-screen flex-col items-center justify-center bg-primary-blue px-4 py-12 overflow-hidden">
        
        <Transition appear name="fade-in-up">
            <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl ring-2 ring-gray-100 transition-all duration-300">
                
                <div class="relative flex flex-col items-center justify-center p-8 text-center text-white border-b border-white/30 bg-primary-blue">
                    
                    <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg width=\'100%25\' height=\'100%25\' xmlns=\'http://www.w3.org/2000/svg\'%3e%3cdefs%3e%3clinearGradient id=\'grad1\' x1=\'0%25\' y1=\'0%25\' x2=\'100%25\' y2=\'100%25\'%3e%3cstop offset=\'0%25\' stop-color=\'%23ffffff\' stop-opacity=\'0.2\' /%3e%3cstop offset=\'100%25\' stop-color=\'%23ffffff\' stop-opacity=\'0\' /%3e%3c/linearGradient%3e%3c/defs%3e%3crect width=\'100%25\' height=\'100%25\' fill=\'url(%23grad1)\' /%3e%3c/svg%3e');"></div>

                    <div class="relative z-10 space-y-3">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-xl overflow-hidden shadow-lg ring-4 ring-white/30 bg-white p-3 transform hover:scale-105 transition-transform duration-500">
                            <img 
                                src="/pln-click.png" 
                                alt="Logo PLN Click" 
                                class="w-full h-full object-contain"
                                onerror="this.style.display='none'" 
                            />
                            <svg class="w-10 h-10 text-[#00A2B9]" fill="none" viewBox="0 0 24 24" stroke="currentColor" v-if="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>

                        <p class="text-base font-semibold text-white/90 pt-1 max-w-xs mx-auto tracking-wide">
                            Sistem Manajemen Aset & Stok
                        </p>
                    </div>
                </div>
                
                <div class="p-8">
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                        Masuk ke Akun Anda
                    </h2>

                    <div v-if="Object.keys(form.errors).length > 0" class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="ml-3">
                                <p class="text-sm text-red-700 font-bold">Gagal Masuk</p>
                                <p class="text-xs text-red-600">Periksa kembali email/username dan password anda.</p>
                            </div>
                        </div>
                    </div>

                    <form class="space-y-6" @submit.prevent="submit">
                        
                        <div>
                            <label for="email" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                                Email / Username
                            </label>
                            <div class="relative group">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <UserIcon class="h-5 w-5 text-gray-400 group-focus-within:text-primary-blue transition-colors" />
                                </div>
                                <input 
                                    id="email" 
                                    type="text" 
                                    v-model="form.email" 
                                    required
                                    autofocus
                                    class="block w-full rounded-xl border border-gray-300 bg-white py-3 pl-10 pr-3 text-gray-900 placeholder-gray-400 focus:border-primary-blue focus:shadow-lg focus:outline-none focus:ring-1 focus:ring-primary-blue sm:text-sm transition-all"
                                    placeholder="Masukkan email atau username"
                                    :class="{ 'border-red-500 ring-red-500': form.errors.email }"
                                />
                            </div>
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                        </div>
                        
                        <div>
                            <label for="password" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Password</label>
                            <div class="relative group">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <LockClosedIcon class="h-5 w-5 text-gray-400 group-focus-within:text-primary-blue transition-colors" />
                                </div>
                                <input 
                                    id="password" 
                                    :type="showPassword ? 'text' : 'password'" 
                                    v-model="form.password" 
                                    required
                                    class="block w-full rounded-xl border border-gray-300 bg-white py-3 pl-10 pr-10 text-gray-900 placeholder-gray-400 focus:border-primary-blue focus:shadow-lg focus:outline-none focus:ring-1 focus:ring-primary-blue sm:text-sm transition-all"
                                    placeholder="Masukkan password"
                                    :class="{ 'border-red-500 ring-red-500': form.errors.password }"
                                />
                                <button 
                                    type="button" 
                                    class="absolute inset-y-0 right-0 z-10 flex items-center pr-3 text-gray-400 hover:text-primary-blue transition-colors outline-none"
                                    @click="showPassword = !showPassword"
                                >
                                    <EyeSlashIcon v-if="showPassword" class="h-5 w-5" />
                                    <EyeIcon v-else class="h-5 w-5" />
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                        </div>

                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="relative flex w-full justify-center rounded-xl bg-primary-blue px-4 py-3 text-sm font-bold text-white shadow-xl transition-all duration-300 hover:bg-[#008c9e] hover:shadow-2xl hover:translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-[#00A2B9]/30 disabled:opacity-70 disabled:cursor-not-allowed !mt-8"
                        >
                            <span v-if="form.processing" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memproses...
                            </span>
                            <span v-else>MASUK APLIKASI</span>
                        </button>

                    </form>

                </div>
            </div>
        </Transition>
        
        <div class="mt-8 text-center text-xs text-white/80 font-medium tracking-wide">
            &copy; 2025 PLN Icon Plus. All rights reserved.
        </div>
    </div>
</template>

<style>
/* CSS Variable untuk Warna Utama (Tosca PLN) */
:root {
  --color-primary-blue: #00A2B9; /* Warna Tosca Khas PLN Click */
}

@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.font-inter {
  font-family: 'Inter', sans-serif;
}

/* Utility Class Custom */
.bg-primary-blue {
    background-color: var(--color-primary-blue);
}
.text-primary-blue {
    color: var(--color-primary-blue);
}
.focus\:border-primary-blue:focus {
    border-color: var(--color-primary-blue);
}
.focus\:ring-primary-blue:focus {
    --tw-ring-color: var(--color-primary-blue);
}

/* Animasi Muncul */
.fade-in-up-enter-active {
  transition: all 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.fade-in-up-enter-from {
  opacity: 0;
  transform: translateY(30px) scale(0.95);
}
.fade-in-up-enter-to {
  opacity: 1;
  transform: translateY(0) scale(1);
}
</style>