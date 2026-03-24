import {
    Carousel,
    CarouselA11y,
    CarouselNavigation,
    CarouselPagination,
    CarouselTouch,
    CarouselAutoPlay
} from "../lib/ui/Carousel";

export class CarouselElement extends HTMLElement {
    private carousel: Carousel | null = null;

    connectedCallback(): void {
        this.carousel = new Carousel(this, {
            loop: true,
            iconPrev: this.renderPrevIcon(),
            iconNext: this.renderNextIcon()
        });

        this.carousel.registerPlugin(new CarouselNavigation());
        this.carousel.registerPlugin(new CarouselPagination());
        this.carousel.registerPlugin(new CarouselA11y());
        this.carousel.registerPlugin(new CarouselTouch());
        this.carousel.registerPlugin(new CarouselAutoPlay())
    }

    disconnectedCallback(): void {
        this.carousel?.destroy();
        this.carousel = null;
    }

    private renderPrevIcon(): string {
        return `
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path
            d="M10.8 16.8L6 12L10.8 7.20001M18 16.8L13.2 12L18 7.20001"
            stroke="white"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"/>
        </svg>`;
    }

    private renderNextIcon(): string {
        return `
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path
                    d="M13.2 7.19999L18 12L13.2 16.8M6 7.19999L10.8 12L6 16.8"
                    stroke="white"
                    stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round"/>
            </svg>`;
    }
}