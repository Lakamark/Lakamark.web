/**
 * Remove a key in the object like a filter
 * Without muted the original object input
 *
 * @param {object} object
 * @param {string} key
 * @return {Omit<*, never>}
 */
export function removeKey(object, key) {
    const {[key]: _, ...rest} = object
    return rest;
}

/**
 * To remove many keys in an object
 *
 * Example:
 * const originalObj ={ a : 1, b: 2, c: 3};
 * const cloneObj = removeKeys(originalObj, ["a", "b"]);
 *
 * @param {obj} obj
 * @param {Array<string>} keys
 * @return {{[p: string]: unknown}}
 */
export function removeKeys(obj, keys) {
    return Object.fromEntries(
        Object.entries(obj).filter(([k]) => !keys.includes(k))
    );
}