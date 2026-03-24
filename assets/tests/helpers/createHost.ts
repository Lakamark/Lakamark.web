export function createHost(slideCount = 4): HTMLElement {
    const host = document.createElement('div');

    for (let i = 0; i < slideCount; i++) {
        const child = document.createElement('article');
        child.textContent = `Slide ${i + 1}`;
        host.appendChild(child);
    }

    document.body.appendChild(host);

    return host;
}