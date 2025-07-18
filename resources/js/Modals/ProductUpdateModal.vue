<template>
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div
            class="relative w-full max-w-lg mx-2 sm:mx-4 md:mx-0 bg-white rounded-2xl shadow-2xl p-4 sm:p-6 md:p-8 max-h-[90vh] flex flex-col overflow-y-auto"
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
            <h2 class="text-2xl sm:text-3xl font-bold text-center mb-6 text-gray-800">Update Product</h2>
            <!-- Product Form -->
            <form @submit.prevent="submitForm" class="flex-1 flex flex-col justify-between">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Description<span class="text-red-500">*</span></label>
                        <textarea v-model="form.description" @input="handleInput('description')"
                            class="w-full border border-gray-300 rounded-lg p-2 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none min-h-[60px]"
                            required></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Features & Benefits<span class="text-red-500">*</span></label>
                        <textarea v-model="form.features" @input="handleInput('features')"
                            class="w-full border border-gray-300 rounded-lg p-2 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none min-h-[60px]"
                            required></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Dosage<span class="text-red-500">*</span></label>
                        <div v-for="(dosage, idx) in form.dosages" :key="idx" class="flex items-center mb-2 gap-2">
                            <input v-model="form.dosages[idx]" @input="handleArrayInput('dosages', idx)" type="text"
                                class="flex-1 border border-gray-300 rounded-lg p-2 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                required />
                            <button type="button" @click="removeDosage(idx)" v-if="form.dosages.length > 1" class="text-red-500 hover:text-red-700 text-xl px-2 py-1 rounded-full focus:outline-none focus:ring-2 focus:ring-red-400">&minus;</button>
                        </div>
                        <button type="button" @click="addDosage" class="mt-1 text-blue-600 hover:text-blue-800 text-sm font-semibold focus:outline-none focus:underline">+ Add Dosage</button>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Target<span class="text-red-500">*</span></label>
                        <div v-for="(target, idx) in form.targets" :key="idx" class="flex items-center mb-2 gap-2">
                            <input v-model="form.targets[idx]" @input="handleArrayInput('targets', idx)" type="text"
                                class="flex-1 border border-gray-300 rounded-lg p-2 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                required />
                            <button type="button" @click="removeTarget(idx)" v-if="form.targets.length > 1" class="text-red-500 hover:text-red-700 text-xl px-2 py-1 rounded-full focus:outline-none focus:ring-2 focus:ring-red-400">&minus;</button>
                        </div>
                        <button type="button" @click="addTarget" class="mt-1 text-blue-600 hover:text-blue-800 text-sm font-semibold focus:outline-none focus:underline">+ Add Target</button>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Category<span class="text-red-500">*</span></label>
                        <select v-model="form.category" required class="w-full border border-gray-400 rounded-lg p-3 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="" disabled>— Please choose a category —</option>
                            <option value="Rice">Rice</option>
                            <option value="Mango">Mango</option>
                            <option value="Vegetables">Vegetables</option>
                            <option value="Sugarcane">Sugarcane</option>
                            <option value="Other Crops">Other Crops</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Type<span class="text-red-500">*</span></label>
                        <select v-model="form.type" required
                            class="w-full border border-gray-400 rounded-lg p-3 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="" disabled>— Please choose a type —</option>
                            <option value="Herbicide">Herbicide</option>
                            <option value="Fungicide">Fungicide</option>
                            <option value="Biostimulant">Biostimulant</option>
                            <option value="Insecticide">Insecticide</option>
                            <option value="Mollusicide">Mollusicide</option>
                        </select>
                    </div>

                    <!-- Image 1 Section -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Image 1 (Logo)</label>
                        <div v-if="form.currentImage1" class="mb-2">
                            <p class="text-sm text-gray-600 mb-1">Current Image:</p>
                            <img :src="`/products_image/${form.currentImage1.replace('products/', '')}`" alt="Current Image 1" class="h-16 w-16 object-cover rounded border" />
                        </div>
                        <input type="file" @change="onFileChange($event, 1)"
                            :class="['w-full border rounded-lg p-2 bg-white focus:outline-none', image1Error ? 'border-red-500 focus:ring-2 focus:ring-red-500' : 'border-gray-300 focus:ring-2 focus:ring-blue-500']"
                            accept=".jpg,.jpeg,.png" />
                        <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image</p>
                        <p v-if="image1Error" class="text-red-500 text-sm mt-1">{{ image1Error }}</p>
                    </div>

                    <!-- Image 2 Section -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Image 2 (Name)</label>
                        <div v-if="form.currentImage2" class="mb-2">
                            <p class="text-sm text-gray-600 mb-1">Current Image:</p>
                            <img :src="`/products_image/${form.currentImage2.replace('products/', '')}`" alt="Current Image 2" class="h-16 w-16 object-cover rounded border" />
                        </div>
                        <input type="file" @change="onFileChange($event, 2)"
                            :class="['w-full border rounded-lg p-2 bg-white focus:outline-none', image2Error ? 'border-red-500 focus:ring-2 focus:ring-red-500' : 'border-gray-300 focus:ring-2 focus:ring-blue-500']"
                            accept=".jpg,.jpeg,.png" />
                        <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image</p>
                        <p v-if="image2Error" class="text-red-500 text-sm mt-1">{{ image2Error }}</p>
                    </div>
                </div>
                <div class="mt-8">
                    <button type="submit"
                        class="w-full bg-blue-600 text-white text-lg font-semibold py-3 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition disabled:opacity-80 disabled:cursor-not-allowed"
                        :disabled="form.processing">
                        <span v-if="form.processing">Updating...</span>
                        <span v-else>Update Product</span>
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
    product: {
        type: Object,
        default: null
    }
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
    currentImage1: '',
    currentImage2: '',
    processing: false
});

// Error messages for file inputs
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
        currentImage1: '',
        currentImage2: '',
        processing: false
    };
    image1Error.value = '';
    image2Error.value = '';
};

const populateForm = (product) => {
    if (!product) return;

    form.value = {
        type: product.type || '',
        description: product.description || '',
        features: product.features || '',
        dosages: product.dosage ? product.dosage.split(',').map(d => d.trim()) : [''],
        targets: product.target ? product.target.split(',').map(t => t.trim()) : [''],
        category: product.category || '',
        image1: null,
        image2: null,
        currentImage1: product.image1 || '',
        currentImage2: product.image2 || '',
        processing: false
    };
};

// Watch for product changes and populate form
watch(() => props.product, (newProduct) => {
    if (newProduct) {
        populateForm(newProduct);
    }
}, { immediate: true });

// Watch for modal state changes
watch(() => props.modelValue, (val) => {
    if (!val) {
        resetForm();
    } else if (props.product) {
        populateForm(props.product);
    }
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

const addTarget = () => {
    form.value.targets.push('');
};

const removeTarget = (idx) => {
    if (form.value.targets.length > 1) form.value.targets.splice(idx, 1);
};

const addDosage = () => {
    form.value.dosages.push('');
};

const removeDosage = (idx) => {
    if (form.value.dosages.length > 1) form.value.dosages.splice(idx, 1);
};

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
    if (!props.product || !props.product.id) {
        Swal.fire({
            title: 'Error!',
            text: 'No product selected for update.',
            icon: 'error',
            confirmButtonColor: '#d33',
        });
        return;
    }

    form.value.processing = true;

    // Capitalize first letter of relevant fields
    form.value.description = capitalizeFirstLetter(form.value.description);
    form.value.features = capitalizeFirstLetter(form.value.features);
    form.value.dosages = form.value.dosages.map(capitalizeFirstLetter);
    form.value.targets = form.value.targets.map(capitalizeFirstLetter);

    const formData = new FormData();
    formData.append('_method', 'PUT'); // Laravel method spoofing for PUT request
    formData.append('type', form.value.type);
    formData.append('description', form.value.description);
    formData.append('features', form.value.features);
    formData.append('dosage', form.value.dosages.join(', '));
    formData.append('target', form.value.targets.join(', '));
    formData.append('category', form.value.category);

    if (form.value.image1) formData.append('image1', form.value.image1);
    if (form.value.image2) formData.append('image2', form.value.image2);

    router.post(`/products/${props.product.id}`, formData, {
        forceFormData: true,
        onSuccess: () => {
            emit('update:modelValue', false);
            resetForm();
            Swal.fire({
                title: 'Success!',
                text: 'Product updated successfully!',
                icon: 'success',
                confirmButtonColor: '#3085d6',
            }).then(() => {
                router.visit('/products');
            });
        },
        onError: (errors) => {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to update product. Please check your input.',
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
