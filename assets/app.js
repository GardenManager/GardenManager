import './stimulus_bootstrap.js';
import { initFlowbite } from 'flowbite';

document.addEventListener('turbo:render', () => initFlowbite());
document.addEventListener('turbo:frame-render', () => initFlowbite());
