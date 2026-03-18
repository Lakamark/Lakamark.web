import {AppModuleInterface} from "../../app/core/module/AppModuleInterface";
import {AppRunner} from "../../app/core/runner/AppRunner";

export class BodyClassModule implements AppModuleInterface {
    private mounted = false;

    public constructor(
        private readonly className: string,
        private readonly doc: Document = document
    ) {}

    public mount(_runner: AppRunner): void {
        if (this.mounted) {
            return;
        }

        this.doc.body.classList.add(this.className);
        this.mounted = true;
    }

    public destroy(): void {
        if (!this.mounted) {
            return;
        }

        this.doc.body.classList.remove(this.className);
        this.mounted = false;
    }

    public isMounted(): boolean {
        return this.mounted;
    }
}