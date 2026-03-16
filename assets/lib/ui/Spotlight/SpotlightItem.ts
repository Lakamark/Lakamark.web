export default class SpotlightItem {
    public static nextId = 0;

    public readonly id: string;
    public readonly element: HTMLLIElement;
    public readonly link: HTMLAnchorElement;
    public readonly title: string;
    public readonly href: string;

    public constructor(title: string, href: string) {
        this.title = title;
        this.href = href;

        this.element = document.createElement("li");
        this.link = document.createElement("a");

        this.id = `spotlight_option_${SpotlightItem.nextId++}`;
        this.element.id = this.id;

        this.element.setAttribute("role", "option");
        this.element.setAttribute("aria-selected", "false");

        this.link.setAttribute("href", href);
        this.link.innerText = title;

        this.element.appendChild(this.link);

        this.hide();
    }

    public match(regexp: RegExp): boolean {
        const matches = this.title.match(regexp);

        if (!matches) {
            this.hide();
            return false;
        }

        this.link.innerHTML = this.buildHighlightedHTML(matches);
        this.element.removeAttribute("hidden");

        return true;
    }

    private buildHighlightedHTML(matches: RegExpMatchArray): string {
        return matches.reduce((acc: string, match: string, index: number) => {
            if (index === 0) {
                return acc;
            }

            if (index % 2 === 0) {
                return acc + `<mark>${this.escapeHTML(match)}</mark>`;
            }

            return acc + this.escapeHTML(match);
        }, "");
    }

    private escapeHTML(str: string): string {
        const map: Record<string, string> = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            "\"": "&quot;",
            "'": "&#39;",
        };

        return str.replace(/[&<>"']/g, (char: string) => map[char] ?? char);
    }

    public hide(): void {
        this.element.setAttribute("hidden", "hidden");
        this.element.setAttribute("aria-selected", "false");
    }

    public activeElement(): void {
        this.element.classList.add("active");
        this.element.setAttribute("aria-selected", "true");
    }

    public unactiveElement(): void {
        this.element.classList.remove("active");
        this.element.setAttribute("aria-selected", "false");
    }

    public follow(): void {
        window.location.href = this.href;
    }
}