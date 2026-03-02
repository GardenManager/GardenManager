import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        autoDismiss: { type: Boolean, default: false },
        timeout: { type: Number, default: 5000 },
    };

    connect() {
        this.element.style.transform = 'translateX(100%)';
        this.element.style.opacity = '0';
        this.element.style.transition = 'transform 300ms ease-out, opacity 300ms ease-out';

        requestAnimationFrame(() => {
            this.element.style.transform = 'translateX(0)';
            this.element.style.opacity = '1';
        });

        if (this.autoDismissValue) {
            this._dismissTimer = setTimeout(() => this.close(), this.timeoutValue);
        }
    }

    close() {
        this.element.style.transform = 'translateX(100%)';
        this.element.style.opacity = '0';

        this.element.addEventListener('transitionend', () => this.element.remove(), { once: true });
    }

    disconnect() {
        if (this._dismissTimer) {
            clearTimeout(this._dismissTimer);
        }
    }
}
