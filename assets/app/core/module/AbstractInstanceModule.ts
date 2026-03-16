import {DestroyableInterface} from "@core/module/DestroyableInterface";
import {AbstractModule} from "@core/module/AbstractModule";
import {AppRunner} from "@core/runner/AppRunner";

export abstract class AbstractInstanceModule<T extends DestroyableInterface> extends AbstractModule {
    protected instance: T | null = null;

    protected onMount(runner: AppRunner) {
        this.instance = this.createInstance(runner);
        this.afterInstanceMount(this.instance, runner);
    }

    protected onDestroy(): void {
        this.beforeInstanceDestroy(this.instance);
        this.instance?.destroy();
        this.instance = null;
    }

    protected abstract createInstance(runner: AppRunner): T;

    protected afterInstanceMount(_instance: T, _runner: AppRunner): void {
    }

    protected beforeInstanceDestroy(_instance: T | null): void {
    }
}