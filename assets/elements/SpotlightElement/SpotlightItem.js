/**
 * Represents a single spotlight suggestion item.
 *
 * Responsible for:
 * - Rendering its own DOM element
 * - Matching against a RegExp
 * - Highlighting matched characters
 * - Handling active state
 * - Redirecting when selected
 *
 * @property {HTMLLIElement} element
 * @property {HTMLAnchorElement} link
 * @property {string} title
 * @property {string} href
 */
export default class SpotlightItem {
    /** @type {HTMLLIElement} */
    element;

    /** @type {HTMLAnchorElement} */
    link;

    /** @type {string} */
    title;

    /** @type {string} */
    href;

    /** @type {number} */
    static nextId = 0

    /**
     * Creates a Spotlight suggestion item.
     *
     * @param {string} title - Displayed label.
     * @param {string} href - Target URL.
     */
    constructor(title, href) {
        this.title = title
        this.href = href;

        // create DOM
        this.element = document.createElement('li')
        this.link = document.createElement('a')

        this.id = `spotlight_option_${SpotlightItem.nextId++}`
        this.element.id = this.id

        this.element.setAttribute('role', 'option')
        this.element.setAttribute('aria-selected', 'false');

        this.link.setAttribute('href', href)
        this.link.innerText = title;

        this.element.appendChild(this.link);

        this.hide()
    }

    /**
     * Tests the title against a RegExp and highlights matches.
     *
     * Returns true if matched, false otherwise.
     *
     * @param {RegExp} regexp
     * @returns {boolean}
     */
    match(regexp) {
        const matches = this.title.match(regexp)
        if (!matches) {
            this.hide()
            return false;
        }

        this.link.innerHTML = this.buildHighlightedHTML(matches)
        this.element.removeAttribute('hidden');

        return true;
    }

    /**
     * Builds the highlighted HTML string from regex matches.
     *
     * @private
     * @param {RegExpMatchArray} matches
     * @returns {string}
     */

    buildHighlightedHTML(matches){
        return matches.reduce((acc, match, index) => {
            if (index === 0) return acc;

            // Even index = captured search character
            if (index % 2 === 0) {
                return acc + `<mark>${this.escapeHTML(match)}</mark>`;
            }

            return acc + this.escapeHTML(match);
        }, '')
    }

    /**
     * Escapes HTML special characters to prevent injection.
     *
     * @private
     * @param {string} str
     * @returns {string}
     */
    escapeHTML(str) {
        return str.replace(/[&<>"']/g, (char) => {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            };
            return map[char];
        });
    }

    /**
     * Hides the item.
     *
     * @returns {void}
     */
    hide() {
        this.element.setAttribute('hidden', 'hidden')
        this.element.setAttribute('aria-selected', 'false');
    }

    /**
     * Activates the item (visual state).
     *
     * @returns {void}
     */
    activeElement() {
        this.element.classList.add('active')
        this.element.setAttribute('aria-selected', 'true');
    }

    /**
     * Removes active state.
     *
     * @returns {void}
     */
    unactiveElement() {
        this.element.classList.remove('active')
        this.element.setAttribute('aria-selected', 'false');
    }

    /**
     * Redirects the browser to the item URL.
     *
     * @returns {void}
     */
    follow() {
        window.location.href = this.href
    }
}