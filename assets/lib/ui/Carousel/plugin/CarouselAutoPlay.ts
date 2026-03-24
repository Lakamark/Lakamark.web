import { CarouselPlugin } from "../CarouselPlugin";
import { Carousel } from "../Carousel";

/**
 * Options for the {@link CarouselAutoPlay} plugin.
 */
export interface AutoplayPluginOptions {
    /**
     * Delay between each automatic slide transition (in milliseconds).
     *
     * @default 5000
     */
    delay?: number;
}

/**
 * Carousel plugin that enables automatic slide transitions (autoplay).
 *
 * This plugin will automatically move the carousel to the next slide
 * at a fixed interval. It also handles common UX behaviors:
 *
 * - Pauses on mouse hover
 * - Pauses when the carousel receives focus
 * - Resumes when interaction ends
 * - Pauses when the document/tab is hidden
 * - Resumes when the tab becomes visible again
 *
 * Autoplay will not start if:
 * - The carousel is not initialized
 * - A timer is already running
 * - The document is hidden
 * - There are not enough items to scroll
 *
 * @implements {CarouselPlugin}
 *
 * @example
 * ```ts
 * const carousel = new Carousel(element, {
 *   loop: true,
 * });
 *
 * carousel.registerPlugin(
 *   new CarouselAutoPlay({ delay: 3000 })
 * );
 * ```
 */
export class CarouselAutoPlay implements CarouselPlugin {
    private carousel: Carousel | null = null;
    private root: HTMLElement | null = null;

    private autoplayId: number | null = null;
    private readonly delay: number;

    /**
     * Creates a new autoplay plugin instance.
     *
     * @param options - Configuration options for autoplay behavior
     */
    public constructor(options: AutoplayPluginOptions = {}) {
        this.delay = options.delay ?? 5000;
    }

    /**
     * Initializes the plugin and attaches it to the carousel instance.
     *
     * @param carousel - The carousel instance
     */
    public init(carousel: Carousel): void {
        this.carousel = carousel;
        this.root = carousel.getRoot();

        this.bindEvents();
        this.start();
    }

    /**
     * Cleans up the plugin by stopping autoplay and removing all event listeners.
     */
    public destroy(): void {
        this.stop();
        this.unbindEvents();

        this.root = null;
        this.carousel = null;
    }

    /**
     * Starts the autoplay if conditions are met.
     *
     * Autoplay will not start if:
     * - The carousel is not available
     * - A timer is already running
     * - The document is hidden
     * - There are not enough items to scroll
     */
    public start(): void {
        if (!this.carousel || this.autoplayId !== null || document.hidden) {
            return;
        }

        // Avoid enabling autoplay if there are not enough items to scroll
        if (this.carousel.getRealItemsCount() <= this.carousel.getSlidesVisible()) {
            return;
        }

        this.autoplayId = window.setInterval(() => {
            this.carousel?.next();
        }, this.delay);
    }

    /**
     * Stops the autoplay if it is currently running.
     */
    public stop(): void {
        if (this.autoplayId === null) {
            return;
        }

        window.clearInterval(this.autoplayId);
        this.autoplayId = null;
    }


    /**
     * Binds all DOM and document events required for autoplay behavior.
     *
     * @private
     */
    private bindEvents(): void {
        this.root?.addEventListener("mouseenter", this.onMouseEnter);
        this.root?.addEventListener("mouseleave", this.onMouseLeave);

        this.root?.addEventListener("focusin", this.onFocusIn);
        this.root?.addEventListener("focusout", this.onFocusOut);

        document.addEventListener("visibilitychange", this.onVisibilityChange);
    }

    /**
     * Removes all previously bound event listeners.
     *
     * @private
     */
    private unbindEvents(): void {
        if (!this.root) {
            return;
        }

        this.root.removeEventListener("mouseenter", this.onMouseEnter);
        this.root.removeEventListener("mouseleave", this.onMouseLeave);

        this.root.removeEventListener("focusin", this.onFocusIn);
        this.root.removeEventListener("focusout", this.onFocusOut);

        document.removeEventListener("visibilitychange", this.onVisibilityChange);
    }

    /**
     * Stops autoplay when the user hovers the carousel.
     *
     * @private
     */
    private readonly onMouseEnter = (): void => {
        this.stop();
    };

    /**
     * Restarts autoplay when the user leaves the carousel.
     *
     * @private
     */
    private readonly onMouseLeave = (): void => {
        this.start();
    };

    /**
     * Stops autoplay when the carousel gains focus.
     *
     * @private
     */
    private readonly onFocusIn = (): void => {
        this.stop();
    };

    /**
     * Restarts autoplay when focus leaves the carousel.
     *
     * Ensures that the focus is not still within the carousel before restarting.
     *
     * @param event - The focus event
     *
     * @private
     */
    private readonly onFocusOut = (event: FocusEvent): void => {
        if (!this.root) {
            return;
        }

        const nextFocused = event.relatedTarget;

        if (nextFocused instanceof Node && this.root.contains(nextFocused)) {
            return;
        }

        this.start();
    };

    /**
     * Handles document visibility changes.
     *
     * Stops autoplay when the tab is hidden and restarts it when visible again.
     *
     * @private
     */
    private readonly onVisibilityChange = (): void => {
        if (document.hidden) {
            this.stop();
            return;
        }

        this.start();
    };
}