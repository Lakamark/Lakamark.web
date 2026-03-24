import { describe, expect, it, vi } from 'vitest';
import '../../../helpers/setupDom';
import {createCarousel} from "../../../helpers/createCarousel";
import {CarouselTouch} from "../../../../lib/ui/Carousel";

function dispatchPointerEvent(
    target: Element,
    type: string,
    {
        clientX = 0,
        clientY = 0,
        pointerType = 'touch',
        isPrimary = true,
        button = 0
    }: {
        clientX?: number;
        clientY?: number;
        pointerType?: string;
        isPrimary?: boolean;
        button?: number;
    } = {}
): Event {
    const event = new Event(type, { bubbles: true, cancelable: true }) as PointerEvent;

    Object.defineProperties(event, {
        clientX: { value: clientX },
        clientY: { value: clientY },
        pointerType: { value: pointerType },
        isPrimary: { value: isPrimary },
        button: { value: button }
    });

    target.dispatchEvent(event);
    return event;
}

describe('CarouselTouch', () => {
    it('sets touch-action on the carousel root', () => {
        const { carousel } = createCarousel(4);
        carousel.registerPlugin(new CarouselTouch());

        expect(carousel.getRoot().style.touchAction).toBe('pan-y');
    });

    it('moves to the next slide on left swipe', () => {
        const { carousel } = createCarousel(4, {
            slidesVisible: 1,
            slidesToScroll: 1
        });

        carousel.registerPlugin(new CarouselTouch());

        const root = carousel.getRoot();

        dispatchPointerEvent(root, 'pointerdown', { clientX: 200, clientY: 0 });
        dispatchPointerEvent(root, 'pointermove', { clientX: 120, clientY: 5 });
        dispatchPointerEvent(root, 'pointerup', { clientX: 120, clientY: 5 });

        expect(carousel.getCurrentItem()).toBe(1);
    });

    it('moves to the previous slide on right swipe', () => {
        const { carousel } = createCarousel(4, {
            slidesVisible: 1,
            slidesToScroll: 1
        });

        carousel.goToItem(1);
        carousel.registerPlugin(new CarouselTouch());

        const root = carousel.getRoot();

        dispatchPointerEvent(root, 'pointerdown', { clientX: 100, clientY: 0 });
        dispatchPointerEvent(root, 'pointermove', { clientX: 180, clientY: 5 });
        dispatchPointerEvent(root, 'pointerup', { clientX: 180, clientY: 5 });

        expect(carousel.getCurrentItem()).toBe(0);
    });

    it('does not move when swipe distance is too small', () => {
        const { carousel } = createCarousel(4, {
            slidesVisible: 1,
            slidesToScroll: 1
        });

        carousel.registerPlugin(new CarouselTouch());

        const root = carousel.getRoot();

        dispatchPointerEvent(root, 'pointerdown', { clientX: 200, clientY: 0 });
        dispatchPointerEvent(root, 'pointermove', { clientX: 170, clientY: 0 });
        dispatchPointerEvent(root, 'pointerup', { clientX: 170, clientY: 0 });

        expect(carousel.getCurrentItem()).toBe(0);
    });

    it('does not move on vertical gesture', () => {
        const { carousel } = createCarousel(4, {
            slidesVisible: 1,
            slidesToScroll: 1
        });

        carousel.registerPlugin(new CarouselTouch());

        const root = carousel.getRoot();

        dispatchPointerEvent(root, 'pointerdown', { clientX: 100, clientY: 100 });
        dispatchPointerEvent(root, 'pointermove', { clientX: 110, clientY: 180 });
        dispatchPointerEvent(root, 'pointerup', { clientX: 110, clientY: 180 });

        expect(carousel.getCurrentItem()).toBe(0);
    });

    it('ignores swipe when starting from an interactive element', () => {
        const { carousel } = createCarousel(4, {
            slidesVisible: 1,
            slidesToScroll: 1
        });

        carousel.registerPlugin(new CarouselTouch());

        const button = document.createElement('button');
        button.textContent = 'Click me';
        carousel.getRoot().appendChild(button);

        dispatchPointerEvent(button, 'pointerdown', { clientX: 200, clientY: 0 });
        dispatchPointerEvent(button, 'pointermove', { clientX: 100, clientY: 0 });
        dispatchPointerEvent(button, 'pointerup', { clientX: 100, clientY: 0 });

        expect(carousel.getCurrentItem()).toBe(0);
    });

    it('prevents click immediately after a swipe', async () => {
        const { carousel } = createCarousel(4, {
            slidesVisible: 1,
            slidesToScroll: 1
        });

        carousel.registerPlugin(new CarouselTouch());

        const root = carousel.getRoot();

        dispatchPointerEvent(root, 'pointerdown', { clientX: 200, clientY: 0 });
        dispatchPointerEvent(root, 'pointermove', { clientX: 100, clientY: 0 });
        dispatchPointerEvent(root, 'pointerup', { clientX: 100, clientY: 0 });

        const clickEvent = new MouseEvent('click', { bubbles: true, cancelable: true });
        const preventDefaultSpy = vi.spyOn(clickEvent, 'preventDefault');
        const stopPropagationSpy = vi.spyOn(clickEvent, 'stopPropagation');

        root.dispatchEvent(clickEvent);

        expect(preventDefaultSpy).toHaveBeenCalled();
        expect(stopPropagationSpy).toHaveBeenCalled();
    });

    it('removes touch behavior on destroy', () => {
        const { carousel } = createCarousel(4, {
            slidesVisible: 1,
            slidesToScroll: 1
        });

        const plugin = new CarouselTouch();
        carousel.registerPlugin(plugin);
        plugin.destroy();

        const root = carousel.getRoot();

        dispatchPointerEvent(root, 'pointerdown', { clientX: 200, clientY: 0 });
        dispatchPointerEvent(root, 'pointermove', { clientX: 100, clientY: 0 });
        dispatchPointerEvent(root, 'pointerup', { clientX: 100, clientY: 0 });

        expect(carousel.getCurrentItem()).toBe(0);
    });
});