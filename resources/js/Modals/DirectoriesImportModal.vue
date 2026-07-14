<template>
    <AdminModal
        :model-value="modelValue"
        title="Import Directories CSV"
        subtitle="Upload, validate, review, and save directory rows"
        icon="add"
        size="xl"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <div class="space-y-6">
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Step 1</p>
                    <p class="mt-2 font-semibold text-slate-800">Upload CSV</p>
                    <p class="mt-1 text-sm text-slate-500">Pick a CSV file with directory data.</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Step 2</p>
                    <p class="mt-2 font-semibold text-slate-800">Review & edit</p>
                    <p class="mt-1 text-sm text-slate-500">Confirm parsed rows and fix any missing fields.</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Step 3</p>
                    <p class="mt-2 font-semibold text-slate-800">Save data</p>
                    <p class="mt-1 text-sm text-slate-500">Save cleaned directories into the database.</p>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                <h3 class="text-lg font-semibold text-slate-800">{{ CSV_Format_style.title }}</h3>
                <p class="mt-2 text-sm text-slate-500">
                    {{ CSV_Format_style.description }}
                    </p>
                    <button>
                    <a :href="`/storage/${CSV_Format_style.csvFileFormat}`" target="_blank" class="admin-btn-info mt-2">
                        Download CSV Format
                    </a>
                </button>
            </div>

            <div v-if="!hasRows" class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center">
                <div
                    class="mx-auto flex max-w-xl flex-col items-center justify-center gap-4 rounded-3xl border border-slate-200 bg-slate-50 p-8 text-slate-600 shadow-sm"
                    @dragover.prevent="dragActive = true"
                    @dragleave.prevent="dragActive = false"
                    @drop.prevent="handleDrop"
                    :class="dragActive ? 'border-brand-500 bg-brand-50/50' : ''"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-12 w-12 text-brand-600">
                        <path fill="currentColor" d="M19.35 10.04C18.67 6.59 15.64 4 12 4c-3.31 0-6.07 2.21-6.84 5.17A5.503 5.503 0 0 0 6.5 20h12c2.48 0 4.5-2.02 4.5-4.5 0-2.36-1.83-4.29-4.15-4.46z" />
                    </svg>
                    <div>
                        <p class="text-lg font-semibold text-slate-800">Drag & drop your CSV file</p>
                        <p class="mt-1 text-sm text-slate-500">or click to choose a file</p>
                    </div>
                    <button type="button" class="admin-btn-secondary" @click="triggerFileInput" :disabled="isParsing">Choose CSV file</button>
                    <input ref="fileInput" type="file" accept=".csv,text/csv" class="hidden" @change="handleFileChange" />
                    <p class="text-xs text-slate-400">Accepted headers: AREA, BUSINESS NAME, Contact Number, ADDRESS (case-insensitive).</p>
                    <p v-if="isParsing" class="mt-4 inline-flex items-center gap-2 text-sm text-slate-500">
                        <svg class="h-4 w-4 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-width="4" class="opacity-25" />
                            <path d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round" class="opacity-75" />
                        </svg>
                        Parsing CSV file...
                    </p>
                </div>

                <p v-if="parsingError" class="mt-4 text-sm text-red-600">{{ parsingError }}</p>
            </div>

            <div v-else class="space-y-4">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-semibold text-slate-800">{{ fileName }}</p>
                            <p class="text-sm text-slate-500">{{ parsedRows.length }} row(s) loaded for review.</p>
                        </div>
                        <button type="button" class="admin-btn-secondary" @click="resetFile">Upload another file</button>
                    </div>
                    <p class="mt-2 text-sm text-slate-500">Edit values directly in the table below before saving.</p>
                    <p class="mt-2 rounded-lg bg-blue-50 px-3 py-2 text-xs text-blue-700">
                        💡 <strong>Tip:</strong> Enter an address in the Address column and it will automatically populate the Major Island Group (Luzon/Visayas/Mindanao) and Province/City fields using OpenStreetMap.
                    </p>
                    
                    <!-- Geolocation Progress Bar -->
                    <div v-if="activeGeolocations > 0" class="mt-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-medium text-slate-700">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-4 w-4 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <circle cx="12" cy="12" r="10" stroke-width="4" class="opacity-25" />
                                        <path d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round" class="opacity-75" />
                                    </svg>
                                    Auto-filling location data...
                                </span>
                            </p>
                            <p class="text-xs text-slate-500">{{ completedGeolocations }}/{{ totalRowsToGeolocate }}</p>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                            <div 
                                class="h-full bg-gradient-to-r from-brand-500 to-brand-600 transition-all duration-300"
                                :style="{ width: totalRowsToGeolocate > 0 ? `${(completedGeolocations / totalRowsToGeolocate) * 100}%` : '0%' }"
                            ></div>
                        </div>
                    </div>
                </div>

                <div v-if="validationErrors.length" class="rounded-3xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <p class="font-semibold">Validation issues detected</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        <li v-for="error in validationErrors" :key="error.key">{{ error.message }}</li>
                    </ul>
                </div>

                <div class="overflow-x-auto rounded-3xl border border-slate-200 bg-white">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Area</th>
                                <th class="px-4 py-3">Major Island Group</th>
                                <th class="px-4 py-3">Province</th>
                                <th class="px-4 py-3">Business Name</th>
                                <th class="px-4 py-3">Address</th>
                                <th class="px-4 py-3">Contact Name</th>
                                <th class="px-4 py-3">Contact No</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in parsedRows" :key="row.id" :class="hasRowError(row.id) ? 'bg-red-50' : ''">
                                <td class="border-t border-slate-200 px-4 py-3"><input v-model="row.area" type="text" class="admin-form-input" /></td>
                                <td class="border-t border-slate-200 px-4 py-3">
                                    <div class="relative">
                                        <input v-model="row.region" type="text" class="admin-form-input" :disabled="geolocatingRowId === row.id" />
                                        <span v-if="geolocatingRowId === row.id" class="absolute right-2 top-1/2 -translate-y-1/2">
                                            <svg class="h-4 w-4 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <circle cx="12" cy="12" r="10" stroke-width="4" class="opacity-25" />
                                                <path d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round" class="opacity-75" />
                                            </svg>
                                        </span>
                                    </div>
                                </td>
                                <td class="border-t border-slate-200 px-4 py-3">
                                    <div class="relative">
                                        <input v-model="row.place" type="text" class="admin-form-input" :disabled="geolocatingRowId === row.id" />
                                        <span v-if="geolocatingRowId === row.id" class="absolute right-2 top-1/2 -translate-y-1/2">
                                            <svg class="h-4 w-4 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <circle cx="12" cy="12" r="10" stroke-width="4" class="opacity-25" />
                                                <path d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round" class="opacity-75" />
                                            </svg>
                                        </span>
                                    </div>
                                </td>
                                <td class="border-t border-slate-200 px-4 py-3"><input v-model="row.business_name" type="text" class="admin-form-input" /></td>
                                <td class="border-t border-slate-200 px-4 py-3">
                                    <input v-model="row.address" type="text" class="admin-form-input" @input="triggerGeolocation(row)" />
                                </td>
                                <td class="border-t border-slate-200 px-4 py-3"><input v-model="row.contact_name" type="text" class="admin-form-input" /></td>
                                <td class="border-t border-slate-200 px-4 py-3"><input v-model="row.contact_no" type="text" class="admin-form-input" @input="formatContactNo(row)" /></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <button
                    v-if="hasRows"
                    type="button"
                    class="admin-modal-btn-secondary"
                    @click="resetFile"
                >
                    Start over
                </button>
                <button
                    v-if="hasRows"
                    type="button"
                    class="admin-modal-submit"
                    :disabled="!canSave || isSaving"
                    @click="saveImport"
                >
                    <span v-if="isSaving">Saving...</span>
                    <span v-else>Save Rows</span>
                </button>
            </div>
        </div>
    </AdminModal>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AdminModal from '@/Components/Admin/AdminModal.vue';

// Mapping of Philippine provinces to island groups
const ISLAND_GROUP_MAP = {
    // Luzon
    'abra': 'Luzon',
    'alaminos city': 'Luzon',
    'albay': 'Luzon',
    'angeles city': 'Luzon',
    'antipolo city': 'Luzon',
    'apayao': 'Luzon',
    'aurora': 'Luzon',
    'baguio city': 'Luzon',
    'bat': 'Luzon', // Bicol Administrative Region
    'bataan': 'Luzon',
    'batac city': 'Luzon',
    'batangas': 'Luzon',
    'batangas city': 'Luzon',
    'bayawan city': 'Luzon',
    'bayombong': 'Luzon',
    'bayugan city': 'Luzon',
    'benguet': 'Luzon',
    'biñan city': 'Luzon',
    'bocaue': 'Luzon',
    'bogo city': 'Luzon',
    'bulan': 'Luzon',
    'bulacan': 'Luzon',
    'cabanatuan city': 'Luzon',
    'cabuyao city': 'Luzon',
    'cagayan': 'Luzon',
    'caloocan city': 'Luzon',
    'camalig': 'Luzon',
    'camarines norte': 'Luzon',
    'camarines sur': 'Luzon',
    'canaman': 'Luzon',
    'capas': 'Luzon',
    'car': 'Luzon', // Cordillera Administrative Region
    'carcar city': 'Luzon',
    'catanduanes': 'Luzon',
    'cavite': 'Luzon',
    'cavite city': 'Luzon',
    'dagupan': 'Luzon',
    'dagupan city': 'Luzon',
    'dasmariñas city': 'Luzon',
    'dinalupihan': 'Luzon',
    'gapan city': 'Luzon',
    'general trias city': 'Luzon',
    'ifugao': 'Luzon',
    'ilocos norte': 'Luzon',
    'ilocos norte (i)': 'Luzon',
    'ilocos sur': 'Luzon',
    'ilocos sur (ii)': 'Luzon',
    'iriga city': 'Luzon',
    'isabela': 'Luzon',
    'kalinga': 'Luzon',
    'la carlota city': 'Luzon',
    'la union': 'Luzon',
    'laguna': 'Luzon',
    'laoag city': 'Luzon',
    'las piñas city': 'Luzon',
    'legazpi city': 'Luzon',
    'lipa city': 'Luzon',
    'lucena city': 'Luzon',
    'mabalacat city': 'Luzon',
    'makati city': 'Luzon',
    'malabon city': 'Luzon',
    'malaybalay city': 'Luzon',
    'malolos city': 'Luzon',
    'mandaluyong city': 'Luzon',
    'manila': 'Luzon',
    'manila city': 'Luzon',
    'marawi city': 'Luzon',
    'marikina city': 'Luzon',
    'marinduque': 'Luzon',
    'masbate': 'Luzon',
    'masbate city': 'Luzon',
    'metro manila': 'Luzon',
    'meycauayan city': 'Luzon',
    'mountain province': 'Luzon',
    'muntinlupa city': 'Luzon',
    'naga city': 'Luzon',
    'narra': 'Luzon',
    'national capital region': 'Luzon',
    'navotas city': 'Luzon',
    'ncr': 'Luzon',
    'new ecija': 'Luzon',
    'nueva ecija': 'Luzon',
    'nueva vizcaya': 'Luzon',
    'nueva viscaya': 'Luzon',
    'occidental mindoro': 'Luzon',
    'olongapo city': 'Luzon',
    'oriental mindoro': 'Luzon',
    'ozamiz city': 'Luzon',
    'palawan': 'Luzon',
    'palayan city': 'Luzon',
    'pampanga': 'Luzon',
    'pangasinan': 'Luzon',
    'parañaque city': 'Luzon',
    'pasay city': 'Luzon',
    'pasig city': 'Luzon',
    'pateros': 'Luzon',
    'puerto princesa city': 'Luzon',
    'quezon': 'Luzon',
    'quezon city': 'Luzon',
    'quirino': 'Luzon',
    'rizal': 'Luzon',
    'romblon': 'Luzon',
    'san carlos city (pangasinan)': 'Luzon',
    'san fernando city (la union)': 'Luzon',
    'san fernando city (pampanga)': 'Luzon',
    'san jose city': 'Luzon',
    'san jose del monte city': 'Luzon',
    'san juan city': 'Luzon',
    'santiago city': 'Luzon',
    'science city of muñoz': 'Luzon',
    'sorsogon': 'Luzon',
    'sorsogon city': 'Luzon',
    'sta. rosa city': 'Luzon',
    'tabaco city': 'Luzon',
    'tacloban city': 'Luzon',
    'tagaytay city': 'Luzon',
    'taguig city': 'Luzon',
    'talisay city': 'Luzon',
    'tanauan city': 'Luzon',
    'tarlac': 'Luzon',
    'tarlac city': 'Luzon',
    'tayabas city': 'Luzon',
    'trece martires city': 'Luzon',
    'tuguegarao city': 'Luzon',
    'urdaneta city': 'Luzon',
    'valenzuela city': 'Luzon',
    'vigan city': 'Luzon',
    'zambales': 'Luzon',
    
    // Visayas
    'aklan': 'Visayas',
    'antique': 'Visayas',
    'bacolod city': 'Visayas',
    'bais city': 'Visayas',
    'bantayan island': 'Visayas',
    'barugo': 'Visayas',
    'bayawan city': 'Visayas',
    'baybay city': 'Visayas',
    'biliran': 'Visayas',
    'borongan city': 'Visayas',
    'bogo city': 'Visayas',
    'bohol': 'Visayas',
    'calbayog city': 'Visayas',
    'canlaon city': 'Visayas',
    'capiz': 'Visayas',
    'carcar city': 'Visayas',
    'catbalogan city': 'Visayas',
    'cebu': 'Visayas',
    'cebu city': 'Visayas',
    'danao city': 'Visayas',
    'dumaguete city': 'Visayas',
    'eastern samar': 'Visayas',
    'escalante city': 'Visayas',
    'guihulngan city': 'Visayas',
    'guimaras': 'Visayas',
    'himamaylan city': 'Visayas',
    'iloilo': 'Visayas',
    'iloilo city': 'Visayas',
    'kabankalan city': 'Visayas',
    'la carlota city': 'Visayas',
    'lapu-lapu city': 'Visayas',
    'leyte': 'Visayas',
    'maasin city': 'Visayas',
    'mandaue city': 'Visayas',
    'naga city (cebu)': 'Visayas',
    'negros occidental': 'Visayas',
    'negros oriental': 'Visayas',
    'northern samar': 'Visayas',
    'ormoc city': 'Visayas',
    'passi city': 'Visayas',
    'roxas city': 'Visayas',
    'sagay city': 'Visayas',
    'samal': 'Visayas',
    'samar': 'Visayas',
    'samar (western samar)': 'Visayas',
    'san carlos city (negros occidental)': 'Visayas',
    'silay city': 'Visayas',
    'sipalay city': 'Visayas',
    'siquijor': 'Visayas',
    'southern leyte': 'Visayas',
    'tacloban city': 'Visayas',
    'tagbilaran city': 'Visayas',
    'talisay city (cebu)': 'Visayas',
    'talisay city (negros occidental)': 'Visayas',
    'tanjay city': 'Visayas',
    'toledo city': 'Visayas',
    'victorias city': 'Visayas',
    'western samar': 'Visayas',
    
    // Mindanao
    'agusan del norte': 'Mindanao',
    'agusan del sur': 'Mindanao',
    'basilan': 'Mindanao',
    'bayugan city': 'Mindanao',
    'bislig city': 'Mindanao',
    'bukidnon': 'Mindanao',
    'butuan': 'Mindanao',
    'butuan city': 'Mindanao',
    'cabadbaran city': 'Mindanao',
    'cagayan de oro': 'Mindanao',
    'cagayan de oro city': 'Mindanao',
    'calinog': 'Mindanao',
    'camiguin': 'Mindanao',
    'cotabato': 'Mindanao',
    'cotabato city': 'Mindanao',
    'dapitan city': 'Mindanao',
    'davao city': 'Mindanao',
    'davao de oro': 'Mindanao',
    'davao del norte': 'Mindanao',
    'davao del sur': 'Mindanao',
    'davao occidental': 'Mindanao',
    'davao oriental': 'Mindanao',
    'digos city': 'Mindanao',
    'dinagat islands': 'Mindanao',
    'dipolog city': 'Mindanao',
    'el salvador city': 'Mindanao',
    'general santos': 'Mindanao',
    'general santos city': 'Mindanao',
    'gingoog city': 'Mindanao',
    'iligan city': 'Mindanao',
    'isabela city': 'Mindanao',
    'kidapawan city': 'Mindanao',
    'koronadal city': 'Mindanao',
    'lanao del norte': 'Mindanao',
    'lanao del sur': 'Mindanao',
    'maguindanao': 'Mindanao',
    'maguindanao del norte': 'Mindanao',
    'maguindanao del sur': 'Mindanao',
    'malaybalay city': 'Mindanao',
    'marawi': 'Mindanao',
    'marawi city': 'Mindanao',
    'mati city': 'Mindanao',
    'misamis occidental': 'Mindanao',
    'misamis oriental': 'Mindanao',
    'north cotabato': 'Mindanao',
    'oroquieta city': 'Mindanao',
    'ozamiz city': 'Mindanao',
    'pagadian city': 'Mindanao',
    'panabo city': 'Mindanao',
    'samal city': 'Mindanao',
    'sarangani': 'Mindanao',
    'south cotabato': 'Mindanao',
    'sultan kudarat': 'Mindanao',
    'surigao city': 'Mindanao',
    'surigao del norte': 'Mindanao',
    'surigao del sur': 'Mindanao',
    'tacurong city': 'Mindanao',
    'tagum city': 'Mindanao',
    'tangub city': 'Mindanao',
    'tawi-tawi': 'Mindanao',
    'valencia city': 'Mindanao',
    'zamboanga city': 'Mindanao',
    'zamboanga del norte': 'Mindanao',
    'zamboanga del sur': 'Mindanao',
    'zamboanga sibugay': 'Mindanao',
};

const props = defineProps({ modelValue: Boolean });
const emit = defineEmits(['update:modelValue', 'imported']);

const fileInput = ref(null);
const fileName = ref('');
const parsingError = ref('');
const dragActive = ref(false);
const parsedRows = ref([]);
const validationErrors = ref([]);
const isParsing = ref(false);
const isSaving = ref(false);
const geolocatingRowId = ref(null); // Track which row is being geolocated
const activeGeolocations = ref(0); // Track number of active geolocation requests
const totalRowsToGeolocate = ref(0); // Total rows that need geolocation
const completedGeolocations = ref(0); // Completed geolocation requests

// Debounce timer for geolocation
const geolocateTimers = new Map();

const hasRows = computed(() => parsedRows.value.length > 0);
const canSave = computed(() => hasRows.value && validationErrors.value.length === 0 && !isSaving.value);

const headerKeyMap = {
    'area': 'area',
    'AREA': 'area',
    'Area': 'area',
    'business name': 'business_name',
    'BUSINESS NAME': 'business_name',
    'Business Name': 'business_name',
    'business_name': 'business_name',
    'businessname': 'business_name',
    'business name': 'business_name',
    'CONTACT NUMBER': 'contact_no',
    'CONTACT NO': 'contact_no',
    'CONTACT': 'contact_no',
    'contact number': 'contact_no',
    'contact no': 'contact_no',
    'contact': 'contact_no',
    'contact_no': 'contact_no',
    'Contact Number': 'contact_no',
    'Contact No': 'contact_no',
    'Contact': 'contact_no',
    'ADDRESS': 'address',
    'address': 'address',
    'Address': 'address',
    'region': 'region',
    'REGION': 'region',
    'Region': 'region',
    'major island group': 'region',
    'place': 'place',
    'province/city': 'place',
    'provinces/cities': 'place',
    'province': 'place',
    'city': 'place',
    'contact name': 'contact_name',
    'CONTACT NAME': 'contact_name',
    'Contact Name': 'contact_name',
    'contact_name': 'contact_name',
    'Contact_Name': 'contact_name',
};

const CSV_Format_style = {
    title: 'CSV Format',
    description: 'The CSV file should have the following headers (case-insensitive): AREA, BUSINESS NAME, Contact Number, ADDRESS. Additional headers will be ignored.',
    csvFileFormat: 'CSVFormat.csv',
};

const requiredHeaders = ['area', 'business_name', 'contact_no'];

function triggerFileInput() {
    fileInput.value?.click();
}

function handleFileChange(event) {
    const selected = event.target?.files?.[0];
    if (selected) {
        loadFile(selected);
    }
}

function handleDrop(event) {
    dragActive.value = false;
    const droppedFile = event.dataTransfer?.files?.[0];
    if (droppedFile) {
        loadFile(droppedFile);
    }
}

function loadFile(file) {
    parsingError.value = '';
    if (!file.name.toLowerCase().endsWith('.csv')) {
        parsingError.value = 'Please upload a CSV file only.';
        return;
    }
    fileName.value = file.name;
    isParsing.value = true;
    readFile(file);
}

async function readFile(file) {
    try {
        const text = await file.text();
        parseCsvText(text);
    } catch (error) {
        parsingError.value = 'Unable to read the CSV file. Please try again.';
    } finally {
        isParsing.value = false;
    }
}

const headerNormalizeCache = new Map();

function normalizeHeader(rawHeader) {
    if (headerNormalizeCache.has(rawHeader)) {
        return headerNormalizeCache.get(rawHeader);
    }
    const normalized = rawHeader
        .toString()
        .trim()
        .toLowerCase()
        .replace(/\s+/g, ' ')
        .replace(/[\u2018\u2019\u201c\u201d"']/g, '')
        .replace(/[^a-z0-9 /]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
    headerNormalizeCache.set(rawHeader, normalized);
    return normalized;
}

function mapHeader(rawHeader) {
    return headerKeyMap[normalizeHeader(rawHeader)] ?? null;
}

function parseCsv(text) {
    const rows = [];
    const currentChars = [];
    let row = [];
    let inQuotes = false;

    for (let i = 0; i < text.length; i += 1) {
        const char = text[i];
        const nextChar = text[i + 1];

        if (char === '"') {
            if (inQuotes && nextChar === '"') {
                currentChars.push('"');
                i += 1;
            } else {
                inQuotes = !inQuotes;
            }
        } else if (char === ',' && !inQuotes) {
            row.push(currentChars.join(''));
            currentChars.length = 0;
        } else if ((char === '\n' || char === '\r') && !inQuotes) {
            if (char === '\r' && nextChar === '\n') {
                i += 1;
            }
            row.push(currentChars.join(''));
            rows.push(row);
            row = [];
            currentChars.length = 0;
        } else {
            currentChars.push(char);
        }
    }

    if (currentChars.length || row.length) {
        row.push(currentChars.join(''));
        rows.push(row);
    }

    return rows.filter((item) => item.length > 1 || item[0]?.trim() !== '');
}

function parseCsvText(text) {
    const rows = parseCsv(text);
    if (!rows.length) {
        parsingError.value = 'CSV file is empty or invalid.';
        return;
    }

    const headers = rows[0].map((header) => mapHeader(header));
    const headerIndex = {};

    headers.forEach((key, index) => {
        if (key && headerIndex[key] === undefined) {
            headerIndex[key] = index;
        }
    });

    const missingHeaders = requiredHeaders.filter((key) => headerIndex[key] === undefined);
    if (missingHeaders.length) {
        parsingError.value = `Required headers missing: ${missingHeaders
            .map((key) => headerLabel(key))
            .join(', ')}.`;
        return;
    }

    const parsed = rows.slice(1).map((row, rowIndex) => {
        const area = getCell(row, headerIndex, 'area');
        const region = getCell(row, headerIndex, 'region');
        const place = getCell(row, headerIndex, 'place');
        const business_name = getCell(row, headerIndex, 'business_name');
        const address = getCell(row, headerIndex, 'address');
        const contact_name = getCell(row, headerIndex, 'contact_name');
        const contact_no = getCell(row, headerIndex, 'contact_no');

        // Skip empty rows
        if (!area && !region && !place && !business_name && !address && !contact_name && !contact_no) {
            return null;
        }

        return {
            id: rowIndex + 1,
            area,
            region,
            place,
            business_name,
            address,
            contact_name,
            contact_no,
        };
    }).filter((row) => row !== null);

    if (!parsed.length) {
        parsingError.value = 'CSV file contains no valid rows.';
        return;
    }

    parsedRows.value = parsed;
    
    // Don't validate immediately - wait for geolocation to complete
    // Just clear validation errors for now
    validationErrors.value = [];
    
    // Count rows that need geolocation
    const rowsWithAddress = parsed.filter(row => row.address && row.address.trim());
    totalRowsToGeolocate.value = rowsWithAddress.length;
    completedGeolocations.value = 0;
    
    // Trigger geolocation for all rows with addresses
    parsed.forEach((row) => {
        if (row.address && row.address.trim()) {
            // Use a small delay to stagger API requests
            const delay = row.id * 300; // 300ms between each request
            setTimeout(() => {
                triggerGeolocation(row);
            }, delay);
        }
    });
}

function getCell(row, headerIndex, field) {
    const index = headerIndex[field];
    if (index === undefined) {
        return '';
    }
    const value = row[index];
    return value ? value.toString().trim() : '';
}

function headerLabel(key) {
    const labels = {
        area: 'AREA || Area || area',
        business_name: 'BUSINESS NAME || Business Name || Business_Name || business name',
        contact_no: 'CONTACT NUMBER || Contact Number || Contact_No || contact number || NUMBER || number',
        address: 'ADDRESS || Address || ADDRESS || address',
        region: 'Major Island Group || Major Island Group || Region || region',
        place: 'Provinces/Cities || Provinces/Cities || Place || place',
        contact_name: 'CONTACT NAME || Contact Name || Contact_Name || contact name',
    };
    return labels[key] ?? key;
}

function validateRows() {
    validationErrors.value = [];
    parsedRows.value.forEach((row, index) => {
        const rowErrors = [];

        if (!row.area?.trim()) {
            rowErrors.push('Area is required.');
        }
        if (!row.region?.trim()) {
            rowErrors.push('Major Island Group is required.');
        }
        if (!row.place?.trim()) {
            rowErrors.push('Provinces/Cities is required.');
        }
        if (!row.business_name?.trim()) {
            rowErrors.push('Business Name is required.');
        }
        if (!row.contact_name?.trim()) {
            rowErrors.push('Contact Name is required.');
        }
        if (!row.contact_no?.trim()) {
            rowErrors.push('Contact Number is required.');
        }

        if (rowErrors.length) {
            validationErrors.value.push({
                key: `${row.id}-${index}`,
                message: `Row ${row.id}: ${rowErrors.join(' ')}`,
            });
        }
    });
}

function normalizeContactNo(value) {
    const raw = (value ?? '').toString().replace(/[^0-9]/g, '').slice(0, 11);
    return raw;
}

function formatContactNo(row) {
    row.contact_no = normalizeContactNo(row.contact_no);
}

/**
 * Extract province name from address string
 * Tries to find province names in the address text
 */
function extractProvinceFromAddressString(address) {
    if (!address) return null;
    
    const addressLower = address.toLowerCase();
    const provinceList = Object.keys(ISLAND_GROUP_MAP);
    
    // Sort by length (longer matches first) to avoid partial matches
    const sortedProvinces = [...provinceList].sort((a, b) => b.length - a.length);
    
    for (const province of sortedProvinces) {
        // Look for province name as a complete word/phrase in the address
        // Match with word boundaries or commas
        const patterns = [
            new RegExp(`\\b${province}\\b`, 'i'),
            new RegExp(`,\\s*${province}`, 'i'),
            new RegExp(`${province}`, 'i')
        ];
        
        for (const pattern of patterns) {
            if (pattern.test(addressLower)) {
                return province.charAt(0).toUpperCase() + province.slice(1);
            }
        }
    }
    
    return null;
}

/**
 * Geolocation function to fetch location data from Nominatim (OpenStreetMap)
 * and auto-populate region and place fields
 */
async function geolocationFromAddress(row) {
    if (!row.address || !row.address.trim()) {
        return;
    }

    activeGeolocations.value++;
    geolocatingRowId.value = row.id;

    try {
        // First, try to extract province from the address string itself
        let extractedProvince = extractProvinceFromAddressString(row.address);
        
        if (extractedProvince) {
            // Got province from address string
            if (!row.place || !row.place.trim()) {
                row.place = extractedProvince;
            }

            const islandGroup = getIslandGroupFromProvince(extractedProvince);
            if (islandGroup && (!row.region || !row.region.trim())) {
                row.region = islandGroup;
            }
            return;
        }

        // Fallback: Use Nominatim if province not found in address string
        const searchQuery = `${row.address}, Philippines`;
        const response = await fetch(
            `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(searchQuery)}&format=json&limit=1`,
            {
                headers: {
                    'Accept': 'application/json',
                    'User-Agent': 'DirectoriesImportApp/1.0',
                }
            }
        );

        if (!response.ok) {
            console.warn('Geolocation API failed');
            return;
        }

        const results = await response.json();
        if (!results || results.length === 0) {
            console.warn('No geolocation results found');
            return;
        }

        const location = results[0];
        
        // Get reverse geocoding to extract province/city information
        const reverseResponse = await fetch(
            `https://nominatim.openstreetmap.org/reverse?lat=${location.lat}&lon=${location.lon}&format=json&addressdetails=1`,
            {
                headers: {
                    'Accept': 'application/json',
                    'User-Agent': 'DirectoriesImportApp/1.0',
                }
            }
        );

        if (!reverseResponse.ok) {
            console.warn('Reverse geolocation API failed');
            return;
        }

        const reverseData = await reverseResponse.json();
        const address_parts = reverseData.address || {};

        // Extract province/city - try multiple fields
        const province = address_parts.state || 
                        address_parts.province || 
                        address_parts.county ||
                        address_parts.city ||
                        address_parts.municipality ||
                        '';

        if (province) {
            // Try to auto-populate place (province/city)
            if (!row.place || !row.place.trim()) {
                row.place = province;
            }

            // Try to auto-populate region (island group)
            const islandGroup = getIslandGroupFromProvince(province);
            if (islandGroup && (!row.region || !row.region.trim())) {
                row.region = islandGroup;
            }
        }

    } catch (error) {
        console.warn('Geolocation error:', error);
    } finally {
        activeGeolocations.value--;
        completedGeolocations.value++;
        geolocatingRowId.value = null;
        
        // If all geolocation requests are done, re-validate
        if (activeGeolocations.value === 0) {
            setTimeout(() => {
                validateRows();
                completedGeolocations.value = 0;
                totalRowsToGeolocate.value = 0;
            }, 100);
        }
    }
}

/**
 * Get island group based on province name
 */
function getIslandGroupFromProvince(province) {
    if (!province) return null;
    
    const normalized = province.toLowerCase().trim();
    
    // Direct lookup first
    if (ISLAND_GROUP_MAP[normalized]) {
        return ISLAND_GROUP_MAP[normalized];
    }

    // Try to find partial matches (longer matches first)
    const provinceKeys = Object.keys(ISLAND_GROUP_MAP).sort((a, b) => b.length - a.length);
    
    for (const provinceKey of provinceKeys) {
        // Check if the normalized input contains or is contained in the province key
        if (normalized.includes(provinceKey) || provinceKey.includes(normalized)) {
            return ISLAND_GROUP_MAP[provinceKey];
        }
    }

    return null;
}

/**
 * Debounced geolocation on address change
 */
function triggerGeolocation(row) {
    // Clear existing timer for this row
    if (geolocateTimers.has(row.id)) {
        clearTimeout(geolocateTimers.get(row.id));
    }

    // Set new timer (debounce for 1.5 seconds)
    const timer = setTimeout(() => {
        geolocationFromAddress(row);
        geolocateTimers.delete(row.id);
    }, 1500);

    geolocateTimers.set(row.id, timer);
}

function resetFile() {
    fileName.value = '';
    parsingError.value = '';
    parsedRows.value = [];
    validationErrors.value = [];
    isSaving.value = false;
    activeGeolocations.value = 0;
    completedGeolocations.value = 0;
    totalRowsToGeolocate.value = 0;
}

function hasRowError(rowId) {
    return validationErrors.value.some((err) => err.key.startsWith(`${rowId}-`));
}

async function saveImport() {
    // Run validation one more time before saving
    validateRows();

    if (validationErrors.value.length) {
        Swal.fire({
            title: 'Cannot save',
            text: 'Please resolve the per-row validation issues before saving.',
            icon: 'warning',
            confirmButtonColor: '#3085d6',
        });
        return;
    }

    isSaving.value = true;

    const payload = {
        rows: parsedRows.value.map((row) => {
            const formattedContact = normalizeContactNo(row.contact_no);
            let contactNo = formattedContact;
            if (contactNo.startsWith('0')) {
                contactNo = '+63' + contactNo.slice(1);
            }
            return {
                area: row.area,
                region: row.region,
                place: row.place,
                business_name: row.business_name,
                address: row.address,
                contact_name: row.contact_name,
                contact_no: contactNo,
            };
        }),
    };

    router.post('/directories/import', payload, {
        preserveState: true,
        onSuccess: () => {
            Swal.fire({
                title: 'Import complete',
                text: 'CSV rows were saved successfully.',
                icon: 'success',
                confirmButtonColor: '#3085d6',
            }).then(() => {
                emit('update:modelValue', false);
                emit('imported');
                resetFile();
            });
        },
        onError: () => {
            Swal.fire({
                title: 'Import failed',
                text: 'There was a problem saving CSV data. Please check your rows and try again.',
                icon: 'error',
                confirmButtonColor: '#d33',
            });
        },
        onFinish: () => {
            isSaving.value = false;
        },
    });
}

// Cleanup geolocation timers on component unmount
onBeforeUnmount(() => {
    geolocateTimers.forEach(timer => clearTimeout(timer));
    geolocateTimers.clear();
});

watch(parsedRows, validateRows, { deep: true });
</script>
