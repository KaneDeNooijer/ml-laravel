/**
 * The polyfill package does not currently publish TypeScript declarations.
 * Scanner-facing detector types live in types.ts; this declaration only makes
 * the side-effect import understood by the TypeScript compiler.
 */
declare module 'barcode-detector/polyfill';
