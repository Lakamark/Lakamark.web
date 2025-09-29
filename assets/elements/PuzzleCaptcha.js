import {clamp, randomNumberBetween} from "../helpers/MathOperations.js";

export default class PuzzleCaptcha  extends HTMLElement {

    constructor() {
        super();
    }

    connectedCallback() {
        const width =  parseInt(this.getAttribute('width'), 10);
        const height =  parseInt(this.getAttribute('height'), 10);
        const pieceWith =  parseInt(this.getAttribute('piece-width'), 10);
        const pieceHeight =  parseInt(this.getAttribute('piece-height'), 10);
        const background =  this.getAttribute('src');
        const maxX = width - pieceWith
        const maxY = height - pieceHeight

        // Init CSS variables
        this.classList.add("captcha");
        this.classList.add("captcha-waiting-interaction");
        this.style.setProperty('--width', `${width}px`);
        this.style.setProperty('--height', `${height}px`);
        this.style.setProperty('--piece-width', `${pieceWith}px`);
        this.style.setProperty('--piece-height', `${pieceHeight}px`);
        this.style.setProperty('--image', `url('${background}')`);

        // Select the hidden input
        const input = this.querySelector('input');

        // Create the puzzle element
        const piece = document.createElement("div");
        piece.classList.add("captcha__piece");
        this.appendChild(piece);

        // The user is dragging an element
        let isDragging = false;
        let position = {
            x: randomNumberBetween(0, maxX),
            y: randomNumberBetween(0, maxY),
        }

        // Place the hold and the piece in random position in the puzzle
        piece.style.setProperty('transform', `translate(${position.x}px, ${position.y}px)`);

        piece.addEventListener('pointerdown', e => {
            isDragging = true
            document.body.style.setProperty('user-select', 'none')
            this.classList.remove('captcha-waiting-interaction')
            piece.classList.add('is-moving')

            window.addEventListener(
                'pointerup',
                () => {
                    document.body.style.removeProperty('user-select')
                    piece.classList.remove('is-moving')
                    isDragging = false
                }, {once: true}
            )
        })

        this.addEventListener('pointermove', (e) => {
            if (!isDragging) {
                return;
            }
            position.x = clamp(position.x + e.movementX, 0, maxX);
            position.y = clamp(position.y + e.movementY, 0, maxY);
            piece.style.setProperty('transform', `translate(${position.x}px, ${position.y}px)`)

            // Field the hidden input with piece positions (x, y)
            input.value = `${position.x}-${position.y}`;
        })
    }
}