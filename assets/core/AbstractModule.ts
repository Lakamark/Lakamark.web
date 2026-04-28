export abstract class AbstractModule {
    private mounted: boolean = false;

    mount(): void {
        if (this.mounted) {
            return;
        }

        this.mounted = true;
        this.onMount();
    }

    destroy(): void {
        if (!this.mounted) return;

        this.onDestroy();
        this.mounted = false;
    }

    protected abstract onMount(): void;
    protected abstract onDestroy(): void;
}