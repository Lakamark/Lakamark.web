// Core
export { Carousel } from './Carousel';

// Types
export type { CarouselOptions, MoveCallback } from './Carousel.types';
export type { CarouselPlugin } from './CarouselPlugin';

// i18n
export { CAROUSEL_MESSAGES } from '../../../i18n/messages/Carousel.i18n'
export type { CarouselMessages } from '../../../i18n/messages/Carousel.i18n';

// Plugins (re-export)
export {
    CarouselNavigation,
    CarouselPagination,
    CarouselA11y,
    CarouselTouch,
    CarouselAutoPlay
} from './plugin';