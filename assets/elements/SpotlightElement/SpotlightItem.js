/**
 * Represent a spotlight suggestion element
 *
 * @property {HTMLLIElement } element
 * @property {string} title
 * @property {string} href
 */
export default class SpotlightItem {

    /**
     * Spotlight Suggestion constructor.
     *
     * @param {string} title
     * @param {string} href
     */
    constructor(title, href) {
        // create the HTML element
        const li = document.createElement('li')
        const a = document.createElement('a')

        // Set the HTML attributes
        a.setAttribute('href', href)
        a.innerText = title;
        li.appendChild(a);

        // Store the list suggestion in the element instance.
        this.element = li;
        this.title = title
        this.href = href
        this.hide()
    }

    /**
     * To find if a matched element with the regexp parameter
     *
     * @param {RegExp} regexp
     * @return {boolean}
     */
    match(regexp) {
        const matches = this.title.match(regexp)
        if (matches === null) {
            this.hide()
            return false
        }

        this.element.firstChild.innerHTML = matches.reduce((acc, match, index) => {
            if (index === 0) {
                return acc
            }
            return acc + (index % 2 === 0 ? `<mark>${match}</mark>` : match)
            }, '')
        this.element.removeAttribute('hidden')
        return true
    }

    /**
     * Hide all the list elements
     */
    hide() {
        this.element.setAttribute('hidden', 'hidden')
    }

    /**
     * Add the active class on selected element.
     */
    activeElement() {
        this.element.classList.add('active')
    }

    /**
     * remove the active class on unselected element.
     */
    unactiveElement() {
        this.element.classList.remove('active')
    }

    /**
     * Redirect to the location
     */
    follow() {
        window.location.href = this.href
    }
}