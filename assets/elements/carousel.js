export default class Carousel  extends HTMLElement{

    constructor() {
        super();
        this.root = this.attachShadow({mode:'open'});
    }
    connectedCallback() {
        console.log("Carousel connected");
    }

    disconnectedCallback() {
        console.log('Carousel deconnected');
    }

}