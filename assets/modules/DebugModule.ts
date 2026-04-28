import { AbstractModule } from '../core';
import type { AppConfig } from '../dom';

export class DebugModule extends AbstractModule {
    protected onMount(config: AppConfig): void {
        if (config.environment !== 'dev') {
            return;
        }

        this.removeBadge();

        console.log('%c[DebugModule] AppConfig', 'color: #00bcd4; font-weight: bold;');
        console.log(config);

        this.injectBadge();
    }

    protected onDestroy(): void {
        this.removeBadge();
    }

    private injectBadge(): void {
        const badge = document.createElement('div');
        badge.id = 'debug-badge';
        badge.textContent = 'DEV';

        Object.assign(badge.style, {
            position: 'fixed',
            bottom: '10px',
            left: '10px',
            padding: '6px 10px',
            background: '#ffac04',
            color: '#fff',
            fontSize: '12px',
            fontWeight: 'bold',
            zIndex: '9999',
            borderRadius: '4px',
        });

        document.body.appendChild(badge);
    }

    private removeBadge(): void {
        const badge: HTMLElement | null = document.getElementById('debug-badge');
        if (badge) {
            badge.remove();
        }
    }
}