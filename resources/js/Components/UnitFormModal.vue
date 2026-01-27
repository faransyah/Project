<template>
  <Teleport to="body">
    <div v-if="show" @click="onClose" class="fixed inset-0 z-[999] bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300"></div>

    <div v-if="show" class="fixed inset-0 z-[1000] flex items-center justify-center p-4 sm:p-6">
      
      <div class="relative w-full max-w-4xl h-[85vh] transform overflow-hidden rounded-2xl bg-slate-50 text-left shadow-2xl transition-all ring-1 ring-slate-900/5 flex flex-col" @click.stop>
        
        <div class="flex items-center justify-between border-b border-slate-200 px-8 py-5 bg-white z-20 flex-shrink-0 shadow-sm relative overflow-hidden">
          <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
          <div class="flex items-center gap-5">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30 ring-1 ring-white">
              <BuildingOfficeIcon class="h-7 w-7" />
            </div>
            <div>
              <h3 class="text-xl font-extrabold leading-6 text-slate-800 tracking-tight">
                {{ isEditing ? 'Edit Data Unit' : 'Registrasi Unit Baru' }}
              </h3>
              <p class="text-sm text-slate-500 mt-0.5 font-medium">
                Kelola detail unit operasional dan struktur organisasi.
              </p>
            </div>
          </div>
          <button 
            @click="onClose" 
            class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-red-500 transition-all"
            title="Tutup Modal"
          >
            <XMarkIcon class="h-7 w-7" />
          </button>
        </div>

        <div class="p-8 overflow-y-auto custom-scrollbar flex-1 bg-slate-50/80">
          <form @submit.prevent="onSave" class="space-y-8 max-w-5xl mx-auto pb-10">
            
            <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
              <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500"></div>
              <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wide mb-6 flex items-center gap-2 border-b border-slate-100 pb-3">
                <InformationCircleIcon class="h-5 w-5 text-emerald-600" />
                Identitas Utama
              </h4>
              
              <div class="space-y-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Ref ID (Kode) <span class="text-red-500">*</span></label>
                        <div class="flex rounded-xl shadow-sm">
                          <div class="relative">
                            <select 
                              v-model="refPrefix" 
                              class="h-full rounded-l-xl border-y border-l border-r-0 border-slate-300 bg-slate-50 text-slate-600 font-bold text-sm pl-3 pr-7 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 cursor-pointer hover:bg-slate-100 transition-colors appearance-none"
                              @change="handlePrefixChange"
                            >
                              <option value="UID">UID</option>
                              <option value="UIW">UIW</option>
                              <option value="UP3">UP3</option>
                              <option value="ULP">ULP</option>
                              <option value="PUSAT">PUSAT</option>
                            </select>
                            <ChevronDownIcon class="h-3 w-3 text-slate-400 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none" />
                          </div>
                          <div class="relative flex-1">
                             <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-slate-400 font-bold text-sm">-</span>
                             </div>
                             <input 
                               v-model="refNumber" 
                               @input="handleRefNumberInput"
                               type="text" 
                               maxlength="5" 
                               required 
                               class="block w-full rounded-r-xl border border-slate-300 bg-white text-slate-800 font-semibold tracking-wide focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 pl-6 py-2.5 transition-all shadow-sm hover:border-emerald-300 sm:text-sm" 
                               placeholder="1001" 
                             />
                          </div>
                        </div>
                        <p class="text-[10px] text-emerald-600 pt-1 ml-1 font-medium">
                           Preview Kode: <strong>{{ computedRefId || '-' }}</strong>
                        </p>
                      </div>

                      <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Parent ID</label>
                        <div class="relative group">
                           <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                              <QueueListIcon class="h-5 w-5 text-slate-400" />
                           </div>
                           <input 
                            v-model.number="localUnit.parent_id" 
                            @input="filterParentId"
                            type="text" 
                            class="form-input-bold pl-11" 
                            placeholder="0" 
                          />
                        </div>
                        <p class="text-[10px] text-slate-400 pt-1 ml-1">Isi 0 jika ini adalah Unit Induk / Wilayah.</p>
                      </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                      <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Alias Unit (Wilayah) <span class="text-red-500">*</span></label>
                        <div class="relative group">
                          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors group-focus-within:text-emerald-600">
                              <span class="text-sm font-bold text-slate-500">{{ refPrefix }}</span>
                          </div>
                          <input 
                            v-model="unitRegion" 
                            @input="handleRegionInput"
                            type="text" 
                            maxlength="30" 
                            required 
                            class="form-input-bold capitalize"
                            :class="refPrefix.length > 3 ? 'pl-[4.5rem]' : 'pl-14'"
                            placeholder="Jatim / Surabaya" 
                          />
                        </div>
                        <p class="text-[10px] text-emerald-600 pt-1 ml-1 font-medium flex items-center gap-1">
                           <TagIcon class="h-3 w-3" />
                           Preview Alias: <strong>{{ computedAlias || '-' }}</strong>
                        </p>
                      </div>
                      
                      <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input 
                          v-model="localUnit.name" 
                          type="text" 
                          maxlength="100" 
                          required 
                          class="form-input-bold px-4" 
                          placeholder="cth: PT PLN (Persero) Unit Induk..." 
                        />
                      </div>
                  </div>
              </div>
            </div>

            <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
              <div class="absolute left-0 top-0 bottom-0 w-1 bg-cyan-500"></div>
              <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wide mb-6 flex items-center gap-2 border-b border-slate-100 pb-3">
                <MapPinIcon class="h-5 w-5 text-cyan-600" />
                Detail Lokasi
              </h4>

              <div class="space-y-6">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Nomor Telepon <span class="text-red-500">*</span></label>
                    <div class="relative group w-full md:w-1/2">
                      <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors group-focus-within:text-cyan-600">
                        <PhoneIcon class="h-5 w-5 text-slate-400" />
                      </div>
                      <input 
                        v-model="localUnit.phone" 
                        @input="filterPhoneInput" 
                        type="text" 
                        maxlength="20" 
                        required 
                        class="form-input-bold pl-11" 
                        placeholder="cth: (031) 5340651" 
                      />
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Alamat Lengkap <span class="text-red-500">*</span></label>
                    <div class="relative group">
                      <div class="absolute top-3.5 left-3.5 pointer-events-none transition-colors group-focus-within:text-cyan-600">
                        <MapPinIcon class="h-5 w-5 text-slate-400" />
                      </div>
                      <textarea 
                        v-model="localUnit.address" 
                        rows="3" 
                        required 
                        maxlength="255"
                        class="form-input-bold pl-11 py-3 resize-none leading-relaxed" 
                        placeholder="Masukkan alamat lengkap kantor unit..."
                      ></textarea>
                    </div>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              
              <div 
                class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:border-emerald-300 transition-all cursor-pointer group h-full"
                @click="localUnit.is_active = localUnit.is_active ? 0 : 1"
              >
                <div class="flex gap-4 items-center">
                    <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600">
                      <ShieldCheckIcon class="h-6 w-6" />
                    </div>
                    <div>
                      <span class="block text-sm font-bold text-slate-800 group-hover:text-emerald-700 transition-colors">Status Operasional</span>
                      <span class="text-[11px] text-slate-500 font-medium">Non-aktifkan jika unit tutup.</span>
                    </div>
                </div>
                <button 
                  type="button" 
                  class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none" 
                  :class="localUnit.is_active ? 'bg-green-500' : 'bg-slate-300'"
                >
                  <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" :class="localUnit.is_active ? 'translate-x-5' : 'translate-x-0'"></span>
                </button>
              </div>

              <div v-if="isEditing" class="bg-slate-50/80 rounded-2xl p-6 border border-slate-200 flex-1">
                <h4 class="text-xs font-bold text-slate-400 uppercase mb-4 flex items-center gap-2">
                  <ClockIcon class="h-4 w-4" /> Jejak Audit
                </h4>
                <div class="grid grid-cols-2 gap-4 text-[10px] text-slate-500">
                  <div>
                    <span class="block font-bold text-slate-700 mb-0.5">Dibuat Oleh</span>
                    <div class="flex items-center gap-2">
                      <div class="h-5 w-5 rounded-full bg-white border border-slate-200 flex items-center justify-center text-[8px] font-bold text-slate-400">SY</div>
                      <span>{{ localUnit.created_by || 'System' }}</span>
                    </div>
                    <span class="font-mono ml-7 mt-0.5 block">{{ localUnit.created_at || '-' }}</span>
                  </div>
                  <div>
                    <span class="block font-bold text-slate-700 mb-0.5">Update Terakhir</span>
                    <div class="flex items-center gap-2">
                      <div class="h-5 w-5 rounded-full bg-white border border-slate-200 flex items-center justify-center text-[8px] font-bold text-slate-400">AD</div>
                      <span>{{ localUnit.updated_by || 'Admin' }}</span>
                    </div>
                    <span class="font-mono ml-7 mt-0.5 block">{{ localUnit.updated_at || '-' }}</span>
                  </div>
                </div>
              </div>
            </div>

          </form>
        </div>

        <div class="flex items-center justify-between px-8 py-5 bg-white border-t border-slate-200 shrink-0 rounded-b-2xl z-20 shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
          <div class="text-xs font-bold" :class="isValid ? 'text-emerald-600' : 'text-slate-400'">
             <span v-if="isValid" class="flex items-center gap-1.5"><CheckBadgeIcon class="h-5 w-5" /> DATA LENGKAP</span>
             <span v-else>Lengkapi kolom bertanda *</span>
          </div>
          <div class="flex gap-4">
            <button @click="onClose" class="px-6 py-2.5 text-sm font-bold bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200 rounded-xl transition-colors border border-transparent">
               Batal
            </button>
            <button 
              @click="onSave" 
              :disabled="!isValid"
              class="px-8 py-2.5 text-sm font-bold text-white rounded-xl shadow-lg transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none min-w-[120px]"
              :class="isValid ? 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 hover:-translate-y-0.5 hover:shadow-emerald-500/30' : 'bg-slate-300 text-slate-500'"
            >
              {{ isEditing ? 'Simpan Perubahan' : 'Tambah Unit' }}
            </button>
          </div>
        </div>

      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch, computed, onUnmounted } from 'vue';
import { 
  BuildingOfficeIcon, MapPinIcon, XMarkIcon, TagIcon, PhoneIcon,
  ShieldCheckIcon, ClockIcon, InformationCircleIcon, CheckBadgeIcon, 
  ChevronDownIcon, QueueListIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  show: Boolean,
  unitToEdit: Object, 
});

const emit = defineEmits(['close', 'save']);

const localUnit = ref({});
const refPrefix = ref('UID'); 
const refNumber = ref('');    
const unitRegion = ref('');   

const isEditing = computed(() => !!localUnit.value.id);

// Computed untuk Preview Realtime
const computedRefId = computed(() => `${refPrefix.value}-${refNumber.value}`);
const computedAlias = computed(() => unitRegion.value ? `${refPrefix.value} ${unitRegion.value}` : '');

const handlePrefixChange = () => {
  // Logic jika prefix berubah
};

const handleRefNumberInput = (event) => {
  // Hanya Angka
  const val = event.target.value.replace(/[^0-9]/g, '');
  refNumber.value = val;
  event.target.value = val;
};

const handleRegionInput = (event) => {
  // Hanya Huruf dan Spasi
  const val = event.target.value.replace(/[^a-zA-Z\s]/g, ''); 
  unitRegion.value = val;
  event.target.value = val;
};

const filterParentId = (event) => {
  const val = event.target.value.replace(/[^0-9]/g, '');
  localUnit.value.parent_id = val === '' ? 0 : parseInt(val);
  if (event.target.value !== val.toString() && val !== '') event.target.value = val;
};

const filterPhoneInput = (event) => {
  // Angka, +, -, (, ), spasi
  const val = event.target.value.replace(/[^0-9+\-\(\)\s]/g, '');
  localUnit.value.phone = val;
  if (event.target.value !== val) event.target.value = val;
};

const isValid = computed(() => {
  return refNumber.value.trim() && 
         unitRegion.value.trim() &&
         localUnit.value.name?.trim() &&
         localUnit.value.phone?.trim() &&
         localUnit.value.address?.trim(); 
});

// Reset atau Isi Data saat Modal Dibuka
watch(() => props.show, (newVal) => {
  if (newVal) {
    document.body.style.overflow = 'hidden';
    
    if (props.unitToEdit) {
      // MODE EDIT
      localUnit.value = JSON.parse(JSON.stringify(props.unitToEdit));
      
      // Parse Ref ID
      if (localUnit.value.ref_id) {
        const parts = localUnit.value.ref_id.split('-');
        if (parts.length >= 2) {
          refPrefix.value = parts[0];
          refNumber.value = parts[1];
        } else {
          refPrefix.value = 'UID';
          refNumber.value = localUnit.value.ref_id;
        }
      }

      // Parse Alias (Ambil kata setelah spasi pertama)
      if (localUnit.value.alias) {
        const parts = localUnit.value.alias.split(' ');
        if (parts.length >= 2 && parts[0] === refPrefix.value) {
          unitRegion.value = parts.slice(1).join(' ');
        } else {
          // Jika alias custom, masukkan apa adanya (user harus sesuaikan manual jika beda)
          unitRegion.value = localUnit.value.alias.replace(refPrefix.value + ' ', '');
        }
      }

    } else {
      // MODE TAMBAH
      localUnit.value = {
        ref_id: '', parent_id: 0, alias: '', name: '', address: '', phone: '', is_active: 1
      };
      refPrefix.value = 'UID';
      refNumber.value = '';
      unitRegion.value = '';
    }
  } else {
    document.body.style.overflow = '';
  }
});

onUnmounted(() => document.body.style.overflow = '');

const onClose = () => emit('close');

const onSave = () => {
  if (isValid.value) {
    // Gabungkan data sebelum emit
    const payload = { 
        ...localUnit.value,
        ref_id: computedRefId.value,
        alias: computedAlias.value
    };
    emit('save', payload);
  }
};
</script>

<style scoped>
/* --- Professional Form Styles (Bold & Clean) --- */
.form-input-bold {
  @apply block w-full rounded-xl border border-slate-300 bg-white text-slate-800 font-semibold tracking-wide
  focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 focus:bg-white
  placeholder:text-slate-300 placeholder:font-normal transition-all duration-200 sm:text-sm py-2.5 shadow-sm hover:border-emerald-300;
}

.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>