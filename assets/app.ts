import "./css/app.scss";
import "@hotwired/turbo";

import { createFrontendApp } from "./bootstrap/createFrontendApp";
import {bootstrapModules} from "./modules";

const app = createFrontendApp();
app.registerModules(bootstrapModules());

document.addEventListener("turbo:before-render", () => {
    app.destroy();
});

document.addEventListener("turbo:load", () => {
    app.boot();
});