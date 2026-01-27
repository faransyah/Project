<template>
  <Teleport to="body">
    <div v-if="show" @click="onClose" class="fixed inset-0 z-[999] bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300"></div>
    
    <div v-if="show" class="fixed inset-0 z-[1000] flex items-center justify-center p-4 sm:p-6">
      <div class="relative w-full max-w-4xl h-[90vh] transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all flex flex-col" @click.stop>
        
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
           <h3 class="text-lg font-bold text-slate-800">{{ isEditing ? 'Edit Barang' : 'Tambah Barang Baru' }}</h3>
           <button @click="onClose" class="p-2 bg-white hover:bg-red-50 hover:text-red-500 rounded-lg transition-colors border border-slate-200 shadow-sm"><XMarkIcon class="h-5 w-5" /></button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50 custom-scrollbar">
           <form @submit.prevent="onSave" class="space-y-6">
              
              <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                 <h4 class="text-sm font-bold text-slate-700 mb-4 border-b pb-2">Informasi Dasar</h4>
                 
                 <div class="flex flex-col sm:flex-row gap-6">
                    
                    <div class="flex flex-col items-center gap-3 sm:w-40 flex-shrink-0">
                        <div class="relative h-32 w-32 rounded-xl bg-slate-100 border-2 border-dashed border-slate-300 overflow-hidden group hover:border-blue-400 transition-colors">
                            <img v-if="previewUrl" :src="previewUrl" class="h-full w-full object-cover" />
                            
                            <div v-else class="h-full w-full flex flex-col items-center justify-center text-slate-400">
                                <PhotoIcon class="h-8 w-8 mb-1" />
                                <span class="text-[10px] font-medium">Upload Foto</span>
                            </div>

                            <label class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                <span class="text-white text-xs font-bold bg-black/50 px-3 py-1.5 rounded-full backdrop-blur-sm flex items-center gap-1">
                                    <CameraIcon class="h-3 w-3" /> Ubah
                                </span>
                                <input type="file" class="hidden" accept="image/*" @change="handleFileChange">
                            </label>
                        </div>
                        <p class="text-[10px] text-slate-500 text-center leading-tight">Format: JPG, PNG. Max: 2MB.</p>
                    </div>

                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                           <label class="block text-xs font-bold text-slate-500 mb-1">Kode Barang (ATK-)</label>
                           <div class="flex">
                              <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-slate-300 bg-slate-100 text-slate-500 text-sm font-mono">ATK-</span>
                              <input v-model="codeSuffix" type="text" class="block w-full rounded-r-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm" placeholder="001" required>
                           </div>
                        </div>
                        <div>
                           <label class="block text-xs font-bold text-slate-500 mb-1">Nama Barang</label>
                           <input v-model="form.name" type="text" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Contoh: Pulpen Standard" required>
                        </div>
                        <div>
                           <label class="block text-xs font-bold text-slate-500 mb-1">Kategori</label>
                           <select v-model="form.category_id" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                              <option value="" disabled>Pilih Kategori</option>
                              <option v-for="cat in categoryOptions" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                           </select>
                        </div>
                        <div>
                           <label class="block text-xs font-bold text-slate-500 mb-1">Satuan (UOM)</label>
                           <select v-model="form.uom" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                              <option value="" disabled>Pilih Satuan</option>
                              <option value="Pcs">Pcs</option>
                              <option value="Box">Box</option>
                              <option value="Rim">Rim</option>
                              <option value="Set">Set</option>
                              <option value="Unit">Unit</option>
                              <option value="Roll">Roll</option>
                           </select>
                        </div>
                     </div>
                 </div>
              </div>

              <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                 <h4 class="text-sm font-bold text-slate-700 mb-4 border-b pb-2">Stok & Harga</h4>
                 <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                       <label class="block text-xs font-bold text-slate-500 mb-1">Harga Acuan (Rp)</label>
                       <input v-model="form.price" type="number" min="0" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                       <label class="block text-xs font-bold text-slate-500 mb-1">Min Stok</label>
                       <input v-model="form.min_stock" type="number" min="0" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                       <label class="block text-xs font-bold text-slate-500 mb-1">Max Stok</label>
                       <input v-model="form.max_stock" type="number" min="0" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                 </div>
                 <div class="mt-4">
                    <label class="block text-xs font-bold text-slate-500 mb-1">Deskripsi / Spesifikasi</label>
                    <textarea v-model="form.description" rows="2" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Spesifikasi detail barang..."></textarea>
                 </div>
              </div>

              <div class="flex items-center gap-3 px-1">
                 <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="isActive" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    <span class="ml-3 text-sm font-medium text-slate-700">Status Aktif</span>
                 </label>
              </div>

           </form>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-white">
           <button @click="onClose" class="px-4 py-2 text-sm font-bold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all">Batal</button>
           <button @click="onSave" class="px-6 py-2 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-lg shadow-blue-500/30 flex items-center gap-2 transition-all">
             <span>Simpan Barang</span>
           </button>
        </div>

      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { XMarkIcon, PhotoIcon, CameraIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  show: Boolean,
  itemToEdit: Object,
  categoryOptions: Array
});

const emit = defineEmits(['close', 'save']);

const form = ref({});
const codeSuffix = ref('');
const isActive = ref(true);
const previewUrl = ref(null); // Untuk menampilkan gambar

const isEditing = computed(() => !!form.value.id);

watch(() => props.show, (val) => {
  if (val) {
    if (props.itemToEdit) {
      // MODE EDIT
      form.value = JSON.parse(JSON.stringify(props.itemToEdit));
      codeSuffix.value = form.value.code ? form.value.code.replace('ATK-', '') : '';
      isActive.value = form.value.status === 'Active';
      // Set preview dari database
      previewUrl.value = form.value.url_photo ? `/storage/${form.value.url_photo}` : null;
      // Reset input file fisik (biar gak double kirim kalau gak diubah)
      form.value.photo = null; 
    } else {
      // MODE CREATE
      form.value = {
        name: '', category_id: '', uom: 'Pcs', price: 0, min_stock: 5, max_stock: 100, description: '', photo: null
      };
      codeSuffix.value = '';
      isActive.value = true;
      previewUrl.value = null;
    }
  }
});

// Handle saat user memilih file
const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.value.photo = file; // Simpan file fisik ke form
        previewUrl.value = URL.createObjectURL(file); // Buat preview lokal
    }
};

const onClose = () => emit('close');

const onSave = () => {
  if (!codeSuffix.value || !form.value.name || !form.value.category_id) {
     alert('Mohon lengkapi data wajib (Kode, Nama, Kategori)!');
     return;
  }

  // Siapkan data untuk dikirim ke Parent (Index.vue)
  const payload = {
     ...form.value,
     code: 'ATK-' + codeSuffix.value,
     status: isActive.value ? 'Active' : 'Inactive',
     // form.value.photo sudah berisi file object jika ada perubahan
  };
  
  emit('save', payload);
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>