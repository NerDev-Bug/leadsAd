<template>
  <Modal :show="modelValue" @close="$emit('update:modelValue', false)">
    <div class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">Archived News</h2>
        <button @click="$emit('update:modelValue', false)" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>

      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <input v-model="search" type="text" placeholder="Search archived news..." class="w-full sm:w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <div class="text-sm text-gray-500" v-if="pagination.total">Total: {{ pagination.total }}</div>
      </div>

      <div v-if="loading" class="py-8 text-center text-gray-500">Loading...</div>
      <div v-else>
        <div v-if="!archived.length" class="py-8 text-center text-gray-500">No archived news found.</div>
        <ul class="divide-y">
          <li v-for="item in archived" :key="item.id" class="py-4 flex items-start gap-4">
            <img v-if="item.featured_image" :src="`/${item.featured_image}`" class="h-14 w-20 object-cover rounded" />
            <div class="flex-1">
              <div class="font-semibold" :title="item.title">{{ item.title }}</div>
              <div class="text-sm text-gray-600" :title="item.content">{{ truncate(item.content, 20) }}</div>
            </div>
            <div class="flex items-center gap-2">
              <button :disabled="retrievingId === item.id" @click="restore(item)" class="px-3 py-2 rounded bg-green-600 hover:bg-green-700 text-white disabled:opacity-50">
                <span v-if="retrievingId === item.id">Retrieving...</span>
                <span v-else>Retrieve</span>
              </button>
            </div>
          </li>
        </ul>
      </div>

      <div class="mt-4 flex justify-end">
        <button @click="$emit('update:modelValue', false)" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Close</button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import Modal from '@/Components/Modal.vue';
import Swal from 'sweetalert2';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue']);

const archived = ref([]);
const pagination = ref({ total: 0 });
const loading = ref(false);
const search = ref('');
const retrievingId = ref(null);
let t = null;

function truncate(text, max = 20) {
  if (!text) return '';
  return text.length > max ? text.slice(0, max) + '...' : text;
}

async function fetchArchived() {
  loading.value = true;
  try {
    const q = search.value ? `?search=${encodeURIComponent(search.value)}` : '';
    const res = await fetch(`/archive-news${q}`, { headers: { Accept: 'application/json' } });
    const data = await res.json();
    archived.value = data.data || [];
    pagination.value = data.pagination || { total: 0 };
  } finally {
    loading.value = false;
  }
}

function getCsrfToken() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
}

async function restore(item) {
  try {
    retrievingId.value = item.id;
    const res = await fetch(`/archive-news/${item.id}/restore`, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
    });
    if (!res.ok) throw new Error('Restore failed');

    // Close modal first so the SweetAlert appears above everything
    emit('update:modelValue', false);
    await nextTick();

    await Swal.fire({
      icon: 'success',
      title: 'Restored!',
      text: 'The news has been successfully restored.',
      confirmButtonText: 'OK',
      zIndex: 2000,
    });
    window.location.reload();
  } catch (e) {
    console.error(e);
    await Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to restore. Please try again.', zIndex: 2000 });
  } finally {
    retrievingId.value = null;
  }
}

watch(() => props.modelValue, (open) => {
  if (open) fetchArchived();
});

watch(search, () => {
  if (t) clearTimeout(t);
  t = setTimeout(fetchArchived, 500);
});
</script>
