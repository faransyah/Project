<template>
  <Teleport to="body">
    <div v-if="show" @click="$emit('close')" class="fixed inset-0 z-[999] bg-slate-900/60 backdrop-blur-sm"></div>
    
    <div v-if="show" class="fixed inset-0 z-[1000] flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl w-full max-w-2xl h-[80vh] flex flex-col shadow-2xl">
         
         <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800">{{ isEditing ? 'Kelola Stok' : 'Tambah Stok Awal' }}</h3>
            <button @click="$emit('close')" class="text-slate-400 hover:text-red-500 transition-colors">
               <span class="text-2xl">×</span>
            </button>
         </div>

         <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <form @submit.prevent="submit" id="stockForm">
               
               <div v-if="!isEditing" class="grid grid-cols-2 gap-4 mb-4">
                  
                  <div class="col-span-2">
                     <label class="block text-xs font-bold text-slate-500 mb-1">Barang ATK</label>
                     <select v-model="form.item_id" class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="" disabled>Pilih Barang</option>
                        <option v-for="item in atkOptions" :key="item.id" :value="item.id">
                           {{ item.code }} - {{ item.name }}
                        </option>
                     </select>
                  </div>

                  <div>
                     <label class="block text-xs font-bold text-slate-500 mb-1">Penanggung Jawab (User)</label>
                     <select v-model="form.user_id" class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="" disabled>Pilih User</option>
                        <option v-for="user in userOptions" :key="user.id" :value="user.id">
                           {{ user.full_name || user.name }}
                        </option>
                     </select>
                  </div>

                  <div>
                     <label class="block text-xs font-bold text-slate-500 mb-1">Unit Kerja (Otomatis)</label>
                     <input type="text" :value="selectedUnitName" class="w-full rounded-lg border-slate-200 bg-slate-100 text-slate-500 text-sm cursor-not-allowed font-medium" disabled placeholder="Pilih User dulu...">
                  </div>

               </div>

               <div v-if="isEditing" class="bg-blue-50 p-4 rounded-xl border border-blue-100 mb-6">
                  <h4 class="text-sm font-bold text-blue-800 mb-3 uppercase flex items-center gap-2">
                     <span>🔄</span> Mutasi Stok
                  </h4>
                  <div class="flex gap-2 mb-4 mt-2">
                     <button type="button" @click="form.txType = 'in'" class="flex-1 py-2 rounded-lg text-sm font-bold border transition-all" :class="form.txType == 'in' ? 'bg-green-100 border-green-300 text-green-700 ring-1 ring-green-300' : 'bg-white border-slate-200 text-slate-500 hover:bg-slate-50'">Masuk (+)</button>
                     <button type="button" @click="form.txType = 'out'" class="flex-1 py-2 rounded-lg text-sm font-bold border transition-all" :class="form.txType == 'out' ? 'bg-orange-100 border-orange-300 text-orange-700 ring-1 ring-orange-300' : 'bg-white border-slate-200 text-slate-500 hover:bg-slate-50'">Keluar (-)</button>
                  </div>
                  <div class="grid grid-cols-2 gap-4">
                     <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Jumlah</label>
                        <input v-model="form.txQty" type="number" min="0" class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="0">
                     </div>
                     <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Keterangan</label>
                        <input v-model="form.txNote" type="text" class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Rusak / Hilang / Restock">
                     </div>
                  </div>
               </div>

               <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                  <h4 class="text-sm font-bold text-slate-700 mb-3 uppercase">Parameter Stok</h4>
                  <div class="grid grid-cols-2 gap-4">
                     <div v-if="!isEditing">
                        <label class="block text-xs font-bold text-slate-500 mb-1">Stok Awal</label>
                        <input v-model="form.stock" type="number" min="0" class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500" required>
                     </div>
                     <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Stok Min (Alert)</label>
                        <input v-model="form.stock_min" type="number" min="0" class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500" required>
                     </div>
                     <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Harga Satuan (Rp)</label>
                        <input v-model="form.price" type="number" min="0" class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500" required>
                     </div>
                     <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Status</label>
                        <select v-model="form.status" class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                           <option value="Active">Aktif</option>
                           <option value="Inactive">Non-Aktif</option>
                        </select>
                     </div>
                  </div>
               </div>

            </form>
         </div>

         <div class="px-6 py-4 border-t bg-slate-50 flex justify-end gap-3 rounded-b-2xl">
            <button @click="$emit('close')" class="px-4 py-2 text-slate-600 font-bold bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">Batal</button>
            <button @click="submit" class="px-6 py-2 text-white font-bold bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition-colors shadow-blue-200">Simpan</button>
         </div>

      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({ 
    show: Boolean, 
    stockToEdit: Object, 
    atkOptions: Array, 
    userOptions: Array, // Data User
    unitOptions: Array  // Data Unit (opsional, krn diambil dr user)
});

const emit = defineEmits(['close', 'save']);
const form = ref({});
const isEditing = computed(() => !!form.value.id);

// --- LOGIKA OTOMATIS UNIT ---
const selectedUnitName = computed(() => {
   if (!form.value.user_id) return '';
   
   // Cari user yang dipilih (menggunakan '==' agar aman beda tipe data)
   const user = props.userOptions?.find(u => u.id == form.value.user_id);
   
   if (user && user.unit) {
      // Set unit_id secara otomatis agar ikut terkirim ke backend
      form.value.unit_id = user.unit.id; 
      
      // Tampilkan nama unit (prioritas alias, kalau null pakai name)
      return user.unit.alias || user.unit.name;
   }
   
   return '- Unit Tidak Ditemukan -';
});

// Reset Form saat Modal Dibuka/Tutup
watch(() => props.show, (val) => {
   if(val) {
      if(props.stockToEdit) {
         // Mode Edit
         form.value = { ...props.stockToEdit, txType: 'in', txQty: 0, txNote: '' };
      } else {
         // Mode Tambah Baru (Reset)
         form.value = { 
            item_id: '', 
            user_id: '', 
            unit_id: '', // Nanti terisi otomatis
            stock: 0, 
            stock_min: 5, 
            price: 0, 
            status: 'Active' 
         };
      }
   }
});

const submit = () => emit('save', form.value);
</script>   