export function isQualificationsEmpty(html) {
    if (!html) return true;
    return html.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim() === '';
}

export function qualificationsToHtml(value) {
    if (!value) return '';

    if (/<[a-z][\s\S]*>/i.test(value)) {
        return value;
    }

    let items = [];

    if (value.includes('\n')) {
        items = value.split('\n').map((line) => line.trim()).filter(Boolean);
    } else if (value.includes(', ')) {
        items = value.split(', ').map((line) => line.trim()).filter(Boolean);
    } else if (value.includes(': ')) {
        items = value.split(': ').map((line) => line.trim()).filter(Boolean);
    } else if (value.includes(',')) {
        items = value.split(',').map((line) => line.trim()).filter(Boolean);
    } else {
        items = [value.trim()];
    }

    if (items.length === 0) return '';

    return `<ul>${items.map((item) => `<li>${item}</li>`).join('')}</ul>`;
}

export function normalizeQualifications(html) {
    return isQualificationsEmpty(html) ? '' : html.trim();
}
