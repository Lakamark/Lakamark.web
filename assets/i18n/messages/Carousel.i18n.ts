import { TranslationDictionary } from '../translate';

export interface CarouselMessages {
    prev: string;
    next: string;
    goToSlide: (index: number) => string;
    slideXOfY: (current: number, total: number) => string;
}

export const CAROUSEL_MESSAGES: TranslationDictionary<CarouselMessages> = {
    en: {
        prev: 'Previous slide',
        next: 'Next slide',
        goToSlide: (index: number) => `Go to slide ${index}`,
        slideXOfY: (current: number, total: number) => `Slide ${current} of ${total}`,
    },
    fr: {
        prev: 'Diapositive précédente',
        next: 'Diapositive suivante',
        goToSlide: (index: number) => `Aller à la diapositive ${index}`,
        slideXOfY: (current: number, total: number) => `Diapositive ${current} sur ${total}`,
    },
};