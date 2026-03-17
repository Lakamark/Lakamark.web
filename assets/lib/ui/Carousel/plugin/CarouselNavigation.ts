import {CarouselPlugin} from "../CarouselPlugin";
import {Carousel} from "../Carousel";

export class CarouselNavigation implements CarouselPlugin {
    private carousel!: Carousel;
    private controls!: HTMLDivElement;
    private prevButton!: HTMLButtonElement;
    private nextButton!: HTMLButtonElement;
    private unsubscribeOnMove?: () => void;

    private readonly boundPrev = () => this.carousel.prev();
    private readonly boundNext = () => this.carousel.next();

    public init(carousel: Carousel) {
        this.carousel = carousel;

        const options = this.carousel.getOptions();
        const messages = options.messages;

        this.controls = this.createDiv('carousel__controls');

        this.prevButton = this.createButton(
            'carousel__prev',
            options.iconPrev || 'prev',
            messages.prev
        );

        this.nextButton = this.createButton(
            'carousel__next',
            options.iconNext || 'next',
            messages.next
        );

        this.prevButton.addEventListener('click', this.boundPrev);
        this.nextButton.addEventListener('click', this.boundNext);

        this.controls.appendChild(this.prevButton);
        this.controls.appendChild(this.nextButton);

        this.carousel.getRoot().appendChild(this.controls);

        if (!this.carousel.isLoopEnabled()) {
            this.unsubscribeOnMove = this.carousel.onMove(() => this.updateVisibility());
            this.updateVisibility();
        }
    }

    destroy(): void {
        this.prevButton?.removeEventListener('click', this.boundPrev);
        this.nextButton?.removeEventListener('click', this.boundNext);
        this.unsubscribeOnMove?.();
        this.controls?.remove();
    }

    private updateVisibility(): void {
        const currentItem = this.carousel.getCurrentItem();
        const items = this.carousel.getItems();
        const slidesVisible = this.carousel.getSlidesVisible();

        this.prevButton.classList.toggle('carousel__prev--hidden', currentItem === 0);
        this.nextButton.classList.toggle(
            'carousel__next--hidden',
            items[currentItem + slidesVisible] === undefined
        );
    }

    private createDiv(className: string): HTMLDivElement {
        const div = document.createElement('div');
        div.className = className;
        return div;
    }

    private createButton(
        className: string,
        content: string,
        ariaLabel: string
    ): HTMLButtonElement {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.innerHTML = content;
        button.setAttribute('aria-label', ariaLabel);
        button.setAttribute('title', ariaLabel);

        return button;
    }
}