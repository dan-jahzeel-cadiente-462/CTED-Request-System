import { Controller } from '@hotwired/stimulus';

/**
 * @class CollapsibleController
 * @property {HTMLElement} contentTarget
 * @property {string[]} hiddenClasses
 */
export default class extends Controller {
    static targets = ['content'];

    toggle() {
        this.contentTarget.classList.toggle('hidden');
    }
}
