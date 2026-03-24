import {describe, it, expect} from "vitest";
import {createCarousel} from "../../../helpers/createCarousel";

describe('Carousel', () => {

    it('should wraps children in the carousel structure', () => {
        const {host, carousel} = createCarousel(3)

        const root = carousel.getRoot();
        const container = carousel.getContainer();
        const items = carousel.getItems();

        // Structure
        expect(root.classList.contains('carousel')).toBe(true);
        expect(container.classList.contains('carousel__container')).toBe(true);

        // Items numbers
        expect(items).toHaveLength(3);

        // Each child was wrapped in the carousel structure
        items.forEach(item => {
            expect(item.classList.contains('carousel__item')).toBe(true);
            expect(item.children.length).toBe(1); // The slide content
        });

        // The root is inside the carousel host
        expect(root.parentElement).toBe(host);
    });

    it('should appy right with styles to the container and items', () => {
        const {carousel} = createCarousel(4, {
            slidesVisible: 2,
            slidesToScroll: 1
        });

        const container = carousel.getContainer();
        const items = carousel.getItems();

        expect(container.style.width).toBe('200%');
        expect(items[0].style.width).toBe('25%');
        expect(items[1].style.width).toBe('25%');
        expect(items[2].style.width).toBe('25%');
        expect(items[3].style.width).toBe('25%');
    });

    it('should to apply right transition on the slide is moving', () => {

        const {carousel} = createCarousel(4, {
            slidesVisible: 2,
            slidesToScroll: 1
        });

        carousel.goToItem(1);

        expect(carousel.getContainer().style.transform).toBe('translate3d(-25%, 0, 0)');
    });

    it('should throw an error if the slides visible extends of real items', () => {

        expect(() => {
            createCarousel(4, {
                slidesVisible: 5,
                slidesToScroll: 1
            });
        }).toThrow('slidesVisible cannot exceed the number of items');
    });
})