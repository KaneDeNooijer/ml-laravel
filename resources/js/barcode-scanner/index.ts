/**
 * Browser entry point for the reusable barcode-scanner Blade component.
 */

import 'barcode-detector/polyfill';
import { mountScanner } from './scanner';

/**
 * Mounts the single barcode scanner rendered in the current document.
 */
const mountBarcodeScanner = (): void => {
    const root = document.querySelector<HTMLElement>('[data-barcode-scanner]');

    if (root !== null) {
        mountScanner(root);
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountBarcodeScanner, { once: true });
} else {
    mountBarcodeScanner();
}

export { mountScanner };
