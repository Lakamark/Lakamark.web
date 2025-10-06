import {debounce} from "../helpers/Timer.js";

/**
 * @property {HTMLInputElement} input
 * @property {SpotlightItem[]} items
 * @property {SpotlightItem[]} matchedItems
 * @property {SpotlightItem} activeItem
 * @property {HTMLUListElement} suggestions
 */
export default class Spotlight extends HTMLElement {

    constructor() {
        super();
        this.shortcutHandler = this.shortcutHandler.bind(this);
        this.hide = this.hide.bind(this);
        this.onInputHandler = this.onInputHandler.bind(this);
        this.inputShortcutHandler = this.inputShortcutHandler.bind(this);
    }

    connectedCallback() {
        // Build the spotlight HTML structure component
        this.classList.add('spotlight');
        this.innerHTML = `
        <div class="spotlight__bar">
            <input type="text" id="spotlightSearch">
            <ul class="spotlight__suggestions" hidden="hidden">
            </ul>
        </div>`

        this.input = this.querySelector('input');
        this.input.addEventListener('blur', this.hide)

        // Build the suggestion list
        this.suggestions = document.querySelector('.spotlight__suggestions');
        this.items = Array.from(document.querySelectorAll(this.getAttribute('target'))).map(a => {
            const title = a.innerText.trim()


            // If the title is empty we won't create a spotlightItem element
            if (title === '') {
                return null;
            }

            const item = new SpotlightItem(title, a.getAttribute('href'));
            this.suggestions.appendChild(item.element)
            return item;
        }).filter(i => i !== null);


        // Listen the shortcut event
        window.addEventListener('keydown', this.shortcutHandler)
        this.input.addEventListener('input', debounce(this.onInputHandler, 500))
        this.input.addEventListener('keydown', this.inputShortcutHandler)
    }

    disconnectedCallback() {
        window.removeEventListener('keydown', this.shortcutHandler)
    }

    /**
     * To display the spotlight component in the DOM
     *
     * @param {KeyboardEvent} e
     */
    shortcutHandler(e) {
        // If the user press on CTRL+k shortcut
        if (e.key === 'k' && e.ctrlKey === true) {
            e.preventDefault();
            this.classList.add('active');
            this.input.value = ''
            this.onInputHandler()
            this.input.focus();
        }
    }

    /**
     * To optimize the search behavior with a fuzzy matching library
     */
    onInputHandler() {
        const search = this.input.value.trim()

        // If search value is empty
        if (search === '') {
            this.items.forEach(item => item.hide())
            this.matchedItems = []
            this.suggestions.setAttribute('hidden', 'hidden');
            return;
        }

        // Set up the regexp
        let regexp = '^(.*)'
        for (let i in search) {
            regexp += `(${search[i]})(.*)`
        }
        regexp += '$'
        regexp = new RegExp(regexp, 'i');

        // Find a matched item
        this.matchedItems = this.items.filter(item => item.match(regexp))

        // Check if we are one or more suggestions to display the list
        if (this.matchedItems.length > 0) {
            this.suggestions.removeAttribute('hidden')
           this.setActiveIndex(0)
        } else {
            this.suggestions.setAttribute('hidden', 'hidden')
        }

    }

    /**
     * Set an active item
     *
     * @param {number} n element index to active
     */
    setActiveIndex(n) {
        if (this.activeItem) {
            this.activeItem.unselect();
        }

        // If the user extend the matched items array we loop
        if (n >= this.matchedItems.length) {
            n = 0
        }

        if (n < 0) {
            n = this.matchedItems.length - 1
        }

        this.matchedItems[n].select();
        this.activeItem = this.matchedItems[n];
    }

    /**
     * To listen different shortcut
     * @param {KeyboardEvent} e
     */
    inputShortcutHandler (e) {
        if(e.key === 'Escape') {
            this.input.blur()
        } else if (e.key === 'ArrowDown') {
            const index = this.matchedItems.findIndex(element => element === this.activeItem)
            this.setActiveIndex(index + 1)
        } else if (e.key === 'ArrowUp') {
            const index = this.matchedItems.findIndex(element => element === this.activeItem)
            this.setActiveIndex(index - 1)
        } else if (e.key === 'Enter') {
            this.activeItem.follow()
        }
    }

    hide () {
        this.classList.remove('active');
    }
}

/**
 * @property {HTMLLIElement} element
 * @property {string} title
 * @property {string} href
 */
class SpotlightItem {

    /**
     * @param {string} title
     * @param {string} href
     */
    constructor(title, href) {
        const li = document.createElement('li');
        const a = document.createElement('a');
        a.setAttribute('href', href);
        a.innerText = title;
        li.appendChild(a);
        li.setAttribute('hidden', 'hidden');
        this.element = li
        this.title = title;
        this.href = href;
        this.hide()
    }

    /**
     * To match an element with a regexp
     *
     * @param {RegExp} regexp
     * @return {boolean}
     */
    match(regexp) {
        const matches = this.title.match(regexp);
        if(matches === null) {
            this.hide()
            return false;
        }

        // Wrap the matched item with mark html
        this.element.firstElementChild.innerHTML = matches.reduce((acc, match, index) => {
            if (index === 0) {
                return acc;
            }
            return acc + (index % 2 === 0 ? `<mark>${match}</mark>` : match);
        }, '');
        this.element.removeAttribute('hidden');
        return true
    }

    /**
     * To hide an item
     */
    hide () {
        this.element.setAttribute('hidden', 'hidden');
    }

    /**
     * Select the active item
     */
    select() {
        this.element.classList.add('active');
    }

    /**
     * Unselect the active item
     */
    unselect() {
        this.element.classList.remove('active');
    }

    /**
     * Redirect to the selected link item
     */
    follow() {
        window.location.href = this.href
    }
}