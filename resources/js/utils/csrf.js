export function getMetaCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');

    return meta?.getAttribute('content') ?? '';
}

export function getXsrfToken() {
    const cookieMatch = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    if (cookieMatch) {
        return decodeURIComponent(cookieMatch[1]);
    }

    return '';
}

export function getCsrfToken() {
    return getMetaCsrfToken() || getXsrfToken();
}

export function csrfHeaders() {
    const headers = {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
    };

    const xsrf = getXsrfToken();
    if (xsrf) {
        headers['X-XSRF-TOKEN'] = xsrf;
        return headers;
    }

    const meta = getMetaCsrfToken();
    if (meta) {
        headers['X-CSRF-TOKEN'] = meta;
    }

    return headers;
}

export function appendCsrfToFormData(formData) {
    const token = getMetaCsrfToken() || getXsrfToken();

    if (token) {
        formData.append('_token', token);
    }

    return formData;
}
