export default class ThemeComponent extends HTMLElement {
    connectedCallback(): void {
        if (this.hasToggle()) {
            return;
        }

        this.render();
    }

    private hasToggle(): boolean {
        return this.querySelector(".theme-toggle") !== null;
    }

    private render(): void {
        this.innerHTML = `
            <button
                class="theme-toggle"
                type="button"
                aria-label="Switch theme"
                aria-pressed="false"
            >
                <span class="sr-only">Switch theme</span>

                <span class="theme-toggle__icon theme-toggle__icon--sun" aria-hidden="true">
                    ${this.sunIcon()}
                </span>

                <span class="theme-toggle__icon theme-toggle__icon--moon" aria-hidden="true">
                    ${this.moonIcon()}
                </span>
            </button>
        `;
    }

    private sunIcon(): string {
        return `
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path
                    d="M11.9999 3.06209V2.40002M11.9999 21.6V20.938M20.9378 12H21.5999M2.3999 12H3.06197M18.3205 5.68003L18.7887 5.21188M5.21106 18.7882L5.67921 18.3201M18.3205 18.32L18.7887 18.7882M5.21106 5.21183L5.67921 5.67998M17.2835 11.9638C17.2835 14.889 14.9122 17.2604 11.987 17.2604C9.06177 17.2604 6.69042 14.889 6.69042 11.9638C6.69042 9.03861 9.06177 6.66727 11.987 6.66727C14.9122 6.66727 17.2835 9.03861 17.2835 11.9638Z"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                />
            </svg>
        `;
    }

    private moonIcon(): string {
        return `
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path
                    d="M21.6001 14.6396C20.6978 14.9133 19.7405 15.0604 18.7488 15.0604C13.3308 15.0604 8.93868 10.6682 8.93868 5.25024C8.93868 4.25902 9.08569 3.30214 9.35909 2.40021C5.33166 3.62159 2.40015 7.36325 2.40015 11.7896C2.40015 17.2076 6.7923 21.5998 12.2103 21.5998C16.6371 21.5998 20.379 18.6677 21.6001 14.6396Z"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linejoin="round"
                />
            </svg>
        `;
    }
}