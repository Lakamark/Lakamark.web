import SpotlightItem from "./SpotlightItem";
import {debounce} from "../../../helper/Timer";
import ScrollTrigger from "../../ScrollTrigger";


export default class Spotlight extends HTMLElement {
    public input!: HTMLInputElement;
    public items: SpotlightItem[] = [];
    public matchedItems: SpotlightItem[] = [];
    public activeItem: SpotlightItem | null = null;
    public isOpen = false;
    public triggerScroll: ScrollTrigger;
    public suggestions: HTMLUListElement | null = null;
    public previousFocus: HTMLElement | null = null;
    public status: HTMLElement | null = null;

    public constructor() {
        super();

        this.shortcutHandler = this.shortcutHandler.bind(this);
        this.open = this.open.bind(this);
        this.hide = this.hide.bind(this);
        this.onInput = this.onInput.bind(this);
        this.inputShortcutHandler = this.inputShortcutHandler.bind(this);

        this.triggerScroll = new ScrollTrigger("is-scroll-locked");
    }

    public connectedCallback(): void {
        this.classList.add("spotlight");
        this.innerHTML = `<div class="spotlight__bar">
            <label class="sr-only" for="spotlight_search_input">
                Go to a page.
            </label>
            <input
                type="text"
                id="spotlight_search_input"
                name="spotlight"
                role="combobox"
                aria-autocomplete="list"
                aria-expanded="false"
                aria-controls="spotlight_list"
                aria-activedescendant=""
            >
            <ul
                id="spotlight_list"
                class="spotlight__suggestions"
                role="listbox"
            ></ul>
            <div
                id="spotlight_status"
                class="sr-only"
                aria-live="polite"
            ></div>
        </div>`;

        const input = this.querySelector("input");
        const suggestions = this.querySelector(".spotlight__suggestions");
        const status = this.querySelector<HTMLElement>("#spotlight_status");

        if (!(input instanceof HTMLInputElement)) {
            throw new Error("Spotlight input not found.");
        }

        if (!(suggestions instanceof HTMLUListElement)) {
            throw new Error("Spotlight suggestions list not found.");
        }

        this.input = input;
        this.suggestions = suggestions;
        this.status = status;

        this.items = this.buildItemsFromTarget();

        this.input.addEventListener("input", debounce(this.onInput, 300));
        this.input.addEventListener("keydown", this.inputShortcutHandler);
        window.addEventListener("keydown", this.shortcutHandler);
    }

    public disconnectedCallback(): void {
        this.input?.removeEventListener("keydown", this.inputShortcutHandler);
        window.removeEventListener("keydown", this.shortcutHandler);
    }

    private buildItemsFromTarget(): SpotlightItem[] {
        const selector = this.getAttribute("target") || "";

        if (!selector || !this.suggestions) {
            return [];
        }

        return Array.from(document.querySelectorAll(selector))
            .map((el): SpotlightItem | null => {
                if (!(el instanceof HTMLAnchorElement)) {
                    return null;
                }

                const title = (el.innerText || "").trim();
                if (title === "") {
                    return null;
                }

                const href = el.getAttribute("href") || "#";
                if (!href || href === "#") {
                    return null;
                }

                const item = new SpotlightItem(title, href);
                this.suggestions?.appendChild(item.element);

                return item;
            })
            .filter((item): item is SpotlightItem => item !== null);
    }

    private shortcutHandler(e: KeyboardEvent): void {
        if (e.key === "k" && e.ctrlKey) {
            e.preventDefault();
            this.isOpen ? this.hide() : this.open();
        }
    }

    public open(): void {
        if (this.isOpen) {
            return;
        }

        this.previousFocus = document.activeElement instanceof HTMLElement
            ? document.activeElement
            : null;

        this.isOpen = true;
        this.classList.add("active");
        this.input.setAttribute("aria-expanded", "true");
        this.input.focus();

        if (this.status) {
            this.status.textContent = "Search open";
        }

        this.triggerScroll.disable();
    }

    public hide(): void {
        if (!this.isOpen) {
            return;
        }

        this.isOpen = false;
        this.classList.remove("active");
        this.input.setAttribute("aria-expanded", "false");
        this.input.setAttribute("aria-activedescendant", "");

        if (this.activeItem) {
            this.activeItem.unactiveElement();
            this.activeItem = null;
        }

        this.triggerScroll.enable();

        this.previousFocus?.focus();
        this.previousFocus = null;
    }

    private onInput(e: Event): void {
        const target = e.target;

        if (!(target instanceof HTMLInputElement)) {
            return;
        }

        const search = target.value.trim();

        if (search === "") {
            this.items.forEach((item) => item.hide());
            this.matchedItems = [];
            this.activeItem = null;
            this.input.setAttribute("aria-activedescendant", "");

            if (this.status) {
                this.status.textContent = "Search cleared";
            }

            return;
        }

        const regexp = this.buildLooseRegexp(search);
        this.matchedItems = this.items.filter((item) => item.match(regexp));

        if (this.status) {
            const n = this.matchedItems.length;
            this.status.textContent = n === 0 ? "No results" : `${n} result${n > 1 ? "s" : ""}`;
        }

        if (this.matchedItems.length > 0) {
            this.setActiveIndex(0);
        } else {
            this.activeItem = null;
            this.input.setAttribute("aria-activedescendant", "");
        }
    }

    private buildLooseRegexp(search: string): RegExp {
        const escaped = Array.from(search).map((ch) =>
            ch.replace(/[.*+?^${}()|[\]\\]/g, "\\$&")
        );

        let pattern = "^(.*)";

        for (const ch of escaped) {
            pattern += `(${ch})(.*)`;
        }

        pattern += "$";

        return new RegExp(pattern, "i");
    }

    private setActiveIndex(n: number): void {
        if (!this.matchedItems.length) {
            this.input.setAttribute("aria-activedescendant", "");
            this.activeItem = null;
            return;
        }

        if (this.activeItem) {
            this.activeItem.unactiveElement();
        }

        if (n >= this.matchedItems.length) {
            n = 0;
        }

        if (n < 0) {
            n = this.matchedItems.length - 1;
        }

        const item = this.matchedItems[n];
        item.activeElement();
        this.activeItem = item;

        this.input.setAttribute("aria-activedescendant", item.id);
    }

    private inputShortcutHandler(e: KeyboardEvent): void {
        if (e.key === "Escape") {
            e.preventDefault();
            this.hide();

            this.input.value = "";
            this.items.forEach((item) => item.hide());
            this.matchedItems = [];
            this.activeItem = null;
            this.input.setAttribute("aria-activedescendant", "");

            if (this.status) {
                this.status.textContent = "Reset search";
            }

            return;
        }

        if (!this.matchedItems.length || !this.activeItem) {
            return;
        }

        if (e.key === "ArrowDown") {
            e.preventDefault();
            const index = this.matchedItems.findIndex((it) => it === this.activeItem);
            this.setActiveIndex(index + 1);
            return;
        }

        if (e.key === "ArrowUp") {
            e.preventDefault();
            const index = this.matchedItems.findIndex((it) => it === this.activeItem);
            this.setActiveIndex(index - 1);
            return;
        }

        if (e.key === "Enter") {
            e.preventDefault();
            this.activeItem.follow();
        }
    }
}