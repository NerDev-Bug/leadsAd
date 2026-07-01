<template>
    <AdminModal
        :model-value="modelValue"
        title="Archived News"
        subtitle="Browse and restore archived articles"
        icon="archive"
        size="xl"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <div class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <input v-model="search" type="text" placeholder="Search archived news..." class="admin-input sm:w-72" />
                <span v-if="pagination.total" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                    Total: {{ pagination.total }}
                </span>
            </div>

            <div v-if="loading" class="flex flex-col items-center py-12">
                <div class="h-8 w-8 animate-spin rounded-full border-2 border-brand-600 border-t-transparent" />
                <p class="mt-3 text-sm text-slate-500">Loading archived news...</p>
            </div>

            <div v-else-if="!archived.length" class="admin-table-empty rounded-xl py-12 text-center">
                <p class="font-semibold text-slate-700">No archived news found</p>
                <p class="mt-1 text-sm text-slate-400">Archived articles will appear here</p>
            </div>

            <ul v-else class="divide-y divide-slate-100 rounded-xl border border-slate-200/80 overflow-hidden">
                <li v-for="item in archived" :key="item.id" class="flex items-start gap-4 bg-white p-4 transition hover:bg-slate-50/80">
                    <img v-if="item.featured_image" :src="`/${item.featured_image}`" class="h-14 w-20 rounded-xl object-cover ring-2 ring-slate-100" />
                    <div v-else class="admin-table-image-placeholder h-14 w-20">No Image</div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-slate-800 truncate" :title="item.title">{{ item.title }}</p>
                        <p class="text-sm text-slate-500 truncate" :title="item.content">{{ truncate(item.content, 60) }}</p>
                    </div>
                    <button
                        :disabled="retrievingId === item.id"
                        class="admin-modal-btn-success shrink-0 px-4"
                        @click="restore(item)"
                    >
                        {{ retrievingId === item.id ? 'Restoring...' : 'Retrieve' }}
                    </button>
                </li>
            </ul>
        </div>
    </AdminModal>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import Swal from 'sweetalert2';
import AdminModal from '@/Components/Admin/AdminModal.vue';

const props = defineProps({ modelValue: { type: Boolean, default: false } });
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
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
        });
        if (!res.ok) throw new Error('Restore failed');

        emit('update:modelValue', false);
        await nextTick();

        await Swal.fire({
            icon: 'success',
            title: 'Restored!',
            text: 'The news has been successfully restored.',
            confirmButtonColor: '#057A31',
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

watch(() => props.modelValue, (open) => { if (open) fetchArchived(); });
watch(search, () => { if (t) clearTimeout(t); t = setTimeout(fetchArchived, 500); });
</script>
