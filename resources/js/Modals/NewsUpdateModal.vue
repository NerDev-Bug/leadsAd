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
            <h2 class="text-2xl sm:text-3xl font-bold text-center mb-6 text-gray-800">Update News Article</h2>
            <!-- News Form -->
            <form @submit.prevent="submitForm" class="flex-1 flex flex-col justify-between">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Title<span class="text-red-500">*</span></label>
                        <input v-model="form.title" type="text"
                            class="w-full border border-gray-300 rounded-lg p-3 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="Enter news title"
                            @input="capitalizeFirstLetter('title')"
                            required />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Content<span class="text-red-500">*</span></label>
                        <div v-for="(content, idx) in form.contents" :key="idx" class="mb-2">
                            <textarea v-model="form.contents[idx]"
                                class="w-full border border-gray-300 rounded-lg p-3 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none min-h-[80px]"
                                :placeholder="`Enter content section ${idx + 1}...`"
                                @input="capitalizeFirstLetterContent(idx)"
                                required></textarea>
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
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Published Date<span class="text-red-500">*</span></label>
                        <input v-model="form.published_at" type="date"
                            class="w-full border border-gray-300 rounded-lg p-3 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            required />
                    </div>

                    <!-- Featured Image Section -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Featured Image</label>
                        <div v-if="form.currentFeaturedImage" class="mb-2">
                            <p class="text-sm text-gray-600 mb-1">Current Image:</p>
                            <img :src="`/news_image/${form.currentFeaturedImage.replace('news/', '')}`" alt="Current Featured Image" class="h-24 w-32 object-cover rounded border" />
                        </div>
                        <input type="file" @change="onFileChange"
                            :class="['w-full border rounded-lg p-3 bg-white focus:outline-none', imageError ? 'border-red-500 focus:ring-2 focus:ring-red-500' : 'border-gray-300 focus:ring-2 focus:ring-blue-500']"
                            accept=".jpg,.jpeg,.png,.webp" />
                        <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image</p>
                        <p v-if="imageError" class="text-red-500 text-sm mt-1">{{ imageError }}</p>
                    </div>

                    <!-- Featured Image 2 Section -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Featured Image 2</label>
                        <div v-if="form.currentFeaturedImage2" class="flex flex-wrap gap-2 mb-2">
                            <div v-for="(img, idx) in form.currentFeaturedImage2.split(',')" :key="idx" class="w-16 h-16 bg-gray-100 rounded overflow-hidden flex items-center justify-center border">
                                <img v-if="img.trim()" :src="`/news_image/${img.trim().replace('news/', '')}`" alt="Current Featured Image 2" class="object-cover w-full h-full" />
                            </div>
                        </div>
                        <input type="file" @change="onFileChange2" multiple
                            :class="['w-full border rounded-lg p-3 bg-white focus:outline-none', image2Error ? 'border-red-500 focus:ring-2 focus:ring-red-500' : 'border-gray-300 focus:ring-2 focus:ring-blue-500']"
                            accept=".jpg,.jpeg,.png,.webp" />
                        <div class="flex flex-wrap gap-2 mt-2">
                            <div v-for="(img, idx) in featuredImages2Preview" :key="'new-'+idx" class="w-16 h-16 bg-gray-100 rounded overflow-hidden flex items-center justify-center border">
                                <img v-if="img" :src="img" class="object-cover w-full h-full" />
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Leave empty to keep current images. You can select multiple images to replace all.</p>
                        <p v-if="image2Error" class="text-red-500 text-sm mt-1">{{ image2Error }}</p>
                    </div>
                </div>
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
import { ref, watch, defineProps, defineEmits } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    modelValue: Boolean,
    news: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['update:modelValue', 'submitted']);

const form = ref({
    title: '',
    contents: [''],
    published_at: '',
    featured_image: null,
    currentFeaturedImage: '',
    featured_image_2: [],
    currentFeaturedImage2: '',
    processing: false
});

// Error messages for file input
const imageError = ref('');
const image2Error = ref('');
const featuredImages2Preview = ref([]);

const resetForm = () => {
    form.value = {
        title: '',
        contents: [''],
        published_at: '',
        featured_image: null,
        currentFeaturedImage: '',
        featured_image_2: [],
        currentFeaturedImage2: '',
        processing: false
    };
    imageError.value = '';
    image2Error.value = '';
    featuredImages2Preview.value = [];
};

const populateForm = (newsItem) => {
    if (!newsItem) return;

    // Parse existing content by colons and trim whitespace
    let contents = [''];
    if (newsItem.content) {
        contents = newsItem.content.split(': ').map(content => content.trim()).filter(content => content !== '');
    }

    // If no content sections found, start with one empty section
    if (contents.length === 0) {
        contents = [''];
    }

    form.value = {
        title: newsItem.title || '',
        contents: contents,
        published_at: newsItem.published_at ? new Date(newsItem.published_at).toISOString().slice(0, 10) : '',
        featured_image: null,
        currentFeaturedImage: newsItem.featured_image || '',
        featured_image_2: newsItem.featured_image_2 ? newsItem.featured_image_2.split(',') : [],
        currentFeaturedImage2: newsItem.featured_image_2 || '',
        processing: false
    };
};

// Watch for news changes and populate form
watch(() => props.news, (newNews) => {
    if (newNews) {
        populateForm(newNews);
    }
}, { immediate: true });

// Watch for modal state changes
watch(() => props.modelValue, (val) => {
    if (!val) {
        resetForm();
    } else if (props.news) {
        populateForm(props.news);
    }
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
    const maxSize = 5 * 1024 * 1024; // 5MB

    if (file) {
        if (!allowedTypes.includes(file.type)) {
            imageError.value = 'Only JPEG, JPG, PNG, or WebP files are allowed.';
            form.value.featured_image = null;
            event.target.value = '';
            return;
        }

        if (file.size > maxSize) {
            imageError.value = 'File size must be less than 5MB.';
            form.value.featured_image = null;
            event.target.value = '';
            return;
        }

        form.value.featured_image = file;
        imageError.value = '';
    }
}

function onFileChange2(event) {
    const files = Array.from(event.target.files);
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    const maxSize = 5 * 1024 * 1024; // 5MB
    let error = '';
    let previews = [];
    const validFiles = [];
    for (const file of files) {
        if (!allowedTypes.includes(file.type)) {
            error = 'Only JPEG, JPG, PNG, or WebP files are allowed.';
            continue;
        }
        if (file.size > maxSize) {
            error = 'File size must be less than 5MB.';
            continue;
        }
        validFiles.push(file);
        previews.push(URL.createObjectURL(file));
    }
    form.value.featured_image_2 = validFiles;
    featuredImages2Preview.value = previews;
    image2Error.value = error;
}

async function submitForm() {
    if (!props.news || !props.news.id) {
        Swal.fire({
            title: 'Error!',
            text: 'No news article selected for update.',
            icon: 'error',
            confirmButtonColor: '#d33',
        });
        return;
    }

    form.value.processing = true;

    // Combine content sections with colons
    const combinedContent = form.value.contents.filter(content => content.trim() !== '').join(': ');

    const formData = new FormData();
    formData.append('_method', 'PUT'); // Laravel method spoofing for PUT request
    formData.append('title', form.value.title);
    formData.append('content', combinedContent);
    formData.append('published_at', form.value.published_at);
    if (form.value.featured_image) formData.append('featured_image', form.value.featured_image);
    // Append all featured_image_2 files and store their names as comma-separated string
    let image2Names = [];
    if (form.value.featured_image_2 && form.value.featured_image_2.length) {
        form.value.featured_image_2.forEach((file, idx) => {
            formData.append('featured_image_2[]', file);
            image2Names.push(file.name);
        });
        formData.append('featured_image_2_names', image2Names.join(','));
    }

    router.post(`/news/${props.news.id}`, formData, {
        forceFormData: true,
        onSuccess: () => {
            emit('update:modelValue', false);
            resetForm();
            Swal.fire({
                title: 'Success!',
                text: 'News article updated successfully!',
                icon: 'success',
                confirmButtonColor: '#3085d6',
            }).then(() => {
                router.visit('/news');
            });
        },
        onError: (errors) => {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to update news article. Please check your input.',
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
