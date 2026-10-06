import type { DrawerStateChangedDetail } from './drawer/types';

/**
 * Connects the generic drawer lifecycle to the scanner page without coupling
 * either reusable component to the other.
 */

const page = document.querySelector<HTMLElement>('[data-scanner-page]');

if (page !== null) {
    const scanner = page.querySelector<HTMLElement>('[data-barcode-scanner]');

    if (scanner === null) {
        throw new Error('Scanner page is missing its barcode scanner.');
    }

    /**
     * Pauses detection while the drawer covers the scanner, without releasing
     * the camera stream or destroying the scanner component.
     *
     * @param event Stable-state event emitted by the drawer component.
     */
    const syncScannerWithDrawer = (event: Event): void => {
        const drawerEvent = event as CustomEvent<DrawerStateChangedDetail>;

        scanner.dispatchEvent(new Event(
            drawerEvent.detail.state === 'expanded'
                ? 'barcode-scanner:disable'
                : 'barcode-scanner:enable',
        ));
    };

    page.addEventListener('drawer-state-changed', syncScannerWithDrawer);
}
