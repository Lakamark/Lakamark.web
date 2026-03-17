import {AbstractModule} from "../app/core/module/AbstractModule";
import {AppRunner} from "../app/core/runner/AppRunner";
import {CarouselElement} from "../components/CarouselElement";

export class CarouselModule extends AbstractModule {
    protected onMount(_runner: AppRunner) {
        if (!customElements.get("carousel-module")) {
            customElements.define("carousel-module", CarouselElement);
        }
    }
}