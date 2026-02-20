import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['menu'];

    connect() {
        this._closeOnClickOutside = (event) => {
            if (!this.element.contains(event.target)) {
                this.menuTarget.classList.add('hidden');
            }
        };

        document.addEventListener('click', this._closeOnClickOutside);
    }

    disconnect() {
        document.removeEventListener('click', this._closeOnClickOutside);
    }

    toggle() {
        this.menuTarget.classList.toggle('hidden');
    }
}
