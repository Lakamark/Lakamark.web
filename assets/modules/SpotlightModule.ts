import {AbstractModule} from "../app/core/module/AbstractModule";
import {AppRunner} from "../app/core/runner/AppRunner";
import Spotlight from "../lib/ui/Spotlight/Spotlight";

export class SpotlightModule extends AbstractModule {
   protected onMount(_runner: AppRunner) {
       if (customElements.get("spotlight-bar")) {
           return;
       }

       customElements.define("spotlight-bar", Spotlight);
   }
}