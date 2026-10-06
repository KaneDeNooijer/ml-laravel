/**
 * Native/polyfilled detector initialization and guide-region frame detection.
 */

import {
    capturePointsToVideo,
    guideCaptureRegion,
} from './geometry';
import type {
    BarcodeCorners,
    BarcodeDetectorGlobal,
    BarcodeDetectorInstance,
    Box,
    DetectedBarcode,
} from './types';

const FORMATS = ['ean_8', 'ean_13', 'upc_a', 'upc_e'] as const;
let detectorPromise: Promise<BarcodeDetectorInstance> | null = null;

/**
 * Returns the shared detector, initializing the polyfilled API when necessary.
 *
 * A failed initialization clears the cached promise so a later page load or
 * remount can try again.
 */
export async function readyDetector(): Promise<BarcodeDetectorInstance> {
    if (detectorPromise !== null) {
        return detectorPromise;
    }

    detectorPromise = Promise.resolve().then(async () => {
        const barcodeGlobal = globalThis as typeof globalThis & BarcodeDetectorGlobal;
        const Detector = barcodeGlobal.BarcodeDetector;

        if (Detector === undefined) {
            throw new Error('BarcodeDetector is unavailable.');
        }

        const supportedFormats = await Detector.getSupportedFormats();
        const formats = FORMATS.filter((format) => supportedFormats.includes(format));

        return new Detector({ formats });
    }).catch((error: unknown) => {
        detectorPromise = null;

        throw error;
    });

    return detectorPromise;
}

/**
 * Captures the guide region and returns detector data in source-video pixels.
 *
 * Results without the four corner points required by the rotating overlay are
 * ignored instead of being passed into geometry that cannot represent them.
 *
 * @param detector Active native or polyfilled barcode detector.
 * @param video Live camera video.
 * @param canvas Reusable off-screen detection canvas.
 * @param context Drawing context belonging to the detection canvas.
 * @param guide Visible guide geometry in scanner coordinates.
 */
export async function detectBarcode(
    detector: BarcodeDetectorInstance,
    video: HTMLVideoElement,
    canvas: HTMLCanvasElement,
    context: CanvasRenderingContext2D,
    guide: Box,
): Promise<DetectedBarcode | null> {
    const display = { width: video.clientWidth, height: video.clientHeight };
    const videoDimensions = { width: video.videoWidth, height: video.videoHeight };
    const capture = guideCaptureRegion(guide, display, videoDimensions, 960);

    if (canvas.width !== capture.outputWidth || canvas.height !== capture.outputHeight) {
        canvas.width = capture.outputWidth;
        canvas.height = capture.outputHeight;
    }

    context.drawImage(
        video,
        capture.left,
        capture.top,
        capture.width,
        capture.height,
        0,
        0,
        capture.outputWidth,
        capture.outputHeight,
    );

    const [barcode] = await detector.detect(canvas);

    if (barcode === undefined || barcode.cornerPoints.length !== 4) {
        return null;
    }

    return {
        value: barcode.rawValue,
        format: barcode.format,
        corners: capturePointsToVideo(barcode.cornerPoints as BarcodeCorners, capture),
    };
}
