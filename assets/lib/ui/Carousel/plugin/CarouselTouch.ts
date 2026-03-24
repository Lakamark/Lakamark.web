import {CarouselPlugin} from "../CarouselPlugin";
import {Carousel} from "../Carousel";

export class CarouselTouch implements CarouselPlugin {
    private carousel!: Carousel;

    private startX = 0;
    private startY = 0;
    private deltaX = 0;
    private deltaY = 0;

    private isPointerDown = false;
    private directionLocked: 'horizontal' | 'vertical' | null = null;
    private didSwipe = false;

    private readonly swipeThreshold = 50;
    private readonly lockThreshold = 10;

    private boundPointerDown!: (e: PointerEvent) => void;
    private boundPointerMove!: (e: PointerEvent) => void;
    private boundPointerUp!: (e: PointerEvent) => void;
    private boundPointerCancel!: () => void;
    private boundClickCapture!: (e: Event) => void;

    public init(carousel: Carousel) {
        this.carousel = carousel;

        this.boundPointerDown = this.onPointerDown.bind(this);
        this.boundPointerMove = this.onPointerMove.bind(this);
        this.boundPointerUp = this.onPointerUp.bind(this);
        this.boundPointerCancel = this.onPointerCancel.bind(this);
        this.boundClickCapture = this.onClickCapture.bind(this);

        const root = this.carousel.getRoot();

        root.style.touchAction = 'pan-y';

        root.addEventListener('pointerdown', this.boundPointerDown, { passive: true });
        root.addEventListener('pointermove', this.boundPointerMove, { passive: true });
        root.addEventListener('pointerup', this.boundPointerUp, { passive: true });
        root.addEventListener('pointercancel', this.boundPointerCancel, { passive: true });
        root.addEventListener('click', this.boundClickCapture, true);
    }

    destroy(): void {
        const root = this.carousel.getRoot();

        root.removeEventListener('pointerdown', this.boundPointerDown);
        root.removeEventListener('pointermove', this.boundPointerMove);
        root.removeEventListener('pointerup', this.boundPointerUp);
        root.removeEventListener('pointercancel', this.boundPointerCancel);
        root.removeEventListener('click', this.boundClickCapture, true);
    }

    private onPointerDown(event: PointerEvent): void {
        if (!event.isPrimary) return;
        if (event.pointerType === 'mouse' && event.button !== 0) return;

        if (this.isInteractive(event)) return;

        this.isPointerDown = true;
        this.directionLocked = null;
        this.didSwipe = false;

        this.startX = event.clientX;
        this.startY = event.clientY;
    }

    private onPointerMove(event: PointerEvent): void {
        if (!this.isPointerDown) return;

        this.deltaX = event.clientX - this.startX;
        this.deltaY = event.clientY - this.startY;

        if (this.directionLocked === null) {
            const absX = Math.abs(this.deltaX);
            const absY = Math.abs(this.deltaY);

            if (absX < this.lockThreshold && absY < this.lockThreshold) return;

            this.directionLocked = absX > absY ? 'horizontal' : 'vertical';
        }
    }

    private onPointerUp(event: PointerEvent): void {
        if (!this.isPointerDown) return;

        const dx = event.clientX - this.startX;
        const dy = event.clientY - this.startY;
        const directionLocked = this.directionLocked;

        this.reset();

        if (directionLocked !== 'horizontal') return;
        if (Math.abs(dx) < this.swipeThreshold) return;
        if (Math.abs(dy) > Math.abs(dx)) return;

        this.didSwipe = true;

        dx < 0 ? this.carousel.next() : this.carousel.prev();

        setTimeout(() => {
            this.didSwipe = false;
        }, 0);
    }

    private onPointerCancel(): void {
        this.reset();
    }

    private onClickCapture(event: Event): void {
        if (!this.didSwipe) return;

        event.preventDefault();
        event.stopPropagation();
    }

    private reset(): void {
        this.isPointerDown = false;
        this.directionLocked = null;
        this.deltaX = 0;
        this.deltaY = 0;
    }

    private isInteractive(event: PointerEvent): boolean {
        const target = event.target;
        if (!(target instanceof Element)) return false;

        return target.closest(
            'a,button,input,textarea,select,[data-carousel-swipe-ignore]'
        ) !== null;
    }
}