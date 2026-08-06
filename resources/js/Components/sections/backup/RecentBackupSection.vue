<template>
    <main>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

            <h2 class="mb-5 text-lg font-bold text-gray-900">
                Recent Backups
            </h2>

            <div class="overflow-x-auto rounded-xl border border-gray-200">

                <table class="min-w-full">

                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold text-gray-500">
                            <th class="px-4 py-3">Date & Time</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Size</th>
                            <th class="px-4 py-3">Created By</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-if="loading">
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                                Loading backups...
                            </td>
                        </tr>

                        <tr v-else-if="!displayedBackups.length">
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                                No backups yet. Create one from Backup Overview.
                            </td>
                        </tr>

                        <tr
                            v-for="backup in displayedBackups"
                            :key="backup.filename"
                            class="border-t border-gray-200 hover:bg-gray-50"
                        >
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ backup.date_label }}
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ backup.type }}
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ backup.size_label }}
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ backup.created_by }}
                            </td>

                            <td class="px-4 py-2">
                                <div class="flex justify-center gap-3">
                                    <button
                                        type="button"
                                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-green-300 text-green-600 transition hover:bg-green-50"
                                        title="Download"
                                        @click="downloadBackup(backup.filename)"
                                    >
                                        <Download class="h-4 w-4" />
                                    </button>

                                    <button
                                        type="button"
                                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-red-300 text-red-500 transition hover:bg-red-50 disabled:opacity-50"
                                        title="Delete"
                                        :disabled="deletingFilename === backup.filename"
                                        @click="handleDelete(backup)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>

                </table>

            </div>

            <div v-if="backups.length > defaultLimit" class="mt-5">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl border border-green-300 px-5 py-2.5 text-sm font-semibold text-green-600 transition hover:bg-green-50"
                    @click="showAll = !showAll"
                >
                    <List class="h-4 w-4" />
                    {{ showAll ? 'Show fewer' : `View All Backups (${backups.length})` }}
                </button>
            </div>

        </div>
    </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import Swal from 'sweetalert2';
import { Download, Trash2, List } from '@lucide/vue';
import { useDatabaseBackups } from '@/composables/useDatabaseBackups';

const defaultLimit = 5;
const showAll = ref(false);
const deletingFilename = ref(null);

const {
    backups,
    loading,
    fetchBackups,
    downloadBackup,
    deleteBackup,
} = useDatabaseBackups();

const displayedBackups = computed(() => {
    if (showAll.value) return backups.value;
    return backups.value.slice(0, defaultLimit);
});

async function handleDelete(backup) {
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Delete this backup?',
        text: `${backup.filename} will be removed permanently.`,
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#dc2626',
        cancelButtonText: 'Cancel',
    });

    if (!result.isConfirmed) return;

    deletingFilename.value = backup.filename;
    try {
        await deleteBackup(backup.filename);
        await Swal.fire({
            icon: 'success',
            title: 'Deleted',
            confirmButtonColor: '#057A31',
        });
    } catch (e) {
        console.error(e);
        await Swal.fire({ icon: 'error', title: 'Error', text: e.message || 'Could not delete backup.' });
    } finally {
        deletingFilename.value = null;
    }
}

onMounted(() => {
    fetchBackups();
});
</script>
