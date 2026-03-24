import '../../../helpers/setupDom';

import {describe, it, expect} from "vitest";
import {createCarousel} from "../../../helpers/createCarousel";
import {CarouselA11y} from "../../../../lib/ui/Carousel";

describe('Carousel A11y Plugin', () => {
    it('renders a live region', () => {
        const { carousel} = createCarousel(4, {
            messages: {
                prev: 'Previous slide',
                next: 'Next slide',
                goToSlide: (index: number) => `Go to slide ${index}`,
                slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`
            }
        });

        carousel.registerPlugin(new CarouselA11y());

        const liveRegion = carousel.getRoot().querySelector('.sr-only');

        expect(liveRegion).not.toBeNull();
        expect(liveRegion?.getAttribute('aria-live')).toBe('polite');
        expect(liveRegion?.getAttribute('aria-atomic')).toBe('true');

    });
    it('adds accessibility attributes to each slide', () => {
        const { carousel } = createCarousel(3, {
            messages: {
                prev: 'Previous slide',
                next: 'Next slide',
                goToSlide: (index: number) => `Go to slide ${index}`,
                slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`
            }
        });

        carousel.registerPlugin(new CarouselA11y());

        const items = carousel.getItems();

        items.forEach((item) => {
            expect(item.getAttribute('role')).toBe('group');
            expect(item.getAttribute('aria-roledescription')).toBe('slide');
        });
    });

    it('should marks visible and hidden slides with aria-hidden', () => {
        const { carousel } = createCarousel(4, {
            slidesVisible: 2,
            slidesToScroll: 1,
            messages: {
                prev: 'Previous slide',
                next: 'Next slide',
                goToSlide: (index: number) => `Go to slide ${index}`,
                slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`
            }
        });

        carousel.registerPlugin(new CarouselA11y());

        const items = carousel.getItems();

        expect(items[0].getAttribute('aria-hidden')).toBe('false');
        expect(items[1].getAttribute('aria-hidden')).toBe('false');
        expect(items[2].getAttribute('aria-hidden')).toBe('true');
        expect(items[3].getAttribute('aria-hidden')).toBe('true');
    });

    it('should updates aria-hidden when the carousel moves', () => {
        const { carousel } = createCarousel(4, {
            slidesVisible: 2,
            slidesToScroll: 1,
            messages: {
                prev: 'Previous slide',
                next: 'Next slide',
                goToSlide: (index: number) => `Go to slide ${index}`,
                slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`
            }
        });

        carousel.registerPlugin(new CarouselA11y());
        carousel.goToItem(1);

        const items = carousel.getItems();

        expect(items[0].getAttribute('aria-hidden')).toBe('true');
        expect(items[1].getAttribute('aria-hidden')).toBe('false');
        expect(items[2].getAttribute('aria-hidden')).toBe('false');
        expect(items[3].getAttribute('aria-hidden')).toBe('true');
    });

    it('should announces the current slide position', () => {
        const { carousel } = createCarousel(4, {
            slidesVisible: 1,
            slidesToScroll: 1,
            messages: {
                prev: 'Previous slide',
                next: 'Next slide',
                goToSlide: (index: number) => `Go to slide ${index}`,
                slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`
            }
        });

        carousel.registerPlugin(new CarouselA11y());

        const liveRegion = carousel.getRoot().querySelector('.sr-only');

        expect(liveRegion?.textContent).toBe('Slide 1 of 4');

        carousel.goToItem(2);

        expect(liveRegion?.textContent).toBe('Slide 3 of 4');
    });

    it('should removes the live region on destroy', () => {
        const { carousel } = createCarousel(4, {
            messages: {
                prev: 'Previous slide',
                next: 'Next slide',
                goToSlide: (index: number) => `Go to slide ${index}`,
                slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`
            }
        });

        const plugin = new CarouselA11y();
        carousel.registerPlugin(plugin);

        plugin.destroy();

        expect(carousel.getRoot().querySelector('.sr-only')).toBeNull();
    });
});