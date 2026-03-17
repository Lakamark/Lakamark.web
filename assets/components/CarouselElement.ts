import {
    Carousel,
    CarouselA11y,
    CarouselNavigation,
    CarouselPagination,
    CarouselTouch
} from "../lib/ui/Carousel";

export class CarouselElement extends HTMLElement {
    private carousel: Carousel | null = null;

    connectedCallback(): void {
        this.carousel = new Carousel(this);

        this.carousel.registerPlugin(new CarouselNavigation());
        this.carousel.registerPlugin(new CarouselPagination());
        this.carousel.registerPlugin(new CarouselA11y());
        this.carousel.registerPlugin(new CarouselTouch());
    }

    disconnectedCallback(): void {
        this.carousel?.destroy();
        this.carousel = null;
    }
}