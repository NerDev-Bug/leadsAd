<script setup>
defineEmits(['edit', 'delete']);

defineProps({
    editTitle: { type: String, default: 'Edit' },
    deleteTitle: { type: String, default: 'Delete' },
});
</script>

<template>
    <div class="inline-flex items-center justify-center gap-1.5">
        <button
            type="button"
            class="admin-action-btn admin-action-btn-edit"
            :title="editTitle"
            @click="$emit('edit')"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <span class="hidden sm:inline">Edit</span>
        </button>
        <button
            type="button"
            class="admin-action-btn admin-action-btn-delete"
            :title="deleteTitle"
            @click="$emit('delete')"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <span class="hidden sm:inline">Delete</span>
        </button>
    </div>
</template>
