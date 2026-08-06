<template>
    <AdminModal
        :model-value="modelValue"
        title="Edit Archived News"
        subtitle="Update the archived article details"
        icon="edit"
        size="xl"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <form @submit.prevent="submitForm">
            <div class="space-y-4">
                <div class="admin-form-group">
                    <label class="admin-form-label">Title<span class="admin-form-required">*</span></label>
                    <input
                        v-model="form.title"
                        type="text"
                        class="admin-form-input"
                        placeholder="Enter news title"
                        @input="capitalizeFirstLetter('title')"
                        required
                    />
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Content<span class="admin-form-required">*</span></label>
                    <RichTextEditor
                        v-model="form.content"
                        placeholder="Write the article content..."
                        min-height="200px"
                    />
                    <p class="admin-form-hint">Use the toolbar to format text and create bullet or numbered lists.</p>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Published Date<span class="admin-form-required">*</span></label>
                    <VueDatePicker
                        v-model="form.published_at"
                        model-type="format"
                        format="yyyy-MM-dd"
                        :enable-time-picker="false"
                        :clearable="true"
                        :auto-apply="true"
                        :teleport="true"
                        input-class-name="admin-form-input"
                        placeholder="Select date"
                    />
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Featured Image</label>
                    <div v-if="form.currentFeaturedImage" class="admin-modal-preview mb-2 flex items-center gap-3">
                        <img :src="archiveImageUrl(form.currentFeaturedImage)" alt="Current" class="h-24 w-32 rounded-xl object-cover ring-2 ring-slate-100" />
                        <span class="text-xs text-slate-500">Current image</span>
                    </div>
                    <input
                        type="file"
                        @change="onFileChange"
                        :class="['admin-form-file', imageError ? 'admin-form-file-error' : '']"
                        accept=".jpg,.jpeg,.png,.webp"
                    />
                    <p class="admin-form-hint">Leave empty to keep current image (1200x630px, max 10MB)</p>
                    <p v-if="imageError" class="admin-form-error">{{ imageError }}</p>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Article Image</label>
                    <div v-if="form.currentFeaturedImage2" class="admin-modal-preview mb-2 flex items-center gap-3">
                        <img :src="archiveImageUrl(form.currentFeaturedImage2)" alt="Current" class="h-24 w-32 rounded-xl object-cover ring-2 ring-slate-100" />
                        <span class="text-xs text-slate-500">Current image</span>
                    </div>
                    <input
                        type="file"
                        @change="onFileChange2"
                        :class="['admin-form-file', image2Error ? 'admin-form-file-error' : '']"
                        accept=".jpg,.jpeg,.png,.webp"
                    />
                    <div v-if="featuredImage2Preview" class="mt-2">
                        <img :src="featuredImage2Preview" alt="Preview" class="h-24 w-32 rounded-xl object-cover ring-2 ring-slate-100" />
                    </div>
                    <p class="admin-form-hint">Leave empty to keep current image</p>
                    <p v-if="image2Error" class="admin-form-error">{{ image2Error }}</p>
                </div>
            </div>
            <button type="submit" class="admin-modal-submit" :disabled="form.processing">
                <span v-if="form.processing">Updating...</span>
                <span v-else>Update Archived Article</span>
            </button>
        </form>
    </AdminModal>
</template>

<script setup>
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';
import VueDatePicker from '@vuepic/vue-datepicker';
import AdminModal from '@/Components/Admin/AdminModal.vue';
import RichTextEditor from '@/Components/Admin/RichTextEditor.vue';
import { csrfHeaders, appendCsrfToFormData } from '@/utils/csrf';
import { isQualificationsEmpty, normalizeQualifications, qualificationsToHtml } from '@/utils/qualifications';

const props = defineProps({
    modelValue: Boolean,
    item: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue', 'updated']);

const form = ref({
    title: '',
    content: '',
    published_at: '',
    featured_image: null,
    currentFeaturedImage: '',
    featured_image_2: null,
    currentFeaturedImage2: '',
    processing: false,
});

const imageError = ref('');
const image2Error = ref('');
const featuredImage2Preview = ref(null);

function archiveImageUrl(path) {
    if (!path) return '';
    return `/archive_news/${path.split('/').pop()}`;
}

const resetForm = () => {
    form.value = {
        title: '',
        content: '',
        published_at: '',
        featured_image: null,
        currentFeaturedImage: '',
        featured_image_2: null,
        currentFeaturedImage2: '',
        processing: false,
    };
    imageError.value = '';
    image2Error.value = '';
    featuredImage2Preview.value = null;
};

const populateForm = (item) => {
    if (!item) return;

    form.value = {
        title: item.title || '',
        content: qualificationsToHtml(item.content),
        published_at: item.published_at
            ? new Date(item.published_at).toISOString().slice(0, 10)
            : '',
        featured_image: null,
        currentFeaturedImage: item.featured_image || '',
        featured_image_2: null,
        currentFeaturedImage2: item.featured_image_2 || '',
        processing: false,
    };
};

watch(() => props.item, (value) => {
    if (value) populateForm(value);
}, { immediate: true });

watch(() => props.modelValue, (open) => {
    if (!open) {
        resetForm();
    } else if (props.item) {
        populateForm(props.item);
    }
});

function capitalizeFirstLetter(field) {
    const val = form.value[field];
    if (val && val.length > 0) {
        form.value[field] = val.charAt(0).toUpperCase() + val.slice(1);
    }
}

function onFileChange(e) {
    const file = e.target.files[0];
    validateFile(file, imageError, (valid) => {
        form.value.featured_image = valid;
    });
}

function onFileChange2(e) {
    const file = e.target.files[0];
    validateFile(file, image2Error, (valid) => {
        form.value.featured_image_2 = valid;
        featuredImage2Preview.value = valid ? URL.createObjectURL(valid) : null;
    });
}

function validateFile(file, errorRef, setFile) {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    const maxSize = 10 * 1024 * 1024;

    if (!file) {
        setFile(null);
        errorRef.value = '';
        return;
    }

    if (!allowedTypes.includes(file.type)) {
        errorRef.value = 'Only JPEG, JPG, PNG, or WebP files are allowed.';
        setFile(null);
        return;
    }

    if (file.size > maxSize) {
        errorRef.value = 'File size must be less than 10MB.';
        setFile(null);
        return;
    }

    errorRef.value = '';
    setFile(file);
}

async function submitForm() {
    if (!props.item?.id) {
        Swal.fire('Error!', 'No archived article selected for update.', 'error');
        return;
    }

    if (isQualificationsEmpty(form.value.content)) {
        Swal.fire({ title: 'Required!', text: 'Please enter article content.', icon: 'warning', confirmButtonColor: '#057A31' });
        return;
    }

    if (!form.value.published_at) {
        Swal.fire({ title: 'Required!', text: 'Please select a published date.', icon: 'warning', confirmButtonColor: '#057A31' });
        return;
    }

    form.value.processing = true;

    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('title', form.value.title);
    formData.append('content', normalizeQualifications(form.value.content));
    formData.append('published_at', form.value.published_at);

    if (form.value.featured_image) {
        formData.append('featured_image', form.value.featured_image);
    }
    if (form.value.featured_image_2) {
        formData.append('featured_image_2', form.value.featured_image_2);
    }

    appendCsrfToFormData(formData);

    try {
        const res = await fetch(`/archive-news/${props.item.id}`, {
            method: 'POST',
            headers: csrfHeaders(),
            credentials: 'same-origin',
            body: formData,
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            if (res.status === 419) {
                throw new Error('Your session expired. Please refresh the page and try again.');
            }

            const message = data.message || Object.values(data.errors || {}).flat().join('\n') || 'Failed to update archived article.';
            throw new Error(message);
        }

        emit('update:modelValue', false);
        emit('updated', data.data || props.item);
        resetForm();

        await Swal.fire({
            title: 'Success!',
            text: 'Archived article updated successfully!',
            icon: 'success',
            confirmButtonColor: '#057A31',
        });
    } catch (error) {
        await Swal.fire({
            title: 'Error!',
            text: error.message || 'Something went wrong while updating the article.',
            icon: 'error',
            confirmButtonColor: '#d33',
        });
    } finally {
        form.value.processing = false;
    }
}
</script>
