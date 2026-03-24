import "../helpers/setupDom";
import {describe, it, expect} from "vitest";
import {createCarousel} from "../helpers/createCarousel";
import {
    CarouselA11y,
    CarouselAutoPlay,
    CarouselNavigation,
    CarouselPagination,
    CarouselTouch
} from "../../lib/ui/Carousel";

describe('Carousel integration', () => {
    it('should renderer the carousel with all plugins', () => {
        const {carousel} = createCarousel(4, {
            slidesVisible: 1,
            slidesToScroll: 1,
            messages: {
                prev: 'Previous slide',
                next: 'Next slide',
                goToSlide: (index: number) => `Go to slide ${index}`,
                slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`
            }
        });

        carousel.registerPlugin(new CarouselNavigation());
        carousel.registerPlugin(new CarouselPagination());
        carousel.registerPlugin(new CarouselA11y());
        carousel.registerPlugin(new CarouselTouch());
        carousel.registerPlugin(new CarouselAutoPlay())

        const root = carousel.getRoot();

        expect(root.querySelector('.carousel__controls')).not.toBeNull();
        expect(root.querySelector('.carousel__pagination')).not.toBeNull();
        expect(root.querySelector('.sr-only')).not.toBeNull();

        const nextButton = root.querySelector('.carousel__next') as HTMLButtonElement;
        nextButton.click();

        expect(carousel.getCurrentItem()).toBe(1);

        const paginationButtons = root.querySelectorAll(
            '.carousel__pagination__button'
        ) as NodeListOf<HTMLButtonElement>;

        expect(paginationButtons[1].classList.contains('carousel__pagination__button--active')).toBe(true);

        const liveRegion = root.querySelector('.sr-only');
        expect(liveRegion?.textContent).toBe('Slide 2 of 4');
    });
})