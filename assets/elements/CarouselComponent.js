import {Carousel} from "../libs/carousel.js"
import {removeKey} from "../helpers/ObjectHelper.js";

export default class CarouselComponent extends HTMLElement {

    constructor() {
        super();
    }

    connectedCallback() {
        // Prepare the object before to send to the Carousel instance
        let config = this.buildAnObjectFromAttributes(this.attributes);

        // When all it was initialized, we pass the new object through the carousel instance
        new Carousel(this, {
           slidesVisible: config.slidesvisibles ? config.slidesvisibles : 1,
           infinite: config.infinite ? config.infinite : false,
        });
    }

    /**
     * Create an object for the Carousel class from html attributes
     *
     * @param {NamedNodeMap} htmlAttributes
     * @return {Omit<*, never>}
     */
    buildAnObjectFromAttributes(htmlAttributes) {
        let attributes = [].slice.call(htmlAttributes);
        let objectConfig = {}
        attributes.forEach(attribute => {
            let value = attribute.value;
            // If the html attribute is a number (e.g. slidesVisibles="1")
            if (this.isNumberValue(value)) {
                objectConfig[attribute.name] = parseInt(attribute.value, 10);
            } else if (this.isBooleanValue(value)) {
                // If the html attribute is a boolean (e.g. infinite="true")
                objectConfig[attribute.name] = Boolean(value);
            } else {
                // If the html attribute is a string (e.g. carouselClass="myCarousel")
                objectConfig[attribute.name] = attribute.value;
            }
        })

        // To avoid to destroy the original object,
        // We create an object clone and to remove unnecessary keys,
        // before to send to the carousel instance
        return removeKey(objectConfig, 'class');
    }


    /**
     * Check if the value is a boolean (my-attribute="false")
     *
     * @param {string} value
     * @return {boolean}
     */
    isBooleanValue(value) {
        return value === "true" || value === "false";
    }

    /**
     * Check if the htmlAttribute is a number
     *
     * @param {number} value
     * @return {boolean}
     */
    isNumberValue(value) {
        return !isNaN(value);
    }

    disconnectedCallback() {}
}