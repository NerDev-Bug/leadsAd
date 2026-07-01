<script setup>
defineProps({
    from: { type: Number, default: 0 },
    to: { type: Number, default: 0 },
    total: { type: Number, default: 0 },
    currentPage: { type: Number, default: 1 },
    lastPage: { type: Number, default: 1 },
    label: { type: String, default: 'items' },
});
</script>

<template>
    <div class="admin-table-footer">
        <div class="flex items-center gap-2 text-sm text-slate-600">
            <span class="inline-flex h-2 w-2 rounded-full bg-brand-500" />
            Showing <span class="font-semibold text-slate-800">{{ from }}</span> to
            <span class="font-semibold text-slate-800">{{ to }}</span> of
            <span class="font-semibold text-slate-800">{{ total }}</span> {{ label }}
        </div>
        <div class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600">
            Page {{ currentPage }} of {{ lastPage }}
        </div>
    </div>
</template>
