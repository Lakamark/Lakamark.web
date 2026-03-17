import {CarouselPlugin} from "./CarouselPlugin";
import {Carousel} from "./Carousel";

export class CarouselPagination implements CarouselPlugin {
    private carousel!: Carousel;
    private root!: HTMLDivElement;
    private buttons: HTMLButtonElement[] = [];
    private handlers = new Map<HTMLButtonElement, EventListener>();
    private unsubscribeOnMove?: () => void;

    init(carousel: Carousel): void {
        this.carousel = carousel;
        this.root = this.createDiv('carousel__pagination');

        this.build();
        this.carousel.getRoot().appendChild(this.root);

        this.unsubscribeOnMove = this.carousel.onMove((index) => this.updateActiveState(index));
        this.updateActiveState(this.carousel.getCurrentItem());
    }

    destroy(): void {
        this.handlers.forEach((handler, button) => {
            button.removeEventListener('click', handler);
        });

        this.handlers.clear();
        this.buttons = [];
        this.unsubscribeOnMove?.();
        this.root?.remove();
    }

    private build(): void {
        const offset = this.carousel.getOffset();
        const slidesToScroll = this.carousel.getSlidesToScroll();
        const realItemsCount = this.carousel.getRealItemsCount();
        const messages = this.carousel.getOptions().messages;

        if (realItemsCount <= 0 || slidesToScroll <= 0) {
            return;
        }

        for (let i = 0; i < realItemsCount; i += slidesToScroll) {
            const targetIndex = i + offset;
            const labelIndex = i + 1;

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'carousel__pagination__button';

            const label = messages.goToSlide(labelIndex);
            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);
            button.setAttribute('aria-current', 'false');

            const handler: EventListener = () => this.carousel.goToItem(targetIndex);
            button.addEventListener('click', handler);

            this.handlers.set(button, handler);
            this.buttons.push(button);
            this.root.appendChild(button);
        }
    }

    private updateActiveState(index: number): void {
        const slidesToScroll = this.carousel.getSlidesToScroll();
        const offset = this.carousel.getOffset();
        const realItemsCount = this.carousel.getRealItemsCount();

        if (realItemsCount <= 0 || slidesToScroll <= 0) {
            return;
        }

        const normalizedIndex =
            (((index - offset) % realItemsCount) + realItemsCount) % realItemsCount;

        const activeIndex = Math.floor(normalizedIndex / slidesToScroll);

        this.buttons.forEach((button, buttonIndex) => {
            const isActive = buttonIndex === activeIndex;
            button.classList.toggle('carousel__pagination__button--active', isActive);
            button.setAttribute('aria-current', isActive ? 'true' : 'false');
        });
    }

    private createDiv(className: string): HTMLDivElement {
        const div = document.createElement('div');
        div.className = className;
        return div;
    }
}