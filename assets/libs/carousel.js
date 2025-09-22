export default class Carousel {
    slidesVisible;

    /**
     * @param {HTMLElement} element
     * @param {Object} options
     * @param {Object} [options.slidesToScroll=1] Number of element to scroll
     * @param {Object} [options.slidesVisible=1] Number of the element visible in a slider
     *
     * TODO Fix the ratio elements
     */
    constructor(element, options = {}) {
        this.element = element
        this.options = Object.assign({}, {
            slidesToScroll: 1,
            slidesVisible: 1,
        }, options);
        this.children = [].slice.call(element.children);
        let ratio = this.children.length / this.slidesVisible;
        let root = this.createDivWithClass('carousel');
        let container = this.createDivWithClass('carousel__container');
        root.appendChild(container);
        this.element.appendChild(root);
        this.children.forEach((child) => {
            let item =this.createDivWithClass('carousel__item');
            item.appendChild(child);
            container.appendChild(item);
        })
    }

    /**
     * Create a div with class css attribute
     *
     * @param className
     * @returns {HTMLElement}
     */
    createDivWithClass(className) {
        let div = document.createElement('div');
        div.setAttribute('class', className);
        return div;
    }
}