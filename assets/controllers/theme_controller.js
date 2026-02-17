import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['toggle'];

    connect() {
        const saved = localStorage.getItem('theme');
        if (saved === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        this.syncToggle();
    }

    toggleTargetConnected() {
        this.syncToggle();
    }

    switch() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        this.syncToggle();
    }

    syncToggle() {
        if (this.hasToggleTarget) {
            this.toggleTarget.checked = document.documentElement.classList.contains('dark');
        }
    }
}
