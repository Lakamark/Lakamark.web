/**
 * Call a function with a dealy
 *
 * @param callback
 * @param {number} delay
 * @returns {(function(): void)|*}
 */
export function debounce(callback, delay) {
    let timer = null;
    return function(...args) {
        // Get the function context
        let context = this;

        // clear all timer already in process
        clearTimeout(timer)

        // Reset a new timer
        timer = setTimeout(function () {
            callback.apply(context, args);
        }, delay);
    }
}

/**
 * To add a delay between each callback
 *
 * @param callback
 * @param {number} delay
 * @returns {(function(): void)|*}
 */
export function throttle(callback, delay) {
    // The last time when the function was called
    let last = 0;

    let timer = null;

    return function() {
        let now = Date.now();

        // Get the function context
        let context = this;

        // Get the arguments passed to the function
        let args = arguments;

        if (now - last > delay) {
            last = now;
            callback.apply(context, args)
        } else {
            clearTimeout(timer);

            timer = setTimeout(() => {
                last = Date.now();
                callback.apply(context, args)
            })
        }
    }
}