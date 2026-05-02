import "./css/app.scss";
import '@hotwired/turbo';

import {createApp} from "./core/application";
import {AppRunner} from "./core";

const app: AppRunner = createApp();

app.mount();