import {CarouselPlugin} from "../CarouselPlugin";
import {Carousel} from "../Carousel";

export class CarouselA11y implements CarouselPlugin {
    private carousel!: Carousel;
    private liveRegion!: HTMLDivElement;
    private unsubscribeOnMove?: () => void;

    public init(carousel: Carousel): void {
        this.carousel = carousel;

        this.liveRegion = this.createLiveRegion();
        this.carousel.getRoot().appendChild(this.liveRegion);

        this.setupRootAttributes();
        this.setupSlidesAttributes();

        this.unsubscribeOnMove = this.carousel.onMove(() => {
            this.updateSlides();
            this.announce();
        });

        this.updateSlides();
        this.announce();
    }

    destroy(): void {
        this.unsubscribeOnMove?.();
        this.liveRegion?.remove();
    }

    private setupRootAttributes(): void {
        const root = this.carousel.getRoot();
        root.setAttribute('role', 'region');
        root.setAttribute('aria-roledescription', 'carousel');
    }

    private setupSlidesAttributes(): void {
        this.carousel.getItems().forEach((item) => {
            item.setAttribute('role', 'group');
            item.setAttribute('aria-roledescription', 'slide');
        });
    }

    private updateSlides(): void {
        const items = this.carousel.getItems();
        const current = this.carousel.getCurrentItem();
        const visible = this.carousel.getSlidesVisible();

        items.forEach((item, index) => {
            const isVisible = index >= current && index < current + visible;
            item.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
        });
    }

    private announce(): void {
        const messages = this.carousel.getOptions().messages;
        const total = this.carousel.getRealItemsCount();
        const offset = this.carousel.getOffset();
        const index = this.carousel.getCurrentItem();

        const normalized =
            (((index - offset) % total) + total) % total;

        const current = normalized + 1;

        this.liveRegion.textContent =
            messages.slideXOfY(current, total);
    }

    private createLiveRegion(): HTMLDivElement {
        const el = document.createElement('div');
        el.className = 'sr-only';
        el.setAttribute('aria-live', 'polite');
        el.setAttribute('aria-atomic', 'true');
        return el;
    }
}