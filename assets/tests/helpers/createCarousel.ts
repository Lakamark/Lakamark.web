import {createHost} from "./createHost";
import {Carousel} from "../../lib/ui/Carousel";

export function createCarousel(slideCount = 4, options = {}) {
    const host = createHost(slideCount);
    const carousel = new Carousel(host, {
        slidesVisible: 1,
        slidesToScroll: 1,
        ...options
    });

    return { host, carousel };
}