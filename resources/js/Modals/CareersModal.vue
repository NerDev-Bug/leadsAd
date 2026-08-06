<template>
    <AdminModal
        :model-value="modelValue"
        title="Add Career Posting"
        subtitle="Create a new job opening"
        icon="add"
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
                    <textarea v-model="form.details" @input="handleInput('details')" class="admin-form-textarea" placeholder="Enter the subsidiary or department" required></textarea>
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
                <span v-else>Submit Career Posting</span>
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
import { isQualificationsEmpty, normalizeQualifications } from '@/utils/qualifications';

const props = defineProps({ modelValue: Boolean });
const emit = defineEmits(['update:modelValue', 'submitted']);

const form = ref({
    employment_type: '', position: '', details: '', location: '',
    job_description: '', qualifications: '', processing: false,
});

const resetForm = () => {
    form.value = { employment_type: '', position: '', details: '', location: '', job_description: '', qualifications: '', processing: false };
};

watch(() => props.modelValue, (val) => { if (!val) resetForm(); });

function capitalizeFirst(str) { return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''; }
function handleInput(field) { form.value[field] = capitalizeFirst(form.value[field]); }

async function submitForm() {
    if (isQualificationsEmpty(form.value.qualifications)) {
        Swal.fire({ title: 'Required!', text: 'Please enter at least one qualification.', icon: 'warning', confirmButtonColor: '#057A31' });
        return;
    }

    form.value.processing = true;
    const payload = { ...form.value, qualifications: normalizeQualifications(form.value.qualifications) };
    router.post('/careers', payload, {
        onSuccess: () => {
            emit('update:modelValue', false);
            resetForm();
            Swal.fire({ title: 'Success!', text: 'Career posting added successfully!', icon: 'success', confirmButtonColor: '#057A31' })
                .then(() => router.visit('/careers'));
        },
        onError: () => {
            Swal.fire({ title: 'Error!', text: 'Failed to add career posting.', icon: 'error', confirmButtonColor: '#d33' });
        },
        onFinish: () => { form.value.processing = false; },
    });
}
</script>
