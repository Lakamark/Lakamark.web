import {AppModuleInterface} from "@core/module/AppModuleInterface";
import {HeaderModule} from "@modules/HeaderModule";
import {ThemeModule} from "@modules/ThemeModule";
import {SpotlightModule} from "@modules/SpotlightModule";
import {PuzzleModule} from "@modules/PuzzleModule";


export function boostrapModules(): AppModuleInterface[] {
    return [
        new HeaderModule(),
        new ThemeModule(),
        new SpotlightModule(),
        new PuzzleModule(),
    ];
}