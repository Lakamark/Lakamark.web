/**
 * To clamp a value between the min value and the max value
 *
 * @param {number} n Number input
 * @param {number} min
 * @param {number } max
 * @return {number}
 */
export function clamp(n, min, max) {
    return Math.min(Math.max(n, min), max);
}

/**
 * To choose a random number in the range
 *
 * @param {number} min
 * @param {number} max
 * @return {number}
 */
export function randomNumberBetween(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

/**
 * Compute the max radius needed to cover the viewport
 * from the center of a target element.
 *
 * @param {HTMLElement} element
 * @return {x, y, radius }
 */
export function  getRadius(element) {
    const rect = element.getBoundingClientRect();

    const x = rect.left + rect.width / 2;
    const y = rect.top + rect.height / 2;

    const radius = Math.hypot(
        Math.max(x, window.innerWidth - x),
        Math.max(y, window.innerHeight - y)
    );

    return { x, y, radius };
}