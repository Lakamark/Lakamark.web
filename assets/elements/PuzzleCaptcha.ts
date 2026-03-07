import { clamp, randomNumberBetween } from "../helpers/Maths";
import { verifyCaptcha } from "../helpers/Api/captchaApi";

interface PuzzleCaptchaConfig {
    width: number;
    height: number;
    pieceWidth: number;
    pieceHeight: number;
    background: string;
    type: string;
}

interface PuzzleCaptchaState {
    valid?: boolean;
    invalid?: boolean;
    message?: string;
}

export default class PuzzleCaptcha extends HTMLElement {
    private static readonly ICON_SUCCESS = "M5 13l4 4L19 7";
    private static readonly ICON_ERROR = "M6 6L18 18M18 6L6 18";

    private isDragging = false;
    private isValidated = false;
    private isBound = false;
    private isLocked = false;
    private isSubmitting = false;

    private position = {
        x: 0,
        y: 0,
    };

    private config: PuzzleCaptchaConfig = {
        width: 0,
        height: 0,
        pieceWidth: 0,
        pieceHeight: 0,
        background: "",
        type: "puzzle",
    };

    private input: HTMLInputElement | null = null;
    private message: HTMLElement | null = null;
    private piece: HTMLDivElement | null = null;
    private overlay: HTMLDivElement | null = null;
    private icon: SVGPathElement | null = null;

    public connectedCallback(): void {
        if (this.isBound) {
            return;
        }

        this.config = this.readConfig();
        this.cacheElements();
        this.createPiece();
        this.createOverlay();
        this.applyStyles();
        this.randomizePosition();
        this.bindEvents();

        this.isBound = true;
    }

    public disconnectedCallback(): void {
        this.unbindEvents();
        document.body.style.removeProperty("user-select");

        this.isDragging = false;
        this.isSubmitting = false;
        this.isBound = false;
    }

    private readConfig(): PuzzleCaptchaConfig {
        return {
            width: parseInt(this.getAttribute("width") ?? "0", 10),
            height: parseInt(this.getAttribute("height") ?? "0", 10),
            pieceWidth: parseInt(this.getAttribute("piece-width") ?? "0", 10),
            pieceHeight: parseInt(this.getAttribute("piece-height") ?? "0", 10),
            background: this.getAttribute("src") ?? "",
            type: this.getAttribute("captcha-type") ?? "puzzle",
        };
    }

    private cacheElements(): void {
        this.input = this.querySelector(".captcha-answer");
        this.message = this.querySelector(".captcha-message");

        if (!(this.input instanceof HTMLInputElement)) {
            throw new Error("PuzzleCaptcha: missing .captcha-answer input");
        }
    }

    private createPiece(): void {
        const existingPiece = this.querySelector(".captcha__piece");

        if (existingPiece instanceof HTMLDivElement) {
            this.piece = existingPiece;
            return;
        }

        this.piece = document.createElement("div");
        this.piece.className = "captcha__piece";
        this.appendChild(this.piece);
    }

    private createOverlay(): void {
        const existingOverlay = this.querySelector(".captcha__overlay");

        if (existingOverlay instanceof HTMLDivElement) {
            this.overlay = existingOverlay;
            this.icon = this.overlay.querySelector(".captcha__icon__path") as SVGPathElement | null;
            return;
        }

        this.overlay = document.createElement("div");
        this.overlay.className = "captcha__overlay";

        this.overlay.innerHTML = `
            <svg class="captcha-overlay__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path class="captcha__icon__path"></path>
            </svg>
        `;

        this.appendChild(this.overlay);
        this.icon = this.overlay.querySelector(".captcha__icon__path") as SVGPathElement | null;
    }

    private applyStyles(): void {
        this.classList.add("captcha", "captcha-waiting-interaction");

        const cssVars: Record<string, string> = {
            "--width": `${this.config.width}px`,
            "--height": `${this.config.height}px`,
            "--piece-width": `${this.config.pieceWidth}px`,
            "--piece-height": `${this.config.pieceHeight}px`,
            "--image": `url('${this.config.background}')`,
        };

        for (const [key, value] of Object.entries(cssVars)) {
            this.style.setProperty(key, value);
        }
    }

    private randomizePosition(): void {
        this.position = {
            x: Math.round(randomNumberBetween(0, this.maxX)),
            y: Math.round(randomNumberBetween(0, this.maxY)),
        };

        this.syncPosition();
    }

    private bindEvents(): void {
        this.piece?.addEventListener("pointerdown", this.handlePointerDown);
        this.addEventListener("pointermove", this.handlePointerMove);
        window.addEventListener("pointerup", this.handlePointerUp);
    }

    private unbindEvents(): void {
        this.piece?.removeEventListener("pointerdown", this.handlePointerDown);
        this.removeEventListener("pointermove", this.handlePointerMove);
        window.removeEventListener("pointerup", this.handlePointerUp);
    }

    private readonly handlePointerDown = (): void => {
        if (this.isValidated || this.isLocked || this.isSubmitting) {
            return;
        }

        this.isDragging = true;
        document.body.style.setProperty("user-select", "none");

        this.classList.remove("captcha-waiting-interaction");
        this.piece?.classList.add("is-moving");
    };

    private readonly handlePointerMove = (event: PointerEvent): void => {
        if (!this.isDragging || this.isValidated || this.isLocked || this.isSubmitting) {
            return;
        }

        this.position.x = clamp(this.position.x + event.movementX, 0, this.maxX);
        this.position.y = clamp(this.position.y + event.movementY, 0, this.maxY);

        this.syncPosition();
    };

    private readonly handlePointerUp = async (): Promise<void> => {
        if (!this.isDragging || this.isSubmitting) {
            return;
        }

        this.isDragging = false;
        this.isSubmitting = true;

        document.body.style.removeProperty("user-select");
        this.piece?.classList.remove("is-moving");

        try {
            await this.validateCaptcha();
        } finally {
            this.isSubmitting = false;
        }
    };

    private syncPosition(): void {
        this.piece?.style.setProperty(
            "transform",
            `translate(${this.position.x}px, ${this.position.y}px)`
        );

        if (this.input) {
            this.input.value = `${this.position.x}-${this.position.y}`;
        }
    }

    private async validateCaptcha(): Promise<void> {
        this.setState({
            valid: false,
            invalid: false,
            message: "Validation...",
        });

        try {
            const answer = this.input?.value ?? "";

            const result = await verifyCaptcha({
                type: this.config.type,
                answer,
            });

            if (!result) {
                this.setInvalid("Network error.");
                return;
            }

            if (!result.ok) {
                if (result.error === "request_timeout") {
                    this.setInvalid("The server is unavailable.");
                    return;
                }

                if (result.error === "network_error") {
                    this.setInvalid("Network error.");
                    return;
                }

                if (result.data?.locked) {
                    this.isLocked = true;
                    this.classList.add("is-locked");
                    this.setInvalid("Too many attempts.");
                    return;
                }

                this.setInvalid("Invalid captcha.");
                this.randomizePosition();
                return;
            }

            if (result.data?.valid) {
                this.isValidated = true;

                this.setState({
                    valid: true,
                    invalid: false,
                    message: "Validated.",
                });

                return;
            }

            this.setInvalid("Invalid captcha.");
            this.randomizePosition();
        } catch (error) {
            console.error("PuzzleCaptcha validation error:", error);
            this.setInvalid("Network error.");
        }
    }

    private setState({
                         valid = false,
                         invalid = false,
                         message = "",
                     }: PuzzleCaptchaState): void {
        this.overlay?.classList.toggle("is-visible", valid || invalid);
        this.overlay?.classList.toggle("is-success", valid);
        this.overlay?.classList.toggle("is-error", invalid);

        if (valid) {
            this.icon?.setAttribute("d", PuzzleCaptcha.ICON_SUCCESS);
            this.classList.add("captcha-completed");
        }

        if (invalid) {
            this.icon?.setAttribute("d", PuzzleCaptcha.ICON_ERROR);
            this.classList.remove("captcha-completed");
        }

        if (this.message) {
            this.message.textContent = message;
        }
    }

    private setInvalid(message: string): void {
        this.isValidated = false;

        this.setState({
            valid: false,
            invalid: true,
            message,
        });
    }

    private get maxX(): number {
        return this.config.width - this.config.pieceWidth;
    }

    private get maxY(): number {
        return this.config.height - this.config.pieceHeight;
    }
}