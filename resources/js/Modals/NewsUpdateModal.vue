<template>
    <AdminModal
        :model-value="modelValue"
        title="Update News Article"
        subtitle="Edit the article details below"
        icon="edit"
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
                    <label class="admin-form-label">Published Date<span class="admin-form-required">*</span></label>
                    <VueDatePicker v-model="form.published_at" model-type="format" format="yyyy-MM-dd" :enable-time-picker="false" :clearable="true" :auto-apply="true" :teleport="true" input-class-name="admin-form-input" placeholder="Select date" />
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Featured Image</label>
                    <div v-if="form.currentFeaturedImage" class="admin-modal-preview mb-2 flex items-center gap-3">
                        <img :src="`/news_image/${form.currentFeaturedImage.replace('news/', '')}`" alt="Current" class="h-24 w-32 rounded-xl object-cover ring-2 ring-slate-100" />
                        <span class="text-xs text-slate-500">Current image</span>
                    </div>
                    <input type="file" @change="onFileChange" :class="['admin-form-file', imageError ? 'admin-form-file-error' : '']" accept=".jpg,.jpeg,.png,.webp" />
                    <p class="admin-form-hint">Leave empty to keep current image (1200x630px, max 10MB)</p>
                    <p v-if="imageError" class="admin-form-error">{{ imageError }}</p>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Article Image</label>
                    <div v-if="form.currentFeaturedImage2" class="admin-modal-preview mb-2 flex items-center gap-3">
                        <img :src="`/news_image/${form.currentFeaturedImage2.replace('news/', '')}`" alt="Current" class="h-24 w-32 rounded-xl object-cover ring-2 ring-slate-100" />
                        <span class="text-xs text-slate-500">Current image</span>
                    </div>
                    <input type="file" @change="onFileChange2" :class="['admin-form-file', image2Error ? 'admin-form-file-error' : '']" accept=".jpg,.jpeg,.png,.webp" />
                    <div v-if="featuredImage2Preview" class="mt-2">
                        <img :src="featuredImage2Preview" alt="Preview" class="h-24 w-32 rounded-xl object-cover ring-2 ring-slate-100" />
                    </div>
                    <p class="admin-form-hint">Leave empty to keep current image</p>
                    <p v-if="image2Error" class="admin-form-error">{{ image2Error }}</p>
                </div>
            </div>
            <button type="submit" class="admin-modal-submit" :disabled="form.processing">
                <span v-if="form.processing">Updating...</span>
                <span v-else>Update News Article</span>
            </button>
        </form>
    </AdminModal>
</template>

<script setup>
import { ref, watch, defineProps, defineEmits } from 'vue'
import { router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import VueDatePicker from '@vuepic/vue-datepicker'
import AdminModal from '@/Components/Admin/AdminModal.vue'

const props = defineProps({
    modelValue: Boolean,
    news: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits(['update:modelValue', 'submitted'])

const form = ref({
    title: '',
    contents: [''],
    published_at: '',
    featured_image: null,
    currentFeaturedImage: '',
    featured_image_2: null,
    currentFeaturedImage2: '',
    processing: false,
})

// Errors & Preview
const imageError = ref('')
const image2Error = ref('')
const featuredImage2Preview = ref(null)

const resetForm = () => {
    form.value = {
        title: '',
        contents: [''],
        published_at: '',
        featured_image: null,
        currentFeaturedImage: '',
        featured_image_2: null,
        currentFeaturedImage2: '',
        processing: false,
    }
    imageError.value = ''
    image2Error.value = ''
    featuredImage2Preview.value = null
}

const populateForm = (newsItem) => {
    if (!newsItem) return

    let contents = ['']
    if (newsItem.content) {
        contents = newsItem.content.split(': ').map((c) => c.trim()).filter((c) => c !== '')
    }
    if (contents.length === 0) contents = ['']

    form.value = {
        title: newsItem.title || '',
        contents,
        published_at: newsItem.published_at
            ? new Date(newsItem.published_at).toISOString().slice(0, 10)
            : '',
        featured_image: null,
        currentFeaturedImage: newsItem.featured_image || '',
        featured_image_2: null,
        currentFeaturedImage2: newsItem.featured_image_2 || '',
        processing: false,
    }
}

// Watchers
watch(() => props.news, (n) => n && populateForm(n), { immediate: true })
watch(() => props.modelValue, (val) => !val && resetForm())

function capitalizeFirstLetter(field) {
    const val = form.value[field]
    if (val && val.length > 0) form.value[field] = val.charAt(0).toUpperCase() + val.slice(1)
}
function capitalizeFirstLetterContent(idx) {
    const val = form.value.contents[idx]
    if (val && val.length > 0)
        form.value.contents[idx] = val.charAt(0).toUpperCase() + val.slice(1)
}
function addContent() {
    form.value.contents.push('')
}
function removeContent(idx) {
    if (form.value.contents.length > 1) form.value.contents.splice(idx, 1)
}

// Featured Image 1
function onFileChange(e) {
    const file = e.target.files[0]
    validateFile(file, imageError, (valid) => (form.value.featured_image = valid))
}

// Featured Image 2 (single)
function onFileChange2(e) {
    const file = e.target.files[0]
    validateFile(file, image2Error, (valid) => {
        form.value.featured_image_2 = valid
        featuredImage2Preview.value = valid ? URL.createObjectURL(valid) : null
    })
}

function validateFile(file, errorRef, setFile) {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']
    const maxSize = 10 * 1024 * 1024
    if (!file) {
        setFile(null)
        errorRef.value = ''
        return
    }
    if (!allowedTypes.includes(file.type)) {
        errorRef.value = 'Only JPEG, JPG, PNG, or WebP files are allowed.'
        setFile(null)
        return
    }
    if (file.size > maxSize) {
        errorRef.value = 'File size must be less than 10MB.'
        setFile(null)
        return
    }
    errorRef.value = ''
    setFile(file)
}

async function submitForm() {
    if (!props.news?.id) {
        Swal.fire('Error!', 'No news article selected for update.', 'error')
        return
    }

    form.value.processing = true

    const combinedContent = form.value.contents.filter((c) => c.trim() !== '').join(': ')
    const formData = new FormData()
    formData.append('_method', 'PUT')
    formData.append('title', form.value.title)
    formData.append('content', combinedContent)
    formData.append('published_at', form.value.published_at)

    if (form.value.featured_image)
        formData.append('featured_image', form.value.featured_image)
    if (form.value.featured_image_2)
        formData.append('featured_image_2', form.value.featured_image_2)

    try {
        await router.post(`/news/${props.news.id}`, formData, {
            forceFormData: true,
            onSuccess: () => {
                emit('update:modelValue', false)
                resetForm()
                Swal.fire('Success!', 'News article updated successfully!', 'success').then(() =>
                    router.visit('/news')
                )
            },
            onError: (errors) => {
                const msgs = Object.values(errors).flat().join('\n')
                Swal.fire('Validation Error!', msgs || 'Please check your input fields.', 'warning')
            },
            onFinish: () => (form.value.processing = false),
        })
    } catch (e) {
        Swal.fire('Error!', 'Something went wrong while updating the article.', 'error')
        form.value.processing = false
    }
}
</script>
