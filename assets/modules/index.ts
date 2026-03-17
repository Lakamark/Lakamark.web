import { AppModuleInterface } from "../app/core/module/AppModuleInterface";
import { HeaderModule } from "./HeaderModule";
import { ThemeModule } from "./ThemeModule";
import { SpotlightModule } from "./SpotlightModule";
import { PuzzleModule } from "./PuzzleModule";

export function bootstrapModules(): AppModuleInterface[] {
    return [
        new HeaderModule(),
        new ThemeModule(),
        new SpotlightModule(),
        new PuzzleModule(),
    ];
}