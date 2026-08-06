<template>
    <div class="admin-rich-text-editor">
        <QuillEditor
            v-model:content="content"
            content-type="html"
            theme="snow"
            :placeholder="placeholder"
            :toolbar="toolbar"
            :style="{
                '--editor-min-height': minHeight,
                '--editor-max-height': maxHeight,
            }"
        />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    minHeight: { type: String, default: '140px' },
    maxHeight: { type: String, default: '280px' },
});

const emit = defineEmits(['update:modelValue']);

const toolbar = [
    ['bold', 'italic', 'underline', 'strike'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    [{ align: [] }],
    ['clean'],
];

const content = computed({
    get: () => props.modelValue,
    set: (value) => {
        const normalized = !value || value === '<p><br></p>' ? '' : value;
        emit('update:modelValue', normalized);
    },
});
</script>
