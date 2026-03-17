import {AppModuleInterface} from "../module/AppModuleInterface";

export class AppRunner {
    private readonly modules: AppModuleInterface[] = [];
    private mounted = false;

    public register(module: AppModuleInterface): this {
        if (this.modules.includes(module)) {
            return this;
        }

        this.modules.push(module);

        return this;
    }

    public registerMany(modules: AppModuleInterface[]): this {
        for (const module of modules) {
            this.register(module);
        }

        return this;
    }

    public mount(): void {
        if (this.mounted) {
            return;
        }

        for (const module of this.modules) {
            module.mount(this);
        }

        this.mounted = true;
    }

    public destroy(): void {
        if (!this.mounted) {
            return;
        }

        for (const module of [...this.modules].reverse()) {
            module.destroy();
        }

        this.mounted = false;
    }

    public isMounted(): boolean {
        return this.mounted;
    }

    public getModules(): readonly AppModuleInterface[] {
        return this.modules;
    }

    public getModuleCount(): number {
        return this.modules.length;
    }
}