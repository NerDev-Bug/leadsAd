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
            <h2 class="text-2xl sm:text-3xl font-bold text-center mb-6 text-gray-800">Update News Article</h2>

            <!-- News Form -->
            <form @submit.prevent="submitForm" class="flex-1 flex flex-col justify-between">
                <div class="grid grid-cols-1 gap-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Title<span
                                class="text-red-500">*</span></label>
                        <input v-model="form.title" type="text"
                            class="w-full border border-gray-300 rounded-lg p-3 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="Enter news title" @input="capitalizeFirstLetter('title')" required />
                    </div>

                    <!-- Content -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Content<span
                                class="text-red-500">*</span></label>
                        <div v-for="(content, idx) in form.contents" :key="idx" class="mb-2">
                            <textarea v-model="form.contents[idx]"
                                class="w-full border border-gray-300 rounded-lg p-3 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none min-h-[80px]"
                                :placeholder="`Enter content section ${idx + 1}...`"
                                @input="capitalizeFirstLetterContent(idx)" required></textarea>
                            <div class="flex justify-between items-center mt-1">
                                <button type="button" @click="removeContent(idx)" v-if="form.contents.length > 1"
                                    class="text-red-500 hover:text-red-700 text-sm font-semibold focus:outline-none focus:underline">
                                    Remove Section
                                </button>
                                <span class="text-xs text-gray-500">Section {{ idx + 1 }}</span>
                            </div>
                        </div>
                        <button type="button" @click="addContent"
                            class="mt-2 text-blue-600 hover:text-blue-800 text-sm font-semibold focus:outline-none focus:underline">
                            + Add Content Section
                        </button>
                    </div>

                    <!-- Published Date -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Published Date<span
                                class="text-red-500">*</span></label>
                        <VueDatePicker v-model="form.published_at" model-type="format" format="yyyy-MM-dd"
                            :enable-time-picker="false" :clearable="true" :auto-apply="true" :teleport="true"
                            input-class-name="w-full border border-gray-300 rounded-lg p-3 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="Select date" />
                    </div>

                    <!-- Featured Image -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Featured Image</label>
                        <div v-if="form.currentFeaturedImage" class="mb-2">
                            <p class="text-sm text-gray-600 mb-1">Current Image:</p>
                            <img :src="`/news_image/${form.currentFeaturedImage.replace('news/', '')}`"
                                alt="Current Featured Image" class="h-24 w-32 object-cover rounded border" />
                        </div>
                        <input type="file" @change="onFileChange"
                            :class="['w-full border rounded-lg p-3 bg-white focus:outline-none', imageError ? 'border-red-500 focus:ring-2 focus:ring-red-500' : 'border-gray-300 focus:ring-2 focus:ring-blue-500']"
                            accept=".jpg,.jpeg,.png,.webp" />
                        <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image(Recommended image size: 1200x630px with 10MB)</p>
                        <p v-if="imageError" class="text-red-500 text-sm mt-1">{{ imageError }}</p>
                    </div>

                    <!-- Featured Image 2 (now single file) -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Article Image</label>
                        <div v-if="form.currentFeaturedImage2" class="mb-2">
                            <p class="text-sm text-gray-600 mb-1">Current Image:</p>
                            <img :src="`/news_image/${form.currentFeaturedImage2.replace('news/', '')}`"
                                alt="Current Featured Image 2" class="h-24 w-32 object-cover rounded border" />
                        </div>
                        <input type="file" @change="onFileChange2"
                            :class="['w-full border rounded-lg p-3 bg-white focus:outline-none', image2Error ? 'border-red-500 focus:ring-2 focus:ring-red-500' : 'border-gray-300 focus:ring-2 focus:ring-blue-500']"
                            accept=".jpg,.jpeg,.png,.webp" />
                        <div v-if="featuredImage2Preview" class="mt-2">
                            <img :src="featuredImage2Preview" alt="Preview"
                                class="w-24 h-24 object-cover rounded border" />
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image(Recommended image size: 1200x630px with 10MB)</p>
                        <p v-if="image2Error" class="text-red-500 text-sm mt-1">{{ image2Error }}</p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-8">
                    <button type="submit"
                        class="w-full bg-blue-600 text-white text-lg font-semibold py-3 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition disabled:opacity-80 disabled:cursor-not-allowed"
                        :disabled="form.processing">
                        <span v-if="form.processing">Updating...</span>
                        <span v-else>Update News Article</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, defineProps, defineEmits } from 'vue'
import { router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import VueDatePicker from '@vuepic/vue-datepicker'

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
