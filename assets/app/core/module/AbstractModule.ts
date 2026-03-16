import { AppModuleInterface } from "@core/module/AppModuleInterface";
import { AppRunner } from "@core/runner/AppRunner";

export abstract class AbstractModule implements AppModuleInterface {
    private mounted = false;

    public mount(runner: AppRunner): void {
        if (this.mounted) {
            return;
        }

        try {
            this.onMount(runner);
            this.mounted = true;
        } catch (error) {
            console.log(
                `[Module Error] ${this.getName()} failed during mount`,
                error
            )
        }

    }

    public destroy(): void {
        if (!this.mounted) {
            return;
        }

        this.onDestroy();
        this.mounted = false;
    }

    public isMounted(): boolean {
        return this.mounted;
    }

    public getName(): string {
        return this.constructor.name;
    }

    protected abstract onMount(runner: AppRunner): void;

    protected onDestroy(): void {
    }
}