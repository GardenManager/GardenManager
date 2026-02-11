import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        message: { type: String, default: 'Are you sure?' },
    };

    onSubmit(event) {
        if (!confirm(this.messageValue)) {
            event.preventDefault();
        }
    }
}
