import type { DrawerState, DrawerStateChangedDetail } from './types';

/**
 * Browser behavior for the reusable Blade drawer component.
 *
 * The module owns pointer, click and keyboard interactions while consumers can
 * react to stable positions through the drawer-state-changed event.
 */

const HANDLE_HEIGHT = 40;
const STATES: readonly DrawerState[] = ['expanded', 'split', 'collapsed'];

/**
 * Finds required drawer markup and reports a useful selector if Blade and
 * TypeScript drift apart.
 *
 * @param root Drawer component root.
 * @param selector Selector relative to the drawer root.
 */
function requiredElement<T extends Element>(root: HTMLElement, selector: string): T {
    const element = root.querySelector<T>(selector);

    if (element === null) {
        throw new Error(`Drawer component element is missing: ${selector}`);
    }

    return element;
}

/**
 * Determines whether a data attribute contains a supported drawer state.
 *
 * @param value Untrusted state read from component markup.
 */
function isDrawerState(value: string | undefined): value is DrawerState {
    return STATES.includes(value as DrawerState);
}

/**
 * Mounts the drag, click and keyboard interactions for a drawer component.
 *
 * @param root Drawer component root.
 */
function mountDrawer(root: HTMLElement): void {
    const background = requiredElement<HTMLElement>(root, '[data-drawer-background]');
    const panel = requiredElement<HTMLElement>(root, '[data-drawer-panel]');
    const handle = requiredElement<HTMLButtonElement>(root, '[data-drawer-handle]');

    let pointerStartY = 0;
    let drawerStartTop = 0;
    let lastPointerY = 0;
    let didDrag = false;

    /**
     * Returns the current stable state, falling back to split for invalid
     * hand-authored component attributes.
     */
    const currentState = (): DrawerState => (
        isDrawerState(root.dataset.drawerState) ? root.dataset.drawerState : 'split'
    );

    /**
     * Applies one stable position and announces the change to consumers.
     *
     * @param state New stable drawer position.
     */
    const setState = (state: DrawerState): void => {
        panel.style.removeProperty('top');
        background.style.removeProperty('height');
        root.dataset.drawerState = state;
        handle.setAttribute('aria-expanded', state === 'expanded' ? 'true' : 'false');
        root.dispatchEvent(new CustomEvent<DrawerStateChangedDetail>('drawer-state-changed', {
            bubbles: true,
            detail: { state },
        }));
    };

    /**
     * Returns the stable panel offsets for the current component height.
     */
    const snapPoints = (): Record<DrawerState, number> => ({
        expanded: 0,
        split: root.clientHeight / 3,
        collapsed: root.clientHeight - HANDLE_HEIGHT,
    });

    /**
     * Finds the nearest stable state for a panel offset.
     *
     * @param top Panel top offset in pixels.
     */
    const closestState = (top: number): DrawerState => {
        const points = snapPoints();

        return [...STATES].sort(
            (first, second) => Math.abs(points[first] - top) - Math.abs(points[second] - top),
        )[0];
    };

    /**
     * Starts an exclusive pointer drag from the drawer handle.
     *
     * @param event Pointer event originating from the handle.
     */
    const beginDrag = (event: PointerEvent): void => {
        pointerStartY = event.clientY;
        lastPointerY = event.clientY;
        drawerStartTop = panel.getBoundingClientRect().top - root.getBoundingClientRect().top;
        didDrag = false;
        root.dataset.drawerDragging = 'true';
        handle.setPointerCapture(event.pointerId);
    };

    /**
     * Moves the panel and background boundary while dragging.
     *
     * @param event Captured pointer movement.
     */
    const drag = (event: PointerEvent): void => {
        if (!handle.hasPointerCapture(event.pointerId)) {
            return;
        }

        const points = snapPoints();
        const requestedTop = drawerStartTop + event.clientY - pointerStartY;
        const top = Math.min(points.collapsed, Math.max(points.expanded, requestedTop));

        lastPointerY = event.clientY;
        didDrag ||= Math.abs(lastPointerY - pointerStartY) >= 8;
        panel.style.top = `${top}px`;
        background.style.height = `${top}px`;
    };

    /**
     * Snaps the drawer after a drag and respects a deliberate swipe direction.
     *
     * @param event Pointer event ending the captured interaction.
     */
    const endDrag = (event: PointerEvent): void => {
        if (root.dataset.drawerDragging !== 'true') {
            return;
        }

        const movement = lastPointerY - pointerStartY;
        const currentTop = Number.parseFloat(panel.style.top) || drawerStartTop;
        let state = closestState(currentTop);

        if (Math.abs(movement) >= 48) {
            const currentIndex = STATES.indexOf(currentState());
            const direction = movement > 0 ? 1 : -1;
            const targetIndex = Math.min(STATES.length - 1, Math.max(0, currentIndex + direction));

            state = STATES[targetIndex];
        }

        delete root.dataset.drawerDragging;

        if (handle.hasPointerCapture(event.pointerId)) {
            handle.releasePointerCapture(event.pointerId);
        }

        setState(state);
    };

    /**
     * Moves between stable states with the keyboard.
     *
     * @param event Keyboard input received by the drawer handle.
     */
    const moveWithKeyboard = (event: KeyboardEvent): void => {
        const currentIndex = STATES.indexOf(currentState());

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            setState(STATES[Math.max(0, currentIndex - 1)]);
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setState(STATES[Math.min(STATES.length - 1, currentIndex + 1)]);
        }
    };

    handle.addEventListener('pointerdown', beginDrag);
    handle.addEventListener('pointermove', drag);
    handle.addEventListener('pointerup', endDrag);
    handle.addEventListener('pointercancel', endDrag);
    handle.addEventListener('keydown', moveWithKeyboard);
    handle.addEventListener('click', (event: MouseEvent) => {
        if (didDrag) {
            event.preventDefault();
            didDrag = false;

            return;
        }

        const state = currentState();

        setState(state === 'expanded' ? 'split' : state === 'split' ? 'expanded' : 'split');
    });
}

document.querySelectorAll<HTMLElement>('[data-drawer]').forEach(mountDrawer);
