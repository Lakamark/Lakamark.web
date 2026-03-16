<<<<<<< HEAD
console.log("app entrypoint loaded");
=======
import "./css/app.scss";
import "@hotwired/turbo";

import { createFrontendApp } from "@bootstrap/createFrontendApp";
import {boostrapModules} from "@/modules";

const app = createFrontendApp();
app.registerModules(boostrapModules());

document.addEventListener("turbo:before-render", () => {
    app.destroy();
});

document.addEventListener("turbo:load", () => {
    app.boot();
});
>>>>>>> b0d7fe3 (release: bump version to 2.0.0 (major architecture changes))
