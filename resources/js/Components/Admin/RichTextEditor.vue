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
            @ready="onReady"
        />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import Swal from 'sweetalert2';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    minHeight: { type: String, default: '140px' },
    maxHeight: { type: String, default: '280px' },
});

const emit = defineEmits(['update:modelValue']);

/**
 * Opens a SweetAlert2 prompt to insert, edit, or remove a link.
 * This replaces Quill's default snow tooltip, which overlaps the editor.
 */
async function openLinkEditor(quill) {
    const range = quill.getSelection();
    if (!range) return;

    // Detect an existing link under the cursor/selection.
    let targetRange = null;
    let currentUrl = '';

    try {
        const LinkBlot = quill.constructor.import('formats/link');
        const [link, offset] = quill.scroll.descendant(LinkBlot, range.index) || [];
        if (link) {
            targetRange = { index: range.index - offset, length: link.length() };
            currentUrl = LinkBlot.formats(link.domNode) || '';
        }
    } catch {
        currentUrl = (quill.getFormat(range) || {}).link || '';
    }

    // Inserting a new link requires a text selection.
    if (!targetRange && range.length === 0) {
        Swal.fire({
            title: 'Select text first',
            text: 'Please select the text you want to turn into a link.',
            icon: 'info',
            confirmButtonColor: '#3085d6',
        });
        return;
    }

    if (!targetRange) targetRange = range;

    const result = await Swal.fire({
        title: currentUrl ? 'Edit Link' : 'Insert Link',
        input: 'url',
        inputLabel: 'URL',
        inputValue: currentUrl,
        inputPlaceholder: 'https://example.com/page',
        inputAttributes: {
            autocapitalize: 'off',
            autocorrect: 'off',
            spellcheck: 'false',
        },
        showCancelButton: true,
        confirmButtonText: 'Save',
        cancelButtonText: 'Cancel',
        showDenyButton: !!currentUrl,
        denyButtonText: 'Remove link',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        denyButtonColor: '#d33',
        inputValidator: (val) => {
            if (!val || !val.trim()) return 'Please enter a URL.';
            return null;
        },
    });

    if (result.isDenied) {
        quill.formatText(targetRange, 'link', false, 'user');
        quill.setSelection(targetRange.index + targetRange.length, 0);
        return;
    }

    if (!result.isConfirmed || !result.value) return;

    let url = result.value.trim();
    // Auto-prepend https:// for bare domains so the link stays valid.
    if (!/^(https?:\/\/|mailto:|tel:|\/|#)/i.test(url)) {
        url = `https://${url}`;
    }

    quill.formatText(targetRange, 'link', url, 'user');
    quill.setSelection(targetRange.index + targetRange.length, 0);
}

function onReady(quill) {
    // Let users edit/remove a link by clicking it, without the overlapping tooltip.
    quill.root.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element) || !target.closest('a')) return;
        event.preventDefault();
        event.stopPropagation();
        openLinkEditor(quill);
    });
}

const toolbar = {
    container: [
        ['bold', 'italic', 'underline', 'strike'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        [{ align: [] }],
        ['link'],
        ['clean'],
    ],
    handlers: {
        link() {
            openLinkEditor(this.quill);
        },
    },
};

const content = computed({
    get: () => props.modelValue,
    set: (value) => {
        const normalized = !value || value === '<p><br></p>' ? '' : value;
        emit('update:modelValue', normalized);
    },
});
</script>
