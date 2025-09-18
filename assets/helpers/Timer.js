/**
 * Call a function with a dealy
 *
 * @param callback
 * @param {number} delay
 * @returns {(function(): void)|*}
 */
export function debounce(callback, delay) {
    let timer = null;
    return function() {
        // Get the function context
        let context = this;

        // Get the arguments passed to the function
        let args = arguments;

        // clear all timer already in process
        clearTimeout(timer)

        // Reset a new timer
        timer = setTimeout(function () {
            callback.apply(context, args);
        }, delay);
    }
}

/**
 *
 * @param callback
 * @param {number} delay
 * @returns {(function(): void)|*}
 */
export function throttle(callback, delay) {
    // The last time when the function was called
    let last;

    let timer;

    return function() {
        let now =  +new Date();

        // Get the function context
        let context = this;

        // Get the arguments passed to the function
        let args = arguments;

        if (last && now < last + delay) {
            // clear the current timer
            clearTimeout(timer);

            // Create a new timer
            // execute the callback through the context and arguments
            timer = setTimeout(function () {
                callback.apply(context, args);
            }, delay);

            // Storage the last callback
            last = now;
        } else {
            callback.apply(context, args);

            // Store the last callback in the variable
            last = now;
        }
    }
}