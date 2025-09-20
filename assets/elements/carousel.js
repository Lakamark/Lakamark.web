export default class Carousel {

    /**
     *
     * @param {HTMLElement} element
     * @param {object} options
     * @param {object} options.slidesToScroll To define number of element to scroll
     * @param {object} options.slidesPerView To define number of visible element per slide
     */
    constructor(element, options = {}) {
        this.element = element;
        this.children = [].slice.call(element.children);
        this.options = Object.assign({}, {
            slidesToScroll: 1,
            slidesPerView: 1,
        }, options);

        // set the html carousel structure
        let root = this.createDivWithClass('carousel');
        let wrapper = this.createDivWithClass('carousel__sliders');
        wrapper.setAttribute('style', '--items: ' + options.slidesToScroll + ';');
        root.appendChild(wrapper);
        this.element.append(root);

        // Move children
        this.children.forEach(child => {
            let item = this.createDivWithClass('carousel__slider');
            item.appendChild(child);
            wrapper.appendChild(item);
        })
    }

    /**
     * Generate a div with a class
     *
     * @param {string} className
     * @return {HTMLDivElement}
     */
    createDivWithClass(className) {
        let div = document.createElement('div');
        div.setAttribute('class', className);
        return div;
    }
}