<template>
    <AdminModal
        :model-value="modelValue"
        title="Add Product"
        subtitle="Fill in the product details below"
        icon="add"
        size="lg"
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
                    <div v-for="(dosage, idx) in form.dosages" :key="idx" class="admin-form-array-row">
                        <input v-model="form.dosages[idx]" @input="handleArrayInput('dosages', idx)" type="text"
                            class="admin-form-input" required />
                        <button type="button" @click="removeDosage(idx)" v-if="form.dosages.length > 1"
                            class="admin-form-remove-btn">&minus;</button>
                    </div>
                    <button type="button" @click="addDosage" class="admin-form-add-btn">+ Add Dosage</button>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Target<span class="admin-form-required">*</span></label>
                    <div v-for="(target, idx) in form.targets" :key="idx" class="admin-form-array-row">
                        <input v-model="form.targets[idx]" @input="handleArrayInput('targets', idx)" type="text"
                            class="admin-form-input" required />
                        <button type="button" @click="removeTarget(idx)" v-if="form.targets.length > 1"
                            class="admin-form-remove-btn">&minus;</button>
                    </div>
                    <button type="button" @click="addTarget" class="admin-form-add-btn">+ Add Target</button>
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
                        accept=".jpg,.jpeg,.png" />
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

const props = defineProps({
    modelValue: Boolean
});
const emit = defineEmits(['update:modelValue', 'submitted']);

const form = ref({
    type: '',
    description: '',
    features: '',
    dosages: [''],
    targets: [''],
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
        dosages: [''],
        targets: [''],
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

const addTarget = () => { form.value.targets.push(''); };
const removeTarget = (idx) => { if (form.value.targets.length > 1) form.value.targets.splice(idx, 1); };
const addDosage = () => { form.value.dosages.push(''); };
const removeDosage = (idx) => { if (form.value.dosages.length > 1) form.value.dosages.splice(idx, 1); };

function capitalizeFirstLetter(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function handleInput(field) {
    form.value[field] = capitalizeFirstLetter(form.value[field]);
}
function handleArrayInput(field, idx) {
    form.value[field][idx] = capitalizeFirstLetter(form.value[field][idx]);
}

async function submitForm() {
    form.value.processing = true;
    form.value.description = capitalizeFirstLetter(form.value.description);
    form.value.features = capitalizeFirstLetter(form.value.features);
    form.value.dosages = form.value.dosages.map(capitalizeFirstLetter);
    form.value.targets = form.value.targets.map(capitalizeFirstLetter);
    const formData = new FormData();
    formData.append('type', form.value.type);
    formData.append('description', form.value.description);
    formData.append('features', form.value.features);
    formData.append('dosage', form.value.dosages.join(', '));
    formData.append('target', form.value.targets.join(', '));
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
        onError: () => {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to add product. Please check your input.',
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
