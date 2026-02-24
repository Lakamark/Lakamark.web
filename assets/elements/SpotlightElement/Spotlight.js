import SpotlightItem from "./SpotlightItem.js";
import {clearAllBodyScrollLocks, disableBodyScroll, enableBodyScroll} from "body-scroll-lock";
import {debounce} from "../../helpers/Timer.js";

/**
 * Spotlight
 * We can display a suggestion list from the target.
 *
 * @property {HTMLInputElement} input
 * @property {SpotlightItem[]} items
 * @property {SpotlightItem[]} matchedItems
 * @property {SpotlightItem} activeItem
 */
export default class Spotlight extends HTMLElement {

    /**
     * For oldest navigators, the new arrow syntax won't work.
     * We manually bind the ($this) context to ensure the global context
     * pass through the function. By default, inside the function is a local context.
     *
     * myFunction = () => {
     *     // your function logic
     * }
     */
    constructor() {
        super();
        this.shortcutHandler = this.shortcutHandler.bind(this)
        this.hide = this.hide.bind(this)
        this.onInput = this.onInput.bind(this)
        this.inputShortcutHandler = this.inputShortcutHandler.bind(this)
    }

    connectedCallback() {
        // Build the Spotlight HTML structure.
        this.classList.add('spotlight')
        this.innerHTML = `<div class="spotlight__bar">
            <input type="text" id="spotlight_search_input" name="spotlight">
            <ul class="spotlight__suggestions">
            </ul>
        </div>`

        // Select the input
        this.input = this.querySelector('input');

        // build the suggestion list from the target selector property
        const suggestions = this.querySelector('.spotlight__suggestions')
        this.items = Array.from(document.querySelectorAll(this.getAttribute('target')))
            .map(a => {
                const title = a.innerText.trim()

                if (title === '') {
                    return null;
                }

                const item = new SpotlightItem(
                    title,
                    a.getAttribute('href')
                )
                suggestions.appendChild(item.element)
                return item;
            }).filter(i => i !== null)

        // add listeners
        this.input.addEventListener('blur', this.hide)
        this.input.addEventListener('input', debounce(this.onInput, 300))
        this.input.addEventListener('keydown', this.inputShortcutHandler)
        window.addEventListener('keydown', this.shortcutHandler)
    }

    disconnectedCallback() {
        window.removeEventListener('keydown', this.shortcutHandler)
        clearAllBodyScrollLocks()
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
            disableBodyScroll(this)
        }
    }

    /**
     * Remove the active class
     */
    hide() {
        this.classList.remove('active')
        enableBodyScroll(this)
    }

    /**
     * Listen when the user is typing in the input.
     *
     * Later, I will use fizzy searching library.
     *
     * @param {InputEvent} e
     */
    onInput(e) {
        const search = e.target.value.trim();

        // empty search
        if (search === '') {
            this.items.forEach(item => item.hide())
            this.matchedItems = []

            return;
        }

        let regexp = '^(.*)'

        for (const i in search) {
            regexp += `(${search[i]})(.*)`
        }

        regexp += '$'
        regexp = new RegExp(regexp, 'i')
        this.matchedItems = this.items.filter(item => item.match(regexp))

        if (this.matchedItems.length > 0) {
            this.setActiveIndex(0)
        }
    }

    /**
     * Activate the selected index
     *
     * @param {number} n
     */
    setActiveIndex(n) {
        if (this.activeItem) {
            this.activeItem.unactiveElement()
        }

        if (n >= this.matchedItems.length) {
            n = 0
        }

        if (n < 0) {
            n = this.matchedItems.length -1
        }

        this.matchedItems[n].activeElement()
        this.activeItem = this.matchedItems[n]
    }

    /**
     * To navigate in the list
     * @param {KeyboardEvent} e
     */
    inputShortcutHandler(e) {
        if (e.key === 'Escape') {
            this.input.blur()
        } else if (e.key === 'ArrowDown') {
            const index = this.matchedItems.findIndex(element => element === this.activeItem)
            this.setActiveIndex(index + 1)
        } else if(e.key === 'ArrowUp') {
            const index = this.matchedItems.findIndex(element => element === this.activeItem)
            this.setActiveIndex(index - 1)
        } else if(e.key === 'Enter') {
            this.activeItem.follow()
        }
    }
}