// Inline validation for the student profile and application forms.
let nextId = 0;
const messages = new WeakMap();
const inStudentForm = field => field.form && field.closest('[data-student-fields]');

function visibleControl(field) {
    if (field.getClientRects().length) return field;
    return field.parentElement.querySelector('button, [tabindex="0"]') ?? field;
}

function showFieldError(field, text) {
    let message = messages.get(field);
    if (!message && !text) return;
    if (!message) {
        message = document.createElement('p');
        message.id = 'student-field-error-' + (++nextId);
        message.className = 'mt-1 text-sm text-red-600';
        message.setAttribute('aria-live', 'polite');
        const anchor = field.type === 'file' && !field.getClientRects().length
            ? field.parentElement : visibleControl(field);
        anchor.after(message);
        messages.set(field, message);
        for (const control of new Set([field, visibleControl(field)])) {
            const ids = [control.getAttribute('aria-describedby'), message.id].filter(Boolean);
            control.setAttribute('aria-describedby', ids.join(' '));
        }
    }
    message.hidden = !text;
    message.textContent = text;
    for (const control of new Set([field, visibleControl(field)])) {
        if (text) control.setAttribute('aria-invalid', 'true');
        else control.removeAttribute('aria-invalid');
    }
}

function focusField(field) {
    const control = visibleControl(field);
    control.focus({ preventScroll: true });
    control.scrollIntoView({ block: 'center' });
}

function restoreErrors() {
    for (const summary of document.querySelectorAll('[data-field-summary]')) {
        const root = summary.closest('[data-student-fields]');
        const fields = [...root.querySelectorAll('form input, form select, form textarea')];
        let first = null;
        for (const item of summary.querySelectorAll('[data-field-message]')) {
            const name = item.dataset.fieldMessage;
            const field = fields.find(el => el.name === name && visibleControl(el).getClientRects().length);
            if (!field) continue;
            showFieldError(field, item.textContent.trim());
            field.form.querySelectorAll('[data-inline-for="' + CSS.escape(name) + '"]').forEach(el => el.hidden = true);
            item.remove();
            if (!first || fields.indexOf(field) < fields.indexOf(first)) first = field;
        }
        summary.hidden = !summary.querySelector('[data-field-message]');
        if (first) focusField(first);
    }
}

const formats = {
    pdf: 'application/pdf', jpg: 'image/jpeg', jpeg: 'image/jpeg', png: 'image/png',
};

function formatProblem(input, file) {
    const allowed = input.accept.toLowerCase().split(',').map(type => type.trim()).filter(Boolean);
    if (!allowed.length) return '';
    const ext = file.name.split('.').pop().toLowerCase();
    const mime = formats[ext];
    if (mime && (allowed.includes('.' + ext) || allowed.includes(mime))
        && (!file.type || file.type === mime || file.type === 'application/octet-stream')) return '';
    return 'Format berkas tidak sesuai. Pilih ' + allowed.join(', ') + '.';
}

export function registerStudentValidation(sizeProblem) {
    document.addEventListener('change', event => {
        const field = event.target;
        if (!inStudentForm(field) || field.type !== 'file' || !field.files.length) return;
        const problem = sizeProblem(field) || formatProblem(field, field.files[0]);
        field.setCustomValidity(problem);
        showFieldError(field, problem);
        if (problem) field.value = '';
    }, true);
    document.addEventListener('input', event => {
        const field = event.target;
        if (inStudentForm(field) && field.type !== 'file' && field.validity?.valid) showFieldError(field, '');
    });
    let focusing = false;
    document.addEventListener('invalid', event => {
        const field = event.target;
        if (!inStudentForm(field)) return;
        event.preventDefault();
        showFieldError(field, field.validationMessage);
        if (focusing) return;
        focusing = true;
        focusField(field);
        setTimeout(() => { focusing = false; }, 0);
    }, true);
    requestAnimationFrame(restoreErrors);
}
