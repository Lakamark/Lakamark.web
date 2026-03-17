import {Carousel} from "./Carousel";

export interface CarouselPlugin {
    init(carousel: Carousel): void;
    destroy(): void;
}