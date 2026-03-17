export type RadiusResult = {
    x: number;
    y: number;
    right: number;
    bottom: number;
    radius: number;
};

/**
 * To clamp a value between the min value and the max value
 *
 * @param {number} n Number input
 * @param {number} min
 * @param {number } max
 * @return {number}
 */
export function clamp(n: number, min: number, max: number): number {
    return Math.min(Math.max(n, min), max);
}

/**
 * To choose a random number in the range
 *
 * @param {number} min
 * @param {number} max
 * @return {number}
 */
export function randomNumberBetween(min: number, max: number): number {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

/**
 * Compute the max radius needed to cover the viewport
 * from the center of a target element.
 *
 */
export function getRadius(targetElement: Element): RadiusResult {
    const { top, left, width, height } = targetElement.getBoundingClientRect();
    const x = left + width / 2;
    const y = top + height / 2;

    const right = window.innerWidth - x;
    const bottom = window.innerHeight - y;

    return {
        x,
        y,
        right,
        bottom,
        radius: Math.hypot(Math.max(x, right), Math.max(y, bottom)),
    };
}