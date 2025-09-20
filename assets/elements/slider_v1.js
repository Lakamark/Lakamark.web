export default class Slider_v1 {

    /**
     * @param {HTMLDivElement} el
     */
    constructor(el) {
        this.nextButton = el.querySelector('[data-slider-next]');
        this.prevButton = el.querySelector('[data-slider-prev]');
        this.wrapper = el.querySelector('[data-slider-wrapper]');

        // Binds
        this.nextButton.addEventListener('click', () => this.move(1));
        this.prevButton.addEventListener('click', () => this.move(-1));
        this.wrapper.addEventListener('scrollend', () => this.updateUI());
        this.updateUI();
    }

    get itemsToScroll() {
        return parseInt(window.getComputedStyle(this.wrapper).getPropertyValue('--items'), 10);
    }

    get pages () {
        return Math.ceil(this.wrapper.children.length / this.itemsToScroll)
    }

    get page () {
        return Math.ceil(this.wrapper.scrollLeft / this.wrapper.offsetWidth);
    }

    /**
     * To show or hide some UI elements depend on the pages number
     */
    updateUI() {
        if (this.page === 0) {
            this.prevButton.setAttribute('hidden', 'hidden');
        } else {
            this.prevButton.removeAttribute('hidden');
        }

        if (this.page === this.pages - 1) {
            this.nextButton.setAttribute('hidden', 'hidden');
        } else {
            this.nextButton.removeAttribute('hidden');
        }
    }

    /**
     * To move the carousel sliders
     * @param {number} n
     */
    move(n) {
        let newPage = this.page + n

        // If the current page out bounce the minimal sliders pages
        if (newPage < 0) {
            newPage = 0;
        }

        // If the current page out bounce the total pages
        if (newPage >= this.pages) {
            newPage = this.pages - 1;
        }

        this.wrapper.scrollTo({
            left:this.wrapper.children[newPage * this.itemsToScroll].offsetLeft,
            behavior: 'smooth',
        });

    }
}

//new Slider(document.querySelector("[data-slider]"));