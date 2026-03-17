import {CarouselMessages} from "../../../i18n/messages/Carousel.i18n";

export type MoveCallback = (index: number) => void;

export interface CarouselOptions {
    slidesToScroll: number;
    slidesVisible: number;
    loop: boolean;
    infinite: boolean;
    overflow: boolean;
    iconNext: string;
    iconPrev: string;
    mobileBreakpoint: number;
    messages: CarouselMessages;
}

export const DEFAULT_CAROUSEL_OPTIONS: CarouselOptions = {
    slidesToScroll: 1,
    slidesVisible: 1,
    loop: false,
    infinite: false,
    overflow: true,
    iconNext: '',
    iconPrev: '',
    mobileBreakpoint: 800,
    messages: {
        prev: 'Previous slide',
        next: 'Next slide',
        goToSlide: (index: number) => `Go to slide ${index}`,
        slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`,
    },
};