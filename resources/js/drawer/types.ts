/**
 * Shared contracts for the reusable drawer component and its consumers.
 */

/** Stable positions supported by the drawer. */
export type DrawerState = 'expanded' | 'split' | 'collapsed';

/** Event payload emitted after the drawer settles in a stable position. */
export interface DrawerStateChangedDetail {
    state: DrawerState;
}
