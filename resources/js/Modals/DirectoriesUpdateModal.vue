<template>
    <AdminModal
        :model-value="modelValue"
        title="Edit Directory"
        subtitle="Update directory entry details"
        icon="edit"
        size="xl"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <form @submit.prevent="submitForm">
            <div class="space-y-4">
                <div class="admin-form-group">
                    <label class="admin-form-label">Area<span class="admin-form-required">*</span></label>
                    <input v-model="form.area" class="admin-form-input" placeholder="Enter area" required />
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Major Island Group<span class="admin-form-required">*</span></label>
                    <select v-model="form.region" @change="updateProvinces" class="admin-form-select" required>
                        <option value="">Select Major Island</option>
                        <option value="Luzon">Luzon</option>
                        <option value="Visayas">Visayas</option>
                        <option value="Mindanao">Mindanao</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Provinces/Cities<span class="admin-form-required">*</span></label>
                    <select v-model="form.place" class="admin-form-select" required>
                        <option value="">Select Provinces/Cities</option>
                        <option v-for="province in availableProvinces" :key="province" :value="province">{{ province }}</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Business Name<span class="admin-form-required">*</span></label>
                    <input v-model="form.business_name" class="admin-form-input" placeholder="Enter business name" required />
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Address</label>
                    <textarea v-model="form.address" class="admin-form-textarea" placeholder="Enter address (optional)"></textarea>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Contact Name<span class="admin-form-required">*</span></label>
                    <input v-model="form.contact_name" class="admin-form-input" placeholder="Enter contact person" required
                        @input="form.contact_name = form.contact_name.charAt(0).toUpperCase() + form.contact_name.slice(1)" />
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Contact No<span class="admin-form-required">*</span></label>
                    <input v-model="form.contact_no" class="admin-form-input" placeholder="09123456789" required type="tel" maxlength="11" inputmode="numeric" pattern="[0-9]*"
                        @input="form.contact_no = form.contact_no.replace(/[^0-9]/g, '').slice(0, 11)" />
                </div>
            </div>
            <button type="submit" class="admin-modal-submit" :disabled="form.processing">
                <span v-if="form.processing">Please wait...</span>
                <span v-else>Update Directory</span>
            </button>
        </form>
    </AdminModal>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AdminModal from '@/Components/Admin/AdminModal.vue';

const props = defineProps({
    modelValue: Boolean,
    directory: Object
});
const emit = defineEmits(['update:modelValue', 'submitted']);

// Provinces grouped per Region
const provinces = {
    Luzon: [
        "Abra", "Alaminos City", "Albay", "Angeles City", "Antipolo City", "Apayao",
        "Aurora", "Baguio City", "Bataan", "Batac City", "Batangas", "Batangas City",
        "Bayawan City", "Bayombong", "Bayugan City", "Benguet", "Biñan City",
        "Bocaue", "Bogo City", "Bulan", "Bulacan", "Cabanatuan City", "Cabuyao City",
        "Cagayan", "Caloocan City", "Camarines Norte", "Camarines Sur",
        "Camalig", "Canaman", "Capas", "Carcar City", "Catanduanes", "Cavite",
        "Cavite City", "Dagupan City", "Dasmariñas City", "Dinalupihan", "Gapan City",
        "General Trias City", "Ifugao", "Ilocos Norte", "Ilocos Sur", "Iriga City",
        "Isabela", "Kalinga", "La Carlota City", "La Union", "Laoag City",
        "Laguna", "Las Piñas City", "Legazpi City", "Lipa City", "Lucena City",
        "Mabalacat City", "Makati City", "Malabon City", "Malaybalay City",
        "Malolos City", "Mandaluyong City", "Manila City", "Marawi City",
        "Marikina City", "Marinduque", "Masbate", "Masbate City", "Metro Manila",
        "Meycauayan City", "Mountain Province", "Muntinlupa City", "Naga City",
        "Narra", "Navotas City", "Nueva Ecija", "Nueva Vizcaya", "Occidental Mindoro",
        "Olongapo City", "Oriental Mindoro", "Ozamiz City", "Palayan City", "Palawan",
        "Pampanga", "Pangasinan", "Parañaque City", "Pasay City", "Pasig City",
        "Pateros", "Puerto Princesa City", "Quezon", "Quezon City", "Quirino",
        "Rizal", "Romblon", "San Carlos City (Pangasinan)", "San Fernando City (La Union)",
        "San Fernando City (Pampanga)", "San Jose City", "San Jose del Monte City",
        "San Juan City", "Santiago City", "Science City of Muñoz", "Sorsogon",
        "Sorsogon City", "Sta. Rosa City", "Taguig City", "Tagaytay City", "Tabaco City",
        "Tacloban City", "Talisay City", "Tanauan City", "Tarlac", "Tarlac City",
        "Tayabas City", "Trece Martires City", "Tuguegarao City", "Urdaneta City",
        "Valenzuela City", "Vigan City", "Zambales"
    ],


    Visayas: [
        "Aklan", "Antique", "Bacolod City", "Bais City", "Bantayan Island", "Barugo",
        "Bayawan City", "Baybay City", "Biliran", "Borongan City", "Bogo City",
        "Bohol", "Calbayog City", "Canlaon City", "Capiz", "Carcar City",
        "Catbalogan City", "Cebu", "Cebu City", "Danao City", "Dumaguete City",
        "Eastern Samar", "Escalante City", "Guihulngan City", "Guimaras",
        "Himamaylan City", "Iloilo", "Iloilo City", "Kabankalan City", "La Carlota City",
        "Lapu-Lapu City", "Leyte", "Maasin City", "Mandaue City", "Negros Occidental",
        "Negros Oriental", "Naga City (Cebu)", "Northern Samar", "Ormoc City",
        "Passi City", "Roxas City", "Sagay City", "Samal", "Samar (Western Samar)",
        "San Carlos City (Negros Occidental)", "Silay City", "Sipalay City",
        "Siquijor", "Southern Leyte", "Tagbilaran City", "Tacloban City",
        "Talisay City (Cebu)", "Talisay City (Negros Occidental)", "Tanjay City",
        "Toledo City", "Victorias City"
    ],


    Mindanao: [
        "Agusan del Norte", "Agusan del Sur", "Basilan", "Bayugan City", "Bislig City",
        "Bukidnon", "Butuan City", "Cabadbaran City", "Cagayan de Oro City", "Camiguin",
        "Cotabato City", "Dapitan City", "Davao City", "Davao de Oro", "Davao del Norte",
        "Davao del Sur", "Davao Occidental", "Davao Oriental", "Dipolog City",
        "Dinagat Islands", "Digos City", "El Salvador City", "Gingoog City",
        "General Santos City", "Iligan City", "Isabela City", "Kidapawan City",
        "Koronadal City", "Lanao del Norte", "Lanao del Sur", "Malaybalay City",
        "Maguindanao del Norte", "Maguindanao del Sur", "Marawi City", "Mati City",
        "Misamis Occidental", "Misamis Oriental", "North Cotabato", "Oroquieta City",
        "Ozamiz City", "Pagadian City", "Panabo City", "Samal City", "Sarangani",
        "South Cotabato", "Sultan Kudarat", "Surigao City", "Surigao del Norte",
        "Surigao del Sur", "Tagum City", "Tacurong City", "Tangub City", "Tawi-Tawi",
        "Valencia City", "Zamboanga City", "Zamboanga del Norte", "Zamboanga del Sur",
        "Zamboanga Sibugay"
    ],

};

const form = ref({
    area: '',
    region: '',
    place: '',
    business_name: '',
    address: '',
    contact_name: '',
    contact_no: '',
    processing: false
});

const availableProvinces = ref([]);

function updateProvinces() {
    availableProvinces.value = provinces[form.value.region] || [];
    if (!availableProvinces.value.includes(form.value.place)) {
        form.value.place = "";
    }
}

const resetForm = () => {
    form.value = {
        area: '',
        region: '',
        place: '',
        business_name: '',
        address: '',
        contact_name: '',
        contact_no: '',
        processing: false
    };
    availableProvinces.value = [];
};

const populateForm = (directory) => {
    if (!directory) return;

    let contactNo = directory.contact_no || '';
    if (contactNo.startsWith('+63')) {
        contactNo = '0' + contactNo.slice(3);
    }

    form.value = {
        area: directory.area || '',
        region: directory.region || '',
        place: directory.place || '',
        business_name: directory.business_name || '',
        address: directory.address || '',
        contact_name: directory.contact_name || '',
        contact_no: contactNo,
        processing: false
    };
    updateProvinces();
};

watch(() => props.directory, (newDirectory) => {
    if (newDirectory) populateForm(newDirectory);
}, { immediate: true });

watch(() => props.modelValue, (val) => {
    if (!val) {
        resetForm();
    } else if (props.directory) {
        populateForm(props.directory);
    }
});

async function submitForm() {
    form.value.processing = true;

    let formattedContact = form.value.contact_no;

    if (formattedContact.startsWith('0')) {
        formattedContact = '+63' + formattedContact.slice(1);
    } else if (!formattedContact.startsWith('+63')) {
        formattedContact = '+63' + formattedContact;
    }

    const payload = {
        ...form.value,
        contact_no: formattedContact,
        address: form.value.address.trim() === "" ? null : form.value.address
    };

    router.put(`/directories/${props.directory.id}`, payload, {
        onSuccess: () => {
            emit('update:modelValue', false);
            resetForm();
            Swal.fire({
                title: 'Success!',
                text: 'Directory updated successfully!',
                icon: 'success',
                confirmButtonColor: '#3085d6',
            }).then(() => router.visit('/directories'));
        },
        onError: () => {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to update directory. Please check your input.',
                icon: 'error',
                confirmButtonColor: '#d33',
            });
        },
        onFinish: () => {
            form.value.processing = false;
        }
    });
}
</script>
