<template>
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-2xl mx-2 sm:mx-4 md:mx-0 bg-white rounded-2xl shadow-2xl p-4 sm:p-6 md:p-8 max-h-[90vh] flex flex-col overflow-y-auto"
            @click.stop>
            <!-- Close Button -->
            <button
                class="absolute top-3 right-3 text-gray-700 text-3xl w-10 h-10 flex items-center justify-center rounded-full hover:text-black hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                @click="$emit('update:modelValue', false)" aria-label="Close modal">
                &times;
            </button>

            <!-- Modal Title -->
            <h2 class="text-2xl sm:text-3xl font-bold text-center mb-6 text-gray-800">Edit Directory</h2>

            <!-- Directory Form -->
            <form @submit.prevent="submitForm" class="flex-1 flex flex-col justify-between">
                <div class="grid grid-cols-1 gap-4">
                    <!-- Area -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Area<span
                                class="text-red-500">*</span></label>
                        <input v-model="form.area" class="w-full border border-gray-300 rounded-lg p-3 text-gray-900 bg-white
                                   focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter area"
                            required />
                    </div>

                    <!-- Region -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Region<span
                                class="text-red-500">*</span></label>
                        <select v-model="form.region" @change="updateProvinces" class="w-full border border-gray-300 rounded-lg p-3 text-gray-900 bg-white
                                   focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                            <option value="">Select Region</option>
                            <option value="Luzon">Luzon</option>
                            <option value="Visayas">Visayas</option>
                            <option value="Mindanao">Mindanao</option>
                        </select>
                    </div>

                    <!-- Place (Provinces) -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Province<span
                                class="text-red-500">*</span></label>
                        <select v-model="form.place" class="w-full border border-gray-300 rounded-lg p-3 text-gray-900 bg-white
                                   focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                            <option value="">Select Province</option>
                            <option v-for="province in availableProvinces" :key="province" :value="province">
                                {{ province }}
                            </option>
                        </select>
                    </div>

                    <!-- Business Name -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Business Name<span
                                class="text-red-500">*</span></label>
                        <input v-model="form.business_name" class="w-full border border-gray-300 rounded-lg p-3 text-gray-900 bg-white
                                   focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="Enter business name" required />
                    </div>

                    <!-- Address -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Address</label>
                        <textarea v-model="form.address" class="w-full border border-gray-300 rounded-lg p-3 text-gray-900 bg-white
           focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none min-h-[80px]"
                            placeholder="Enter address (optional)">
                        </textarea>
                    </div>


                    <!-- Contact Name -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Contact Name<span
                                class="text-red-500">*</span></label>
                        <input v-model="form.contact_name" class="w-full border border-gray-300 rounded-lg p-3 text-gray-900 bg-white
                                   focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="Enter contact person" required
                            @input="form.contact_name = form.contact_name.charAt(0).toUpperCase() + form.contact_name.slice(1)" />
                    </div>

                    <!-- Contact No -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Contact No<span
                                class="text-red-500">*</span></label>
                        <input v-model="form.contact_no" class="w-full border border-gray-300 rounded-lg p-3 text-gray-900 bg-white
                                   focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="Enter contact number" required type="tel" maxlength="11" inputmode="numeric"
                            pattern="[0-9]*"
                            @input="form.contact_no = form.contact_no.replace(/[^0-9]/g, '').slice(0, 11)" />
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-8">
                    <button type="submit" class="w-full bg-blue-600 text-white text-lg font-semibold py-3 rounded-lg
                               hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition
                               disabled:opacity-80 disabled:cursor-not-allowed" :disabled="form.processing">
                        <span v-if="form.processing">Please wait...</span>
                        <span v-else>Update</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    modelValue: Boolean,
    directory: Object
});
const emit = defineEmits(['update:modelValue', 'submitted']);

// Provinces grouped per Region
const provinces = {
    Luzon: [
        "Abra", "Apayao", "Aurora", "Bataan", "Batangas", "Benguet", "Bulacan",
        "Cagayan", "Camarines Norte", "Camarines Sur", "Catanduanes", "Cavite",
        "Ifugao", "Ilocos Norte", "Ilocos Sur", "Isabela", "Kalinga", "La Union",
        "Laguna", "Mountain Province", "Nueva Ecija", "Nueva Vizcaya",
        "Occidental Mindoro", "Oriental Mindoro", "Pampanga", "Pangasinan",
        "Quezon", "Quirino", "Rizal", "Romblon", "Sorsogon", "Tarlac", "Zambales",
        "Albay", "Marinduque", "Masbate", "Metro Manila", "Palawan"
    ], // 38

    Visayas: [
        "Aklan", "Antique", "Biliran", "Bohol", "Capiz", "Cebu", "Eastern Samar",
        "Guimaras", "Iloilo", "Leyte", "Negros Occidental", "Negros Oriental",
        "Northern Samar", "Samar (Western Samar)", "Southern Leyte",
        "Dinagat Islands", "Siquijor", "Camotes Islands", "Bantayan Islands",
        "Biliran Islands", "Guimaras Islands", "Capul Island", "Biliran Province",
        "Carles Islands", "Kalibo Province", "Ormoc Province", "Tacloban Province"
    ], // 27

    Mindanao: [
        "Agusan del Norte", "Agusan del Sur", "Basilan", "Bukidnon", "Camiguin",
        "Compostela Valley (Davao de Oro)", "Davao del Norte", "Davao del Sur",
        "Davao Occidental", "Davao Oriental", "Dinagat Islands", "Lanao del Norte",
        "Lanao del Sur", "Maguindanao del Norte", "Maguindanao del Sur",
        "Misamis Occidental", "Misamis Oriental", "North Cotabato",
        "Sarangani", "South Cotabato", "Sultan Kudarat", "Sulu", "Surigao del Norte",
        "Surigao del Sur", "Tawi-Tawi", "Zamboanga del Norte", "Zamboanga del Sur",
        "Zamboanga Sibugay"
    ] // 28
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
    form.value = {
        area: directory.area || '',
        region: directory.region || '',
        place: directory.place || '',
        business_name: directory.business_name || '',
        address: directory.address || '',
        contact_name: directory.contact_name || '',
        contact_no: directory.contact_no || '',
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

    // format contact_no with +63
    let formattedContact = form.value.contact_no;
    if (formattedContact.startsWith("0")) {
        formattedContact = "+63" + formattedContact.slice(1);
    } else if (!formattedContact.startsWith("+63")) {
        formattedContact = "+63" + formattedContact;
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
