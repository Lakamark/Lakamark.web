import "./css/app.scss";
import '@hotwired/turbo';

import {
    AppContext,
    AppRunner
} from "./core";
import {createApp} from "./application";
import {loadConfig} from "./dom";

const app: AppRunner = createApp();

const context: AppContext = {
    config: loadConfig(),
    document,
    window
}

app.mount(context);