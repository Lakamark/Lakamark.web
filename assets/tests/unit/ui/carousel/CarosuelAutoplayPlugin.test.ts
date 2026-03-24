import {afterEach, beforeEach, describe, vi, it, expect} from "vitest";
import "../../../helpers/setupDom";
import {createCarousel} from "../../../helpers/createCarousel";
import {CarouselAutoPlay} from "../../../../lib/ui/Carousel";
describe('AutoplayPlugin', () => {
    beforeEach(() => {
        vi.useFakeTimers();
    });

    afterEach(() => {
        vi.clearAllTimers();
        vi.useRealTimers();
    });

    it('should calls carousel.next after delay on init', () => {
        const {carousel} = createCarousel(4, {
            slidesVisible: 1,
            slidesToScroll: 1,
            messages: {
                prev: "Previous",
                next: "Next",
                goToSlide: (index: number) => `Go to slide ${index}`,
                slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`
            }
        });

        carousel.registerPlugin(new CarouselAutoPlay({ delay: 1000}));
        expect(carousel.getCurrentItem()).toBe(0);

        vi.advanceTimersByTime(1000);

        expect(carousel.getCurrentItem()).toBe(1);
    });

    it('should continue to move on each interval', () => {
        const { carousel } = createCarousel(4, {
            slidesVisible: 1,
            slidesToScroll: 1,
            messages: {
                prev: "Previous",
                next: "Next",
                goToSlide: (index: number) => `Go to slide ${index}`,
                slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`
            }
        });

        carousel.registerPlugin(new CarouselAutoPlay({ delay: 1000 }));

        vi.advanceTimersByTime(3000);

        expect(carousel.getCurrentItem()).toBe(3);
    });

    it('should pause autoplay on mouseenter', () => {
        const { carousel } = createCarousel(4, {
            slidesVisible: 1,
            slidesToScroll: 1,
            messages: {
                prev: "Previous",
                next: "Next",
                goToSlide: (index: number) => `Go to slide ${index}`,
                slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`
            }
        });

        carousel.registerPlugin(new CarouselAutoPlay({ delay: 1000 }));

        carousel.getRoot().dispatchEvent(new Event("mouseenter"));

        vi.advanceTimersByTime(3000);

        expect(carousel.getCurrentItem()).toBe(0);
    });

    it('should resume autoplay on mouseleave', () => {
        const { carousel } = createCarousel(4, {
            slidesVisible: 1,
            slidesToScroll: 1,
            messages: {
                prev: "Previous",
                next: "Next",
                goToSlide: (index: number) => `Go to slide ${index}`,
                slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`
            }
        });

        carousel.registerPlugin(new CarouselAutoPlay({ delay: 1000 }));

        carousel.getRoot().dispatchEvent(new Event("mouseenter"));
        vi.advanceTimersByTime(2000);

        expect(carousel.getCurrentItem()).toBe(0);

        carousel.getRoot().dispatchEvent(new Event("mouseleave"));
        vi.advanceTimersByTime(1000);

        expect(carousel.getCurrentItem()).toBe(1);
    });

    it('should pauses autoplay on focusin', () => {
        const { carousel } = createCarousel(4, {
            slidesVisible: 1,
            slidesToScroll: 1,
            messages: {
                prev: "Previous",
                next: "Next",
                goToSlide: (index: number) => `Go to slide ${index}`,
                slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`
            }
        });

        carousel.registerPlugin(new CarouselAutoPlay({ delay: 1000 }));

        carousel.getRoot().dispatchEvent(new FocusEvent("focusin", { bubbles: true }));
        vi.advanceTimersByTime(2000);

        expect(carousel.getCurrentItem()).toBe(0);
    });

    it('should stops autoplay when document becomes hidden', () => {
        const { carousel } = createCarousel(4, {
            slidesVisible: 1,
            slidesToScroll: 1,
            messages: {
                prev: "Previous",
                next: "Next",
                goToSlide: (index: number) => `Go to slide ${index}`,
                slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`
            }
        });

        carousel.registerPlugin(new CarouselAutoPlay({ delay: 1000 }));

        Object.defineProperty(document, "hidden", {
            configurable: true,
            get: () => true
        });

        document.dispatchEvent(new Event("visibilitychange"));
        vi.advanceTimersByTime(3000);

        expect(carousel.getCurrentItem()).toBe(0);
    });

    it('should stops autoplay when the carousel is destroyed', () => {
        const { carousel } = createCarousel(4, {
            slidesVisible: 1,
            slidesToScroll: 1,
            messages: {
                prev: "Previous",
                next: "Next",
                goToSlide: (index: number) => `Go to slide ${index}`,
                slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`
            }
        });

        carousel.registerPlugin(new CarouselAutoPlay({ delay: 1000 }));

        carousel.destroy();
        vi.advanceTimersByTime(3000);

        expect(carousel.getCurrentItem()).toBe(0);
    });
})