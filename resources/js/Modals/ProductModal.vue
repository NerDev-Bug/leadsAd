<template>
    <AdminModal
        :model-value="modelValue"
        title="Add Product"
        subtitle="Fill in the product details below"
        icon="add"
        size="xl"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <form @submit.prevent="submitForm">
            <div class="space-y-4">
                <div class="admin-form-group">
                    <label class="admin-form-label">Description<span class="admin-form-required">*</span></label>
                    <textarea v-model="form.description" @input="handleInput('description')"
                        class="admin-form-textarea min-h-[60px]" required></textarea>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Features & Benefits<span class="admin-form-required">*</span></label>
                    <textarea v-model="form.features" @input="handleInput('features')"
                        class="admin-form-textarea min-h-[60px]" required></textarea>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Dosage<span class="admin-form-required">*</span></label>
                    <RichTextEditor
                        v-model="form.dosage"
                        placeholder="Enter dosage details (use bullet list for each item)..."
                        min-height="120px"
                    />
                    <p class="admin-form-hint">Use the toolbar to format text and create bullet or numbered lists.</p>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Target<span class="admin-form-required">*</span></label>
                    <RichTextEditor
                        v-model="form.target"
                        placeholder="Enter target crops (use bullet list for each item)..."
                        min-height="120px"
                    />
                    <p class="admin-form-hint">Use the toolbar to format text and create bullet or numbered lists.</p>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Category<span class="admin-form-required">*</span></label>
                    <select v-model="form.category" required class="admin-form-select">
                        <option value="" disabled selected>— Please choose a category —</option>
                        <option value="Rice">Rice</option>
                        <option value="Mango">Mango</option>
                        <option value="Vegetables">Vegetables</option>
                        <option value="Sugarcane">Sugarcane</option>
                        <option value="Other Crops">Other Crops</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Type<span class="admin-form-required">*</span></label>
                    <select v-model="form.type" required class="admin-form-select">
                        <option value="" disabled selected>— Please choose a type —</option>
                        <option value="Herbicide">Herbicide</option>
                        <option value="Fungicide">Fungicide</option>
                        <option value="Biostimulant">Biostimulant</option>
                        <option value="Insecticide">Insecticide</option>
                        <option value="Molluscicide">Molluscicide</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Product Image<span class="admin-form-required">*</span></label>
                    <input type="file" @change="onFileChange($event, 1)"
                        :class="['admin-form-file', image1Error ? 'admin-form-file-error' : '']"
                        required accept=".jpg,.jpeg,.png" />
                    <p class="admin-form-hint">Recommended resolution size: 550x580</p>
                    <p v-if="image1Error" class="admin-form-error">{{ image1Error }}</p>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Product Name Image<span class="admin-form-required">*</span></label>
                    <input type="file" @change="onFileChange($event, 2)"
                        :class="['admin-form-file', image2Error ? 'admin-form-file-error' : '']"
                        required accept=".jpg,.jpeg,.png" />
                    <p v-if="image2Error" class="admin-form-error">{{ image2Error }}</p>
                </div>
            </div>
            <button type="submit" class="admin-modal-submit" :disabled="form.processing">
                <span v-if="form.processing">Please wait...</span>
                <span v-else>Submit Product</span>
            </button>
        </form>
    </AdminModal>
</template>

<script setup>
import { ref, watch, defineProps, defineEmits } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AdminModal from '@/Components/Admin/AdminModal.vue';
import RichTextEditor from '@/Components/Admin/RichTextEditor.vue';
import { isQualificationsEmpty, normalizeQualifications } from '@/utils/qualifications';

function formatValidationErrors(errors) {
    if (!errors || typeof errors !== 'object') {
        return 'Failed to add product. Please check your input.';
    }

    return Object.values(errors).flat().join('\n');
}

const props = defineProps({
    modelValue: Boolean
});
const emit = defineEmits(['update:modelValue', 'submitted']);

const form = ref({
    type: '',
    description: '',
    features: '',
    dosage: '',
    target: '',
    category: '',
    image1: null,
    image2: null,
    processing: false
});

const image1Error = ref('');
const image2Error = ref('');

const resetForm = () => {
    form.value = {
        type: '',
        description: '',
        features: '',
        dosage: '',
        target: '',
        category: '',
        image1: null,
        image2: null,
        processing: false
    };
    image1Error.value = '';
    image2Error.value = '';
};

watch(() => props.modelValue, (val) => {
    if (!val) resetForm();
});

function onFileChange(event, imgNum) {
    const file = event.target.files[0];
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (file && !allowedTypes.includes(file.type)) {
        if (imgNum === 1) {
            image1Error.value = 'Only JPEG, JPG, or PNG files are allowed.';
            form.value.image1 = null;
        } else if (imgNum === 2) {
            image2Error.value = 'Only JPEG, JPG, or PNG files are allowed.';
            form.value.image2 = null;
        }
        event.target.value = '';
        return;
    }
    if (imgNum === 1) {
        form.value.image1 = file;
        image1Error.value = '';
    } else if (imgNum === 2) {
        form.value.image2 = file;
        image2Error.value = '';
    }
}

function capitalizeFirstLetter(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function handleInput(field) {
    form.value[field] = capitalizeFirstLetter(form.value[field]);
}

async function submitForm() {
    if (isQualificationsEmpty(form.value.dosage)) {
        Swal.fire({ title: 'Required!', text: 'Please enter dosage details.', icon: 'warning', confirmButtonColor: '#057A31' });
        return;
    }

    if (isQualificationsEmpty(form.value.target)) {
        Swal.fire({ title: 'Required!', text: 'Please enter target details.', icon: 'warning', confirmButtonColor: '#057A31' });
        return;
    }

    if (!form.value.image1) {
        Swal.fire({ title: 'Required!', text: 'Please select a product image.', icon: 'warning', confirmButtonColor: '#057A31' });
        return;
    }

    if (!form.value.image2) {
        Swal.fire({ title: 'Required!', text: 'Please select a product name image.', icon: 'warning', confirmButtonColor: '#057A31' });
        return;
    }

    form.value.processing = true;
    form.value.description = capitalizeFirstLetter(form.value.description);
    form.value.features = capitalizeFirstLetter(form.value.features);
    const formData = new FormData();
    formData.append('type', form.value.type);
    formData.append('description', form.value.description);
    formData.append('features', form.value.features);
    formData.append('dosage', normalizeQualifications(form.value.dosage));
    formData.append('target', normalizeQualifications(form.value.target));
    formData.append('category', form.value.category);
    if (form.value.image1) formData.append('image1', form.value.image1);
    if (form.value.image2) formData.append('image2', form.value.image2);

    router.post('/products', formData, {
        forceFormData: true,
        onSuccess: () => {
            emit('update:modelValue', false);
            resetForm();
            Swal.fire({
                title: 'Success!',
                text: 'Product added successfully!',
                icon: 'success',
                confirmButtonColor: '#057A31',
            }).then(() => {
                router.visit('/products');
            });
        },
        onError: (errors) => {
            Swal.fire({
                title: 'Error!',
                text: formatValidationErrors(errors),
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
