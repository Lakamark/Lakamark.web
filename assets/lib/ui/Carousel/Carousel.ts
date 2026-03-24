import {
    CarouselOptions,
    DEFAULT_CAROUSEL_OPTIONS,
    MoveCallback
} from "./Carousel.types";
import {CarouselPlugin} from "./CarouselPlugin";

export class Carousel {
    private readonly element: HTMLElement;
    private readonly options: CarouselOptions;

    private readonly root: HTMLDivElement;
    private readonly container: HTMLDivElement;

    private items: HTMLDivElement[] = [];
    private currentItem = 0;
    private isMobile = false;
    private offset = 0;

    private moveCallbacks: MoveCallback[] = [];
    private plugins: CarouselPlugin[] = [];

    private readonly boundOnResize: () => void;
    private readonly boundOnKeyUp: (event: KeyboardEvent) => void;
    private readonly boundResetInfinite: () => void;

    constructor(element: HTMLElement, options: Partial<CarouselOptions> = {}) {
        this.element = element;
        this.options = { ...DEFAULT_CAROUSEL_OPTIONS, ...options };

        if (this.options.loop && this.options.infinite) {
            throw new Error("You can't enable both 'loop' and 'infinite' on the carousel.");
        }

        const children = Array.from(this.element.children) as HTMLElement[];

        if (children.length === 0) {
            throw new Error('Carousel requires at least one child element.');
        }

        if (this.options.slidesVisible > children.length) {
            throw new Error('slidesVisible cannot exceed the number of items.');
        }

        if (this.options.slidesToScroll > children.length) {
            throw new Error('slidesToScroll cannot exceed the number of items.');
        }

        this.root = this.createDiv('carousel');
        if (this.options.overflow) {
            this.root.classList.add('carousel__overflow__hidden');
        }
        this.root.tabIndex = 0;

        this.container = this.createDiv('carousel__container');
        this.root.appendChild(this.container);

        this.items = children.map((child) => {
            const item = this.createDiv('carousel__item');
            item.appendChild(child);
            return item;
        });

        if (this.options.infinite) {
            this.setupInfiniteMode(children.length);
        }

        const fragment = document.createDocumentFragment();
        this.items.forEach((item) => fragment.appendChild(item));
        this.container.appendChild(fragment);
        this.element.appendChild(this.root);

        this.setStyles();

        this.boundOnResize = this.onWindowResize.bind(this);
        this.boundOnKeyUp = this.handleKeyUp.bind(this);
        this.boundResetInfinite = this.resetInfinite.bind(this);

        this.onWindowResize();
        window.addEventListener('resize', this.boundOnResize);
        this.root.addEventListener('keyup', this.boundOnKeyUp);

        if (this.options.infinite) {
            this.container.addEventListener('transitionend', this.boundResetInfinite);
        }

        this.emitMove();
    }

    public registerPlugin(plugin: CarouselPlugin): void {
        plugin.init(this);
        this.plugins.push(plugin);
    }

    public next(): void {
        this.goToItem(this.currentItem + this.slidesToScroll);
    }

    public prev(): void {
        this.goToItem(this.currentItem - this.slidesToScroll);
    }

    public goToItem(index: number, animation = true): void {
        if (index < 0) {
            if (this.options.loop) {
                index = this.items.length - this.slidesVisible;
            } else {
                return;
            }
        } else if (
            index >= this.items.length ||
            (this.items[this.currentItem + this.slidesVisible] === undefined && index > this.currentItem)
        ) {
            if (this.options.loop) {
                index = 0;
            } else {
                return;
            }
        }

        this.translateTo(index, animation);
        this.currentItem = index;
        this.updateUI();
        this.emitMove();
    }

    public onMove(callback: MoveCallback): () => void {
        this.moveCallbacks.push(callback);

        return () => {
            this.moveCallbacks = this.moveCallbacks.filter((cb) => cb !== callback);
        };
    }

    public destroy(): void {
        window.removeEventListener('resize', this.boundOnResize);
        this.root.removeEventListener('keyup', this.boundOnKeyUp);

        if (this.options.infinite) {
            this.container.removeEventListener('transitionend', this.boundResetInfinite);
        }

        this.plugins.forEach((plugin) => plugin.destroy());
        this.plugins = [];
        this.moveCallbacks = [];
    }

    public getRoot(): HTMLDivElement {
        return this.root;
    }

    public getContainer(): HTMLDivElement {
        return this.container;
    }

    public getItems(): HTMLDivElement[] {
        return this.items;
    }

    public getCurrentItem(): number {
        return this.currentItem;
    }

    public getSlidesVisible(): number {
        return this.slidesVisible;
    }

    public getSlidesToScroll(): number {
        return this.slidesToScroll;
    }

    public getOptions(): CarouselOptions {
        return this.options;
    }

    public isLoopEnabled(): boolean {
        return this.options.loop;
    }

    public getOffset(): number {
        return this.offset;
    }

    public getRealItemsCount(): number {
        return this.options.infinite
            ? this.items.length - 2 * this.offset
            : this.items.length;
    }

    private setupInfiniteMode(childrenLength: number): void {
        this.offset = this.options.slidesVisible + this.options.slidesToScroll;

        if (this.offset > childrenLength) {
            console.error('Not enough children in the carousel for infinite mode.', this.element);
        }

        this.items = [
            ...this.items.slice(this.items.length - this.offset)
                .map((item) => item.cloneNode(true) as HTMLDivElement),
            ...this.items,
            ...this.items.slice(0, this.offset)
                .map((item) => item.cloneNode(true) as HTMLDivElement),
        ];

        this.currentItem = this.offset;
    }

    private setStyles(): void {
        const ratio = this.items.length / this.slidesVisible;
        this.container.style.width = `${ratio * 100}%`;

        this.items.forEach((item) => {
            item.style.width = `${(100 / this.slidesVisible) / ratio}%`;
        });

        this.updateUI();
        this.translateTo(this.currentItem, false);
    }

    private translateTo(index: number, animation = true): void {
        const translateX = (index * -100) / this.items.length;

        if (!animation) {
            this.container.style.transition = 'none';
        }

        this.container.style.transform = `translate3d(${translateX}%, 0, 0)`;

        void this.container.offsetHeight;

        if (!animation) {
            this.container.style.transition = '';
        }
    }

    private updateUI(): void {
        this.items.forEach((slide, index) => {
            const isActive =
                index >= this.currentItem &&
                index < this.currentItem + this.slidesVisible;

            slide.classList.toggle('carousel__slide--active', isActive);
        });
    }

    private resetInfinite(): void {
        const realItemsCount = this.getRealItemsCount();

        if (this.currentItem <= this.options.slidesToScroll) {
            this.goToItem(this.currentItem + realItemsCount, false);
        } else if (this.currentItem >= this.items.length - this.offset) {
            this.goToItem(this.currentItem - realItemsCount, false);
        }
    }

    private emitMove(): void {
        this.moveCallbacks.forEach((callback) => callback(this.currentItem));
    }

    private onWindowResize(): void {
        const mobile = window.innerWidth < this.options.mobileBreakpoint;

        if (mobile !== this.isMobile) {
            this.isMobile = mobile;
            this.setStyles();
            this.emitMove();
        }
    }

    private handleKeyUp(event: KeyboardEvent): void {
        if (event.key === 'ArrowRight' || event.key === 'Right') {
            this.next();
        } else if (event.key === 'ArrowLeft' || event.key === 'Left') {
            this.prev();
        }
    }

    private createDiv(className: string): HTMLDivElement {
        const div = document.createElement('div');
        div.className = className;
        return div;
    }

    private get slidesToScroll(): number {
        return this.isMobile ? 1 : this.options.slidesToScroll;
    }

    private get slidesVisible(): number {
        return this.isMobile ? 1 : this.options.slidesVisible;
    }
}