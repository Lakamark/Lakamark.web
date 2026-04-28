import {AbstractModule} from "../../core";
import {AppConfig} from "../../dom";

export class FakeModule extends AbstractModule {
    public mountCount = 0;
    public destroyCount = 0;
    public lastConfig: AppConfig | null = null;

    protected onMount(config: AppConfig): void {
        this.mountCount++;
        this.lastConfig = config;
    }

    protected onDestroy(): void {
        this.destroyCount++;
    }
}