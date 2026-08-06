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
                    <RichTextEditor
                        v-model="form.qualifications"
                        placeholder="Enter qualifications (use bullet list for each item)..."
                        min-height="160px"
                    />
                    <p class="admin-form-hint">Use the toolbar to format text and create bullet or numbered lists.</p>
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
import RichTextEditor from '@/Components/Admin/RichTextEditor.vue';
import { isQualificationsEmpty, normalizeQualifications, qualificationsToHtml } from '@/utils/qualifications';

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
    qualifications: '',
    processing: false
});

const resetForm = () => {
    form.value = {
        employment_type: '',
        position: '',
        details: '',
        location: '',
        job_description: '',
        qualifications: '',
        processing: false
    };
};

const populateForm = (career) => {
    if (!career) return;

    form.value = {
        employment_type: career.employment_type || '',
        position: career.position || '',
        details: career.details || '',
        location: career.location || '',
        qualifications: qualificationsToHtml(career.qualifications),
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

function capitalizeFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function handleInput(field) {
    form.value[field] = capitalizeFirst(form.value[field]);
}

async function submitForm() {
    if (isQualificationsEmpty(form.value.qualifications)) {
        Swal.fire({ title: 'Required!', text: 'Please enter at least one qualification.', icon: 'warning', confirmButtonColor: '#3085d6' });
        return;
    }

    form.value.processing = true;

    const payload = {
        ...form.value,
        qualifications: normalizeQualifications(form.value.qualifications)
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
