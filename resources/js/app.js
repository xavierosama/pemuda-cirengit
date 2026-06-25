import './bootstrap';

import Alpine from 'alpinejs';
import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';
import 'flatpickr/dist/flatpickr.css';

window.Alpine = Alpine;

Alpine.start();

const initializeDatePickers = () => {
    flatpickr('.js-date-picker', {
        altInput: true,
        altFormat: 'd/m/Y',
        allowInput: false,
        dateFormat: 'Y-m-d',
        disableMobile: true,
        locale: Indonesian,
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeDatePickers);
} else {
    initializeDatePickers();
}
