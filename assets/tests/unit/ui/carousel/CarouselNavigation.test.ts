import {describe, it, expect} from "vitest";
import {CarouselNavigation} from "../../../../lib/ui/Carousel";
import {createCarousel} from "../../../helpers/createCarousel";
import "../../../helpers/setupDom";

describe('Carousel Navigation Plugin', () => {

    it('should renders navigation controls', () => {
        const {carousel} = createCarousel();
        carousel.registerPlugin(new CarouselNavigation());

        expect(carousel.getRoot().querySelector('.carousel__controls')).not.toBeNull();
        expect(carousel.getRoot().querySelector('.carousel__prev')).not.toBeNull();
        expect(carousel.getRoot().querySelector('.carousel__next')).not.toBeNull();
    });

    it('moves to the next slide when clicking next', () => {
        const {carousel} = createCarousel(4, {
            slidesVisible: 2,
            slidesToScroll: 1
        });

        carousel.registerPlugin(new CarouselNavigation());

        const nextButton = carousel.getRoot().querySelector('.carousel__next') as HTMLButtonElement;
        nextButton.click();

        expect(carousel.getCurrentItem()).toBe(1);
    });
})