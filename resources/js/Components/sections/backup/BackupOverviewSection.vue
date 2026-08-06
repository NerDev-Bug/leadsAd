<template>
    <main>
        <div class="mx-auto max-w-7xl rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-50">
                    <DatabaseBackup class="h-6 w-6 text-green-600" />
                </div>
                <div>

                    <h2 class="text-xl font-bold text-gray-900">
                        Backup Overview
                    </h2>
                    <p class="mt-1 text-xs text-gray-500">
                        Manage your system backups and restore data when needed.
                    </p>
                </div>
            </div>
            <div class="my-8"></div>
            <div class="grid grid-cols-1 lg:grid-cols-3 px-4">
                <div class="flex items-center gap-4 lg:border-r lg:border-gray-200 lg:pr-6">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-green-50">
                        <CalendarDays class="h-6 w-6 text-green-600" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">
                            Last Backup
                        </p>
                        <p class="mt-1 text-base font-bold text-green-600">
                            {{ lastBackupLabel }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ lastBackupMeta }}
                        </p>
                    </div>
                </div>
                <div class="mt-5 flex items-center gap-4 lg:mt-0 lg:border-r lg:border-gray-200 lg:px-6">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-green-50">
                        <Clock3 class="h-6 w-6 text-green-600" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">
                            Next Scheduled Backup
                        </p>
                        <p class="mt-1 text-base font-bold text-green-600">
                            —
                        </p>
                        <p class="text-xs text-gray-500">
                            Automatic scheduling not enabled
                        </p>
                    </div>
                </div>
                <div class="mt-5 flex items-center gap-4 lg:mt-0 lg:pl-6">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-green-50">
                        <Folder class="h-6 w-6 text-green-600" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">
                            Total Backups
                        </p>
                        <p class="mt-1 text-base font-bold text-green-600">
                            {{ overview.total }}
                        </p>
                        <p class="text-xs text-gray-500">
                            Stored all the backups files.
                        </p>
                    </div>
                </div>
            </div>
            <div class="my-5 border-b border-gray-200"></div>
            <div class="grid grid-cols-1 lg:grid-cols-2">
                <div class="lg:border-r lg:border-gray-200 lg:pr-6">
                    <div class="flex items-center justify-between py-4">
                        <div class="max-w-sm">
                            <h3 class="text-base font-semibold text-gray-900">
                                Create Backup Now
                            </h3>
                            <p class="mt-1 text-xs text-gray-500">
                                Manually create a database backup (.sql).
                            </p>
                        </div>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl border border-green-500 px-4 py-2 text-xs font-semibold text-green-600 transition hover:bg-green-50 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="creating"
                            @click="handleCreate"
                        >
                            <PlusCircle class="h-4 w-4" />
                            {{ creating ? 'Creating...' : 'Create Backup' }}
                        </button>
                    </div>
                    <div class="border-b border-gray-200"></div>
                    <div class="flex items-center justify-between py-4">
                        <div class="max-w-sm">
                            <h3 class="text-base font-semibold text-gray-900">
                                Download Latest Backup
                            </h3>
                            <p class="mt-1 text-xs text-gray-500">
                                Download the most recent backup file.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl border border-green-500 px-4 py-2 text-xs font-semibold text-green-600 transition hover:bg-green-50 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="!overview.last_backup"
                            @click="handleDownloadLatest"
                        >
                            <Download class="h-4 w-4" />
                            Download
                        </button>
                    </div>
                </div>
                <div class="mt-5 lg:mt-0 lg:pl-6">
                    <div class="flex items-center justify-between py-4 opacity-60">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">
                                Auto Backup
                            </h3>
                            <p class="mt-1 text-xs text-gray-500">
                                Coming soon — use Create Backup for now.
                            </p>
                        </div>
                        <label class="relative inline-flex cursor-not-allowed items-center">
                            <input type="checkbox" disabled class="peer sr-only" />
                            <div class="relative h-6 w-11 rounded-full bg-gray-300 after:absolute after:left-1 after:top-1 after:h-4 after:w-4 after:rounded-full after:bg-white" />
                        </label>
                    </div>
                    <div class="flex items-center justify-between py-4 opacity-60">
                        <div class="max-w-sm">
                            <h3 class="text-base font-semibold text-gray-900">
                                Backup Frequency
                            </h3>
                            <p class="mt-1 text-xs text-gray-500">
                                Available when auto backup is enabled.
                            </p>
                        </div>
                        <select
                            disabled
                            class="h-10 w-36 cursor-not-allowed rounded-xl border border-gray-300 px-4 text-xs text-gray-700"
                        >
                            <option>Weekly</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import Swal from 'sweetalert2';
import {
    DatabaseBackup,
    CalendarDays,
    Clock3,
    Folder,
    PlusCircle,
    Download,
} from '@lucide/vue';
import { useDatabaseBackups } from '@/composables/useDatabaseBackups';

const {
    overview,
    creating,
    fetchBackups,
    createBackup,
    downloadBackup,
} = useDatabaseBackups();
const lastBackupLabel = computed(() => overview.value.last_backup?.date_label ?? 'No backups yet');
const lastBackupMeta = computed(() => {
    const last = overview.value.last_backup;
    if (!last) return 'Create your first backup below';
    return `By ${last.created_by} (${last.type})`;
});

async function handleCreate() {
    try {
        await createBackup();
        await Swal.fire({
            icon: 'success',
            title: 'Backup created',
            text: 'The database backup was saved to storage/app/backups.',
            confirmButtonColor: '#057A31',
        });
    } catch (e) {
        console.error(e);
        await Swal.fire({
            icon: 'error',
            title: 'Backup failed',
            text: e.message || 'Could not create the database backup.',
        });
    }
}

function handleDownloadLatest() {
    const file = overview.value.last_backup?.filename;
    if (file) downloadBackup(file);
}

onMounted(() => {
    fetchBackups();
});
</script>
