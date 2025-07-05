<template>
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div
            class="relative w-full max-w-2xl mx-2 sm:mx-4 md:mx-0 bg-white rounded-2xl shadow-2xl p-4 sm:p-6 md:p-8 max-h-[90vh] flex flex-col overflow-y-auto"
            @click.stop
        >
            <!-- Close Button -->
            <button
                class="absolute top-3 right-3 text-gray-700 text-3xl w-10 h-10 flex items-center justify-center rounded-full hover:text-black hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                @click="$emit('update:modelValue', false)"
                aria-label="Close modal"
            >
                &times;
            </button>
            <!-- Modal Title -->
            <h2 class="text-2xl sm:text-3xl font-bold text-center mb-6 text-gray-800">Edit Career Posting</h2>
            <!-- Career Form -->
            <form @submit.prevent="submitForm" class="flex-1 flex flex-col justify-between">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Employment Type<span class="text-red-500">*</span></label>
                        <select v-model="form.employment_type"
                            class="w-full border border-gray-300 rounded-lg p-3 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            required>
                            <option value="" disabled>Select employment type</option>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Position<span class="text-red-500">*</span></label>
                        <input v-model="form.position" @input="handleInput('position')"
                            class="w-full border border-gray-300 rounded-lg p-3 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="Enter job title"
                            required />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Details<span class="text-red-500">*</span></label>
                        <textarea v-model="form.details" @input="handleInput('details')"
                            class="w-full border border-gray-300 rounded-lg p-3 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none min-h-[80px]"
                            placeholder="Enter job details"
                            required></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Location<span class="text-red-500">*</span></label>
                        <input v-model="form.location" @input="handleInput('location')"
                            class="w-full border border-gray-300 rounded-lg p-3 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="Enter job location"
                            required />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Job Description<span class="text-red-500">*</span></label>
                        <div v-for="(desc, idx) in form.job_description" :key="idx" class="mb-2">
                            <textarea v-model="form.job_description[idx]" @input="handleJobDescriptionInput(idx)"
                                class="w-full border border-gray-300 rounded-lg p-3 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none min-h-[80px]"
                                :placeholder="`Enter job description section ${idx + 1}...`"
                                required></textarea>
                            <div class="flex justify-between items-center mt-1">
                                <button type="button" @click="removeJobDescription(idx)" v-if="form.job_description.length > 1"
                                    class="text-red-500 hover:text-red-700 text-sm font-semibold focus:outline-none focus:underline">
                                    Remove Section
                                </button>
                                <span class="text-xs text-gray-500">Section {{ idx + 1 }}</span>
                            </div>
                        </div>
                        <button type="button" @click="addJobDescription"
                            class="mt-2 text-blue-600 hover:text-blue-800 text-sm font-semibold focus:outline-none focus:underline">
                            + Add Job Description Section
                        </button>
                    </div>
                </div>
                <div class="mt-8">
                    <button type="submit"
                        class="w-full bg-blue-600 text-white text-lg font-semibold py-3 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition disabled:opacity-80 disabled:cursor-not-allowed"
                        :disabled="form.processing">
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
    career: Object
});
const emit = defineEmits(['update:modelValue', 'submitted']);

const form = ref({
    employment_type: '',
    position: '',
    details: '',
    location: '',
    job_description: [''],
    processing: false
});

const resetForm = () => {
    form.value = {
        employment_type: '',
        position: '',
        details: '',
        location: '',
        job_description: [''],
        processing: false
    };
};

const populateForm = (career) => {
    if (!career) return;
    // Split job_description by colons for multi-section editing
    let jobDescSections = [''];
    if (career.job_description) {
        jobDescSections = career.job_description.split(': ').map(s => s.trim()).filter(s => s !== '');
    }
    if (jobDescSections.length === 0) jobDescSections = [''];
    form.value = {
        employment_type: career.employment_type || '',
        position: career.position || '',
        details: career.details || '',
        location: career.location || '',
        job_description: jobDescSections,
        processing: false
    };
};

watch(() => props.career, (newCareer) => {
    if (newCareer) {
        populateForm(newCareer);
    }
}, { immediate: true });

watch(() => props.modelValue, (val) => {
    if (!val) {
        resetForm();
    } else if (props.career) {
        populateForm(props.career);
    }
});

function addJobDescription() {
    form.value.job_description.push('');
}
function removeJobDescription(idx) {
    if (form.value.job_description.length > 1) {
        form.value.job_description.splice(idx, 1);
    }
}

function capitalizeFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function handleInput(field) {
    form.value[field] = capitalizeFirst(form.value[field]);
}
function handleJobDescriptionInput(idx) {
    form.value.job_description[idx] = capitalizeFirst(form.value.job_description[idx]);
}

async function submitForm() {
    form.value.processing = true;
    // Combine job description sections into a single string separated by colons
    const combinedJobDescription = form.value.job_description.filter(d => d.trim() !== '').join(': ');
    const payload = {
        ...form.value,
        job_description: combinedJobDescription
    };
    router.put(`/careers/${props.career.id}`, payload, {
        onSuccess: () => {
            emit('update:modelValue', false);
            resetForm();
            Swal.fire({
                title: 'Success!',
                text: 'Career posting updated successfully!',
                icon: 'success',
                confirmButtonColor: '#3085d6',
            }).then(() => {
                router.visit('/careers');
            });
        },
        onError: (errors) => {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to update career posting. Please check your input.',
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
