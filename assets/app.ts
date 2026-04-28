import "./css/app.scss";
import '@hotwired/turbo';
import {AppRunner} from "./core";

const app = new AppRunner([]);

document.addEventListener('turbo:load', () => {
    app.mount();
});

document.addEventListener('turbo:before-cache', () => {
    app.destroy();
});