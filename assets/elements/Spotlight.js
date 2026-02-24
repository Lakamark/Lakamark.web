/**
 * Spotlight
 * We can display a suggestion list from the target.
 *
 * @property {HTMLInputElement } input
 */
export default class Spotlight extends HTMLElement {

    /**
     * For oldest navigators, the new arrow syntax won't work.
     * We manually bind the ($this) context to ensure the global context
     * pass through the function.
     *
     * By default, inside the function is a local context.
     *
     * myFunction = () => {
     *     // your function logic
     * }
     */
    constructor() {
        super();
        this.shortcutHandler = this.shortcutHandler.bind(this)
        this.hide = this.hide.bind(this)
    }

    connectedCallback() {
        this.classList.add('spotlight')
        this.innerHTML = `<div class="spotlight__bar">
            <input type="text">
            <ul class="spotlight__suggestions">
                <li class="active"><a href="#">Home</a></li>
                <li><a href="#">Blo<mark>g</mark></a></li>
            </ul>
        </div>`

        // Select the input
        this.input = this.querySelector('input');

        // add listeners
        this.input.addEventListener('blur', this.hide)
        window.addEventListener('keydown', this.shortcutHandler)
    }

    /**
     * To Manage the shortcuts
     *
     * @param {KeyboardEvent} e
     */
    shortcutHandler(e) {
        if (e.key === 'k' && e.ctrlKey === true) {
            e.preventDefault()
            this.classList.add('active')
            this.input.focus();
        }

        // Escape
        if (e.key === 'Escape' && document.activeElement === this.input) {
            this.input.blur();

        }
    }

    hide() {
        this.classList.remove('active')
    }

    disconnectedCallback() {
        window.removeEventListener('keydown', this.shortcutHandler)
    }
}