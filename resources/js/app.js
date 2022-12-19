import './bootstrap';

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import { Chess } from 'chess.js';

window.Chess = Chess;
window.Alpine = Alpine;

Alpine.plugin(focus);

Alpine.start();

import.meta.glob([
    '../img/**',
]);
