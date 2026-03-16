import {AppRunner} from "@core/runner/AppRunner";
import {AppModuleInterface} from "@core/module/AppModuleInterface";

export class FrontendKernel {
    private readonly runner: AppRunner;

    public constructor(runner: AppRunner = new AppRunner()) {
        this.runner = runner;
    }

    public registerModule(module: AppModuleInterface): this {
        this.runner.register(module);

        return this;
    }

    public registerModules(modules: AppModuleInterface[]): this {
        this.runner.registerMany(modules);

        return this;
    }

    public boot(): void {
        this.runner.mount();
    }

    public destroy(): void {
        this.runner.destroy();
    }

    public isBooted(): boolean {
        return this.runner.isMounted();
    }

    public getModules(): readonly AppModuleInterface[] {
        return this.runner.getModules();
    }
}