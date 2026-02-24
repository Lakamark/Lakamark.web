/**
 * ScrollTrigger
 * Utility to lock/unlock document scroll using a CSS trigger class.
 *
 * Supports multiple concurrent locks via an internal static counter.
 */
export default class ScrollTrigger {

    /**
     * Number of active scroll locks.
     *
     * @type {number}
     * @private
     */
    static lockCount = 0;

    /**
     * @param {string} triggerClass CSS class used to lock scroll
     */
    constructor(triggerClass) {
        /**
         * CSS class used to toggle scroll lock
         *
         * @type {string}
         */
        this.trigger = triggerClass;

        /**
         * Root document reference
         *
         * @type {Document}
         */
        this.rootDocument = document;
    }

    /**
     * Enable scroll if no more locks remain.
     */
    enable() {
        ScrollTrigger.lockCount--;

        if (ScrollTrigger.lockCount <= 0) {
            ScrollTrigger.lockCount = 0;

            this.rootDocument.documentElement.classList.remove(this.trigger);
            this.rootDocument.body.classList.remove(this.trigger);
        }
    }

    /**
     * Disable scroll (adds a lock).
     */
    disable() {
        ScrollTrigger.lockCount++;

        if (ScrollTrigger.lockCount === 1) {
            this.rootDocument.documentElement.classList.add(this.trigger);
            this.rootDocument.body.classList.add(this.trigger);
        }
    }
}