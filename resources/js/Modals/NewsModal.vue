<template>
    <AdminModal
        :model-value="modelValue"
        title="Add News Article"
        subtitle="Create a new news post"
        icon="add"
        size="xl"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <form @submit.prevent="submitForm">
            <div class="space-y-4">
                <div class="admin-form-group">
                    <label class="admin-form-label">Title<span class="admin-form-required">*</span></label>
                    <input v-model="form.title" type="text" class="admin-form-input" placeholder="Enter news title" @input="capitalizeFirstLetter('title')" required />
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Content<span class="admin-form-required">*</span></label>
                    <div v-for="(content, idx) in form.contents" :key="idx" class="mb-3">
                        <textarea v-model="form.contents[idx]" class="admin-form-textarea" :placeholder="`Section ${idx + 1}...`" @input="capitalizeFirstLetterContent(idx)" required></textarea>
                        <div class="mt-1 flex items-center justify-between">
                            <button type="button" @click="removeContent(idx)" v-if="form.contents.length > 1" class="text-xs font-semibold text-red-500 hover:text-red-700">Remove</button>
                            <span class="text-xs text-slate-400">Section {{ idx + 1 }}</span>
                        </div>
                    </div>
                    <button type="button" @click="addContent" class="admin-form-add-btn">+ Add Content Section</button>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Published Date</label>
                    <VueDatePicker v-model="form.published_at" model-type="format" format="yyyy-MM-dd" :enable-time-picker="false" :clearable="true" :auto-apply="true" :teleport="true" input-class-name="admin-form-input" placeholder="Select date" />
                    <p class="admin-form-hint">Leave empty to use current date</p>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Featured Image</label>
                    <input type="file" @change="onFileChange" :class="['admin-form-file', imageError ? 'admin-form-file-error' : '']" accept=".jpg,.jpeg,.png,.webp" />
                    <p class="admin-form-hint">Recommended: 1200x630px, max 10MB</p>
                    <p v-if="imageError" class="admin-form-error">{{ imageError }}</p>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Article Image</label>
                    <input type="file" @change="onFileChange2" :class="['admin-form-file', image2Error ? 'admin-form-file-error' : '']" accept=".jpg,.jpeg,.png,.webp" />
                    <div v-if="featuredImages2Preview" class="mt-2">
                        <img :src="featuredImages2Preview" class="h-24 w-32 rounded-xl object-cover ring-2 ring-slate-100" />
                    </div>
                    <p class="admin-form-hint">Recommended: 1200x630px, max 10MB</p>
                    <p v-if="image2Error" class="admin-form-error">{{ image2Error }}</p>
                </div>
            </div>
            <button type="submit" class="admin-modal-submit" :disabled="form.processing">
                <span v-if="form.processing">Please wait...</span>
                <span v-else>Submit Article</span>
            </button>
        </form>
    </AdminModal>
</template>

<script setup>
import { ref, watch, defineProps, defineEmits } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import VueDatePicker from '@vuepic/vue-datepicker';
import AdminModal from '@/Components/Admin/AdminModal.vue';

const props = defineProps({ modelValue: Boolean });
const emit = defineEmits(['update:modelValue', 'submitted']);

const form = ref({
    title: '',
    contents: [''],
    published_at: '',
    featured_image: null,
    featured_image_2: null,
    processing: false
});

const imageError = ref('');
const image2Error = ref('');
const featuredImages2Preview = ref(null);

const resetForm = () => {
    form.value = {
        title: '',
        contents: [''],
        published_at: '',
        featured_image: null,
        featured_image_2: null,
        processing: false
    };
    imageError.value = '';
    image2Error.value = '';
    featuredImages2Preview.value = null;
};

watch(() => props.modelValue, (val) => {
    if (!val) resetForm();
});

function capitalizeFirstLetter(field) {
    if (form.value[field] && form.value[field].length > 0) {
        form.value[field] = form.value[field].charAt(0).toUpperCase() + form.value[field].slice(1);
    }
}

function capitalizeFirstLetterContent(idx) {
    if (form.value.contents[idx] && form.value.contents[idx].length > 0) {
        form.value.contents[idx] = form.value.contents[idx].charAt(0).toUpperCase() + form.value.contents[idx].slice(1);
    }
}

function addContent() {
    form.value.contents.push('');
}

function removeContent(idx) {
    if (form.value.contents.length > 1) {
        form.value.contents.splice(idx, 1);
    }
}

function onFileChange(event) {
    const file = event.target.files[0];
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    const maxSize = 10 * 1024 * 1024;
    if (!file) return;

    if (!allowedTypes.includes(file.type)) {
        imageError.value = 'Only JPEG, JPG, PNG, or WebP files are allowed.';
        event.target.value = '';
        return;
    }
    if (file.size > maxSize) {
        imageError.value = 'File size must be less than 10MB.';
        event.target.value = '';
        return;
    }
    form.value.featured_image = file;
    imageError.value = '';
}

function onFileChange2(event) {
    const file = event.target.files[0];
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    const maxSize = 10 * 1024 * 1024;
    if (!file) return;

    if (!allowedTypes.includes(file.type)) {
        image2Error.value = 'Only JPEG, JPG, PNG, or WebP files are allowed.';
        event.target.value = '';
        return;
    }
    if (file.size > maxSize) {
        image2Error.value = 'File size must be less than 10MB.';
        event.target.value = '';
        return;
    }

    form.value.featured_image_2 = file;
    featuredImages2Preview.value = URL.createObjectURL(file);
    image2Error.value = '';
}

async function submitForm() {
    form.value.processing = true;
    if (!form.value.published_at) {
        form.value.published_at = new Date().toISOString().slice(0, 10);
    }

    const combinedContent = form.value.contents.filter(c => c.trim() !== '').join(': ');
    const formData = new FormData();
    formData.append('title', form.value.title);
    formData.append('content', combinedContent);
    formData.append('published_at', form.value.published_at);
    if (form.value.featured_image) formData.append('featured_image', form.value.featured_image);
    if (form.value.featured_image_2) formData.append('featured_image_2', form.value.featured_image_2);

    router.post('/news', formData, {
        forceFormData: true,
        onSuccess: () => {
            emit('update:modelValue', false);
            resetForm();
            Swal.fire({
                title: 'Success!',
                text: 'News article added successfully!',
                icon: 'success',
                confirmButtonColor: '#3085d6'
            }).then(() => router.visit('/news'));
        },
        onError: () => {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to add news article. Please check your input.',
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        },
        onFinish: () => (form.value.processing = false)
    });
}
</script>
