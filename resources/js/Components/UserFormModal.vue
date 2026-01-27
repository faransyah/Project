<template>
    <div v-if="show" class="relative z-[1050]">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>
  
        <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl transform transition-all animate-in zoom-in-95 duration-200">
                
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-800">
                        {{ userToEdit ? 'Edit Data Pegawai' : 'Tambah Pegawai Baru' }}
                    </h3>
                    <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>
  
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    
                    <div class="flex flex-col sm:flex-row gap-6">
                        <div class="flex flex-col items-center gap-3">
                            <div class="relative h-24 w-24 rounded-full bg-slate-100 border-2 border-slate-200 overflow-hidden group">
                                <img v-if="previewUrl" :src="previewUrl" class="h-full w-full object-cover" />
                                <div v-else class="h-full w-full flex items-center justify-center text-slate-400">
                                    <UserIcon class="h-12 w-12" />
                                </div>
                                
                                <label class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                    <CameraIcon class="h-8 w-8 text-white" />
                                    <input type="file" class="hidden" accept="image/*" @change="handleFileChange">
                                </label>
                            </div>
                            <span class="text-xs text-slate-500">Klik foto untuk ubah</span>
                        </div>
  
                        <div class="flex-1 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Nama Lengkap</label>
                                <input v-model="form.full_name" type="text" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Username</label>
                                    <input v-model="form.username" type="text" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">NIP</label>
                                    <input v-model="form.nip" type="text" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
  
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Email</label>
                            <input v-model="form.email" type="email" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">No. Telepon</label>
                            <input v-model="form.phone" type="text" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                    </div>
  
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Unit Kerja</label>
                            <select v-model="form.unit_id" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option :value="null" disabled>Pilih Unit</option>
                                <option v-for="unit in unitOptions" :key="unit.id" :value="unit.id">{{ unit.alias }} - {{ unit.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Jabatan</label>
                            <input v-model="form.position_name" type="text" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                    </div>
  
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Role</label>
                            <select v-model="form.role" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="User">User</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Status Akun</label>
                            <div class="mt-2 flex items-center gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" v-model="form.is_active" :value="1" class="text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-slate-700">Aktif</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" v-model="form.is_active" :value="0" class="text-red-600 focus:ring-red-500">
                                    <span class="text-sm text-slate-700">Non-Aktif</span>
                                </label>
                            </div>
                        </div>
                    </div>
  
                    <div class="border-t border-slate-100 pt-4">
                        <label class="block text-sm font-medium text-slate-700">
                            {{ userToEdit ? 'Password Baru (Kosongkan jika tidak diubah)' : 'Password' }}
                        </label>
                        <input v-model="form.password" type="password" 
                            :required="!userToEdit" 
                            class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            placeholder="********"
                        >
                    </div>
  
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="$emit('close')" class="px-5 py-2.5 bg-white text-slate-700 border border-slate-300 rounded-xl font-bold text-sm hover:bg-slate-50 transition-all">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 shadow-sm transition-all flex items-center gap-2">
                            <span v-if="loading">Menyimpan...</span>
                            <span v-else>Simpan Pegawai</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
  </template>
  
  <script setup>
  import { ref, watch } from 'vue';
  import { XMarkIcon, UserIcon, CameraIcon } from '@heroicons/vue/24/outline';
  
  const props = defineProps({
    show: Boolean,
    userToEdit: Object,
    unitOptions: Array
  });
  
  const emit = defineEmits(['close', 'save']);
  
  const loading = ref(false);
  const previewUrl = ref(null);
  
  // Struktur Form Awal
  const defaultForm = {
    id: null,
    full_name: '',
    username: '',
    email: '',
    nip: '',
    unit_id: null,
    phone: '',
    position_name: '',
    role: 'User',
    is_active: 1,
    password: '',
    photo: null // Untuk file upload
  };
  
  const form = ref({ ...defaultForm });
  
  // Reset Form saat modal dibuka/ditutup/user berubah
  watch(() => props.userToEdit, (newUser) => {
    if (newUser) {
        // Mode Edit: Copy data user
        form.value = { 
            ...newUser, 
            password: '', 
            photo: null,
            // Pastikan boolean is_active terbaca integer 1/0 agar radio button nyala
            is_active: newUser.is_active ? 1 : 0 
        };
        // Set preview jika user punya foto
        previewUrl.value = newUser.url_photo ? `/storage/${newUser.url_photo}` : null;
    } else {
        // Mode Create: Reset
        form.value = { ...defaultForm };
        previewUrl.value = null;
    }
  }, { immediate: true });
  
  // Handle File Upload & Preview
  const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.value.photo = file;
        // Buat preview lokal
        previewUrl.value = URL.createObjectURL(file);
    }
  };
  
  const submit = () => {
    loading.value = true;
    emit('save', form.value);
    // Loading dimatikan oleh parent saat success/error, tapi kita set false di sini utk safety UI
    setTimeout(() => loading.value = false, 1000);
  };
  </script>