<template>
    <AdminModal
        :model-value="modelValue"
        title="Edit Career Posting"
        subtitle="Update the job opening details"
        icon="edit"
        size="xl"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <form @submit.prevent="submitForm">
            <div class="space-y-4">
                <div class="admin-form-group">
                    <label class="admin-form-label">Employment Type<span class="admin-form-required">*</span></label>
                    <select v-model="form.employment_type" class="admin-form-select" required>
                        <option value="" disabled>Select employment type</option>
                        <option value="Full-time">Full-time</option>
                        <option value="Part-time">Part-time</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Position<span class="admin-form-required">*</span></label>
                    <input v-model="form.position" @input="handleInput('position')" class="admin-form-input" placeholder="Enter job title" required />
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Subsidiary<span class="admin-form-required">*</span></label>
                    <textarea v-model="form.details" @input="handleInput('details')" class="admin-form-textarea" placeholder="Enter subsidiary" required></textarea>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Location<span class="admin-form-required">*</span></label>
                    <input v-model="form.location" @input="handleInput('location')" class="admin-form-input" placeholder="Enter job location" required />
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Job Description<span class="admin-form-required">*</span></label>
                    <textarea v-model="form.job_description" @input="handleInput('job_description')" class="admin-form-textarea min-h-[100px]" placeholder="Enter full job description" required></textarea>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Qualifications<span class="admin-form-required">*</span></label>
                    <div v-for="(qual, idx) in form.qualifications" :key="idx" class="mb-3">
                        <textarea v-model="form.qualifications[idx]" @input="handleQualificationInput(idx)" class="admin-form-textarea" :placeholder="`Qualification ${idx + 1}...`" required></textarea>
                        <div class="mt-1 flex items-center justify-between">
                            <button type="button" @click="removeQualification(idx)" v-if="form.qualifications.length > 1" class="text-xs font-semibold text-red-500 hover:text-red-700">Remove</button>
                            <span class="text-xs text-slate-400">#{{ idx + 1 }}</span>
                        </div>
                    </div>
                    <button type="button" @click="addQualification" class="admin-form-add-btn">+ Add Qualification</button>
                </div>
            </div>
            <button type="submit" class="admin-modal-submit" :disabled="form.processing">
                <span v-if="form.processing">Please wait...</span>
                <span v-else>Update Career Posting</span>
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
    career: Object
});
const emit = defineEmits(['update:modelValue', 'submitted']);

const form = ref({
    employment_type: '',
    position: '',
    details: '',
    location: '',
    job_description: '',
    qualifications: [''],
    processing: false
});

const resetForm = () => {
    form.value = {
        employment_type: '',
        position: '',
        details: '',
        location: '',
        job_description: '',
        qualifications: [''],
        processing: false
    };
};

const populateForm = (career) => {
    if (!career) return;

    // Split qualifications back into array (if saved as string with ": ")
    let qualificationSections = [''];
    if (career.qualifications) {
        qualificationSections = career.qualifications.split(': ').map(s => s.trim()).filter(s => s !== '');
    }
    if (qualificationSections.length === 0) qualificationSections = [''];

    form.value = {
        employment_type: career.employment_type || '',
        position: career.position || '',
        details: career.details || '',
        location: career.location || '',
        qualifications: qualificationSections,
        job_description: career.job_description || '',
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

function addQualification() {
    form.value.qualifications.push('');
}
function removeQualification(idx) {
    if (form.value.qualifications.length > 1) {
        form.value.qualifications.splice(idx, 1);
    }
}

function capitalizeFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function handleInput(field) {
    form.value[field] = capitalizeFirst(form.value[field]);
}
function handleQualificationInput(idx) {
    form.value.qualifications[idx] = capitalizeFirst(form.value.qualifications[idx]);
}

async function submitForm() {
    form.value.processing = true;

    // Combine qualifications into a single string separated by colons
    const combinedQualifications = form.value.qualifications.filter(q => q.trim() !== '').join(': ');

    const payload = {
        ...form.value,
        qualifications: combinedQualifications
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
        onError: () => {
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
