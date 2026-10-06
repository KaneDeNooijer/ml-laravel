/**
 * Shared scanner contracts kept separate from browser and rendering behavior.
 */

/** Two-dimensional coordinate in CSS or source-video pixels. */
export interface Point {
    x: number;
    y: number;
}

/** Positioned rectangle used by guides, crops and overlays. */
export interface Box extends Point {
    width: number;
    height: number;
}

/** Width and height without a position. */
export interface Dimensions {
    width: number;
    height: number;
}

/** Source-video crop and its resized detector-canvas dimensions. */
export interface CaptureRegion {
    left: number;
    top: number;
    width: number;
    height: number;
    outputScale: number;
    outputWidth: number;
    outputHeight: number;
}

/** Ordered top-left, top-right, bottom-right and bottom-left barcode corners. */
export type BarcodeCorners = [Point, Point, Point, Point];

/** Normalized result used throughout the scanner after coordinate conversion. */
export interface DetectedBarcode {
    value: string;
    format: string;
    corners: BarcodeCorners;
}

/** Minimal result contract shared by native and polyfilled detectors. */
export interface BarcodeDetectorResult {
    rawValue: string;
    format: string;
    cornerPoints: Point[];
}

/** Detector instance behavior consumed by the application. */
export interface BarcodeDetectorInstance {
    detect(source: HTMLCanvasElement): Promise<BarcodeDetectorResult[]>;
}

/** Static and constructor behavior exposed by BarcodeDetector. */
export interface BarcodeDetectorConstructor {
    new (options?: { formats?: string[] }): BarcodeDetectorInstance;
    getSupportedFormats(): Promise<string[]>;
}

/** Optional global populated by native support or the polyfill. */
export interface BarcodeDetectorGlobal {
    BarcodeDetector?: BarcodeDetectorConstructor;
}

/** Visual and lifecycle states exposed through the scanner root dataset. */
export type ScannerState =
    | 'starting'
    | 'searching'
    | 'detected'
    | 'success'
    | 'complete'
    | 'error'
    | 'disabled';

/** Lifecycle controls returned to the component entry point. */
export interface MountedScanner {
    enable(): void;
    disable(): void;
    destroy(): void;
}

/** Application event payload emitted as soon as a barcode is confirmed. */
export interface BarcodeScannedDetail {
    barcode: string;
    format: string;
    cornerPoints: Point[];
}
