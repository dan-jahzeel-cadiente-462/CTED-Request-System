import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['count'];
    static values = {
        url: String,
        interval: Number,
    };

    connect() {
        this.poll();
        const interval = this.intervalValue || 10000;
        this.timer = window.setInterval(() => this.poll(), interval);
    }

    disconnect() {
        if (this.timer) {
            window.clearInterval(this.timer);
        }
    }

    async poll() {
        if (!this.urlValue) {
            return;
        }

        try {
            const response = await fetch(this.urlValue, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            const count = Number(data.count) || 0;

            if (this.hasCountTarget) {
                this.countTarget.textContent = count > 0 ? String(count) : '';
                this.countTarget.classList.toggle('hidden', count === 0);
            }
        } catch (error) {
            console.error('Admin badge poll failed:', error);
        }
    }
}
