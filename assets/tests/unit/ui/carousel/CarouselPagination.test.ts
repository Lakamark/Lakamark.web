import {describe, it, expect} from "vitest";
import '../../../helpers/setupDom';
import {createCarousel} from "../../../helpers/createCarousel";
import {CarouselPagination} from "../../../../lib/ui/Carousel";

describe('CarouselPagination Plugin', () => {
    it('moves to the corresponding slide when clicking a pagination button', () => {
        const {carousel} = createCarousel(4, {
            slidesToScroll: 1,
            messages: {
                prev: 'Previous',
                next: 'Next',
                goToSlide: (index: number) => `Go to slide ${index}`,
                slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`
            }
        });

        carousel.registerPlugin(new CarouselPagination());

        const buttons = carousel.getRoot().querySelectorAll(
            '.carousel__pagination__button'
        ) as NodeListOf<HTMLButtonElement>;

        buttons[2].click();

        expect(carousel.getCurrentItem()).toBe(2);
    });

    it('should updates the active pagination button when the carousel moves', () => {
        const { carousel } = createCarousel(4, {
            slidesVisible: 1,
            slidesToScroll: 1,
            messages: {
                prev: 'Previous',
                next: 'Next',
                goToSlide: (index: number) => `Go to slide ${index}`,
                slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`
            }
        });

        carousel.registerPlugin(new CarouselPagination());

        const buttons = carousel.getRoot().querySelectorAll(
            '.carousel__pagination__button'
        ) as NodeListOf<HTMLButtonElement>;

        expect(buttons[0].classList.contains('carousel__pagination__button--active')).toBe(true);

        carousel.goToItem(2);

        expect(buttons[0].classList.contains('carousel__pagination__button--active')).toBe(false);
        expect(buttons[2].classList.contains('carousel__pagination__button--active')).toBe(true);
    });

    it('creates pagination buttons based on slidesToScroll', () => {
        const { carousel } = createCarousel(4, {
            slidesVisible: 2,
            slidesToScroll: 2,
            messages: {
                prev: 'Previous',
                next: 'Next',
                goToSlide: (index: number) => `Go to slide ${index}`,
                slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`
            }
        });

        carousel.registerPlugin(new CarouselPagination());
        const buttons = carousel.getRoot().querySelectorAll('.carousel__pagination__button');

        expect(buttons).toHaveLength(2);
    })
});