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