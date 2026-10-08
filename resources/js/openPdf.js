import { usePage } from '@inertiajs/vue3';

// The part of a signatory (a user or an assignatoree) that the PDFs print.
export function signatory(person) {
    return { name: person?.name, designation: person?.designation };
}

// Opens a PDF report in a new tab, with its parameters posted in the body of
// the request instead of written in the address, where their length is
// limited: past it the server answers "414 Request-URI Too Large".
// `query` is the same query string the address used to end with.
export function openPdf(url, query) {
    const form = document.createElement('form');
    form.method = 'post';
    form.action = url;
    form.target = '_blank';
    form.hidden = true;

    const add = (name, value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    };
    add('_token', usePage().props.csrf_token);
    new URLSearchParams(query).forEach((value, name) => add(name, value));

    document.body.appendChild(form);
    form.submit();
    form.remove();
}
