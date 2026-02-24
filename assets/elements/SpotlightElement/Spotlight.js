import SpotlightItem from "./SpotlightItem.js";
import {debounce} from "../../helpers/Timer.js";
import ScrollTrigger from "../../lib/ScrollTrigger.js";

/**
 * <spotlight-bar> custom element.
 *
 * Builds a searchable suggestion list from a set of target links in the document.
 * The list is built from the `target` attribute selector.
 *
 * @example
 *  <spotlight-bar target=".header__nav a"></spotlight-bar>
 *
 * @extends HTMLElement
 *
 * @property {HTMLInputElement} input
 * @property {SpotlightItem[]} items
 * @property {SpotlightItem[]} matchedItems
 * @property {SpotlightItem|null} activeItem
 * @property {boolean} isOpen
 */
export default class Spotlight extends HTMLElement {
    /** @type {HTMLInputElement} */
    input;

    /** @type {SpotlightItem[]} */
    items = [];

    /** @type {SpotlightItem[]} */
    matchedItems = [];

    /** @type {SpotlightItem|null} */
    activeItem = null;

    /** @type {boolean} */
    isOpen = false;

    /** @type {ScrollTrigger} */
    triggerScroll;

    /** @type {HTMLUListElement|null} */
    suggestions = null;

    /** @type {HTMLElement|null} */
    previousFocus = null;

    /** @type {HTMLElement|null} */
    status = null

    /**
     * Note: for older browsers, avoid class fields with arrow functions.
     * We bind methods to keep `this` stable in callbacks.
     */
    constructor() {
        super();

        /** @private */
        this.shortcutHandler = this.shortcutHandler.bind(this)

        /** @private */
        this.open = this.open.bind(this)

        /** @private */
        this.hide = this.hide.bind(this)

        /** @private */
        this.onInput = this.onInput.bind(this)

        /** @private */
        this.inputShortcutHandler = this.inputShortcutHandler.bind(this)

        this.isOpen = false;
        this.triggerScroll = new ScrollTrigger('is-scroll-locked')
    }

    /**
     * Lifecycle: called when the element is added to the DOM.
     * Builds DOM + registers event listeners.
     *
     * @returns {void}
     */
    connectedCallback() {
        // Build the Spotlight HTML structure.
        this.classList.add('spotlight')
        this.innerHTML = `<div class="spotlight__bar">
            <label class="sr-only"  for="spotlight_search_input">
                Go to a page.
            </label>
            <input
                type="text"
                id="spotlight_search_input"
                name="spotlight"
                role="combobox"
                aria-autocomplete="list"
                aria-expanded="false"
                aria-controls="spotlight_list"
                aria-activedescendant=""
            >
            <ul
                id="spotlight_list"
                class="spotlight__suggestions"
                role="listbox"
            ></ul>
            <div
                id="spotlight_status"
                class="sr-only"
                aria-live="polite"
                aria-haspopup="listbox"
            ></div>
        </div>`


        // Select the input
        /** @type {HTMLInputElement} */
        this.input = this.querySelector('input');

        /** @type {HTMLUListElement} */
        this.suggestions = this.querySelector('.spotlight__suggestions')
        this.items = this.buildItemsFromTarget();

        this.status = this.querySelector('#spotlight_status');

        // listeners
        this.input.addEventListener('input', debounce(this.onInput, 300))
        this.input.addEventListener('keydown', this.inputShortcutHandler)
        window.addEventListener('keydown', this.shortcutHandler)
    }

    disconnectedCallback() {
        window.removeEventListener('keydown', this.shortcutHandler)
    }

    /**
     * Reads the `target` attribute and builds SpotlightItem list.
     *
     * @private
     * @returns {SpotlightItem[]}
     */
    buildItemsFromTarget() {
        const selector = this.getAttribute("target") || "";
        if (!selector || !this.suggestions) return [];

        return Array.from(document.querySelectorAll(selector))
            .map((el) => {
                // Ensure is valid href link not #
                const title = (el.innerText || "").trim();
                if (title === "") return null

                const href = el.getAttribute('href') || "#"

                if (!href || href === "#") return null

                const item = new SpotlightItem(title, href)

                this.suggestions.appendChild(item.element)

                return item
            })
            .filter((i) => i !== null)
    }

    /**
     * Global shortcut handler: Ctrl+K toggles the spotlight.
     *
     * @private
     * @param {KeyboardEvent} e
     * @returns {void}
     */
    shortcutHandler(e) {
        if (e.key === 'k' && e.ctrlKey === true) {
            e.preventDefault()
            this.isOpen ? this.hide() : this.open()
        }
    }

    /**
     * Opens the spotlight UI and locks scroll.
     *
     * @returns {void}
     */
    open() {
        if (this.isOpen) return;

        // Save focus to restore it when closing
        /** @type {HTMLElement|null} */
        this.previousFocus = (document.activeElement)

        this.isOpen = true;
        this.classList.add('active')
        this.input.setAttribute('aria-expanded', 'true')
        this.input.focus()

        // SR Announcement
        this.status && (this.status.textContent = 'Search open')

        this.triggerScroll.disable()
    }

    /**
     * Closes the spotlight UI and restores scroll.
     *
     * @returns {void}
     */
    hide() {
        if (!this.isOpen) return;

        this.isOpen = false
        this.classList.remove('active')
        this.input.setAttribute('aria-expanded', 'false')
        this.input.setAttribute('aria-activedescendant', '')

        // clear active state for SR consistency
        if (this.activeItem) {
            this.activeItem.unactiveElement();
            this.activeItem = null;
        }

        this.triggerScroll.enable()

        // Reset the previous focus
        this.previousFocus?.focus?.()
        this.previousFocus = null
    }

    /**
     * Input handler (debounced): filters items based on typed query.
     *
     * Later: can be replaced by a fuzzy-search lib.
     *
     * @private
     * @param {InputEvent} e
     * @returns {void}
     */
    onInput(e) {
        /** @type {HTMLInputElement|null} */
        const target = (e.target)
        const search = (target?.value || "").trim()

        if (search === "") {
            this.items.forEach(item => item.hide())
            this.matchedItems = []
            this.activeItem = null
            this.input.setAttribute('aria-activedescendant', '')

            if (this.status) this.status.textContent = 'Search cleared'
            return;
        }

        const regexp = this.buildLooseRegexp(search)
        this.matchedItems = this.items.filter(item => item.match(regexp))

        if (this.status) {
            const n = this.matchedItems.length
            this.status.textContent = n === 0 ? 'No results' : `${n} result${n > 1 ? 's' : ''}`
        }

        if (this.matchedItems.length > 0) {
            this.setActiveIndex(0)
        } else {
            this.activeItem = null
            this.input.setAttribute('aria-activedescendant', '')
        }
    }

    /**
     * Creates a "loose" regexp so "abc" matches "a...b...c" (case-insensitive).
     *
     * @private
     * @param {string} search
     * @returns {RegExp}
     */
    buildLooseRegexp(search){
        // IMPORTANT: escape regexp-special chars to avoid invalid regex
        // when user types e.g. "(" or "."
        const escaped = Array.from(search).map((ch) => ch.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"))

        let pattern = "^(.*)"
        for (const ch of escaped) {
            pattern += `(${ch})(.*)`
        }

        pattern += "$"

        return new RegExp(pattern, "i")
    }

    /**
     * Activates the item at index `n` in matchedItems.
     *
     * @private
     * @param {number} n
     * @returns {void}
     */
    setActiveIndex(n) {
        if (!this.matchedItems.length) {
            this.input.setAttribute('aria-activedescendant', '');
            this.activeItem = null
            return;
        }

        if (this.activeItem) {
            this.activeItem.unactiveElement()
        }

        if (n >= this.matchedItems.length) n = 0
        if (n < 0) n = this.matchedItems.length - 1

        const item = this.matchedItems[n]
        item.activeElement()
        this.activeItem = item

        // Screen reader: announce the active option
        this.input.setAttribute('aria-activedescendant', item.id);
    }

    /**
     * Keyboard navigation inside the input.
     *
     * @private
     * @param {KeyboardEvent} e
     * @returns {void}
     */
    inputShortcutHandler(e) {
        if (e.key === "Escape") {
            e.preventDefault()
            this.hide()

            this.input.value = ''
            this.items.forEach(i => i.hide())
            this.matchedItems = []
            this.activeItem = null
            this.input.setAttribute('aria-activedescendant', '')

            // SR Announcement
            this.status && (this.status.textContent = 'Reset search');
            return;
        }

        // Safety: if nothing matched, don't try to navigate/follow
        if (!this.matchedItems.length || !this.activeItem) return;

        if (e.key === "ArrowDown") {
            e.preventDefault()
            const index = this.matchedItems.findIndex((it) => it === this.activeItem)
            this.setActiveIndex(index + 1)
        } else if (e.key === "ArrowUp") {
            e.preventDefault()
            const index = this.matchedItems.findIndex((it) => it === this.activeItem)
            this.setActiveIndex(index - 1)
        } else if (e.key === "Enter") {
            e.preventDefault()
            this.activeItem.follow()
        }
    }
}