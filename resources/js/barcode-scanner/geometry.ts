/**
 * Pure coordinate transformations shared by capture and overlay rendering.
 */

import type {
    BarcodeCorners,
    Box,
    CaptureRegion,
    Dimensions,
    Point,
} from './types';

// Geometry stays independent from the DOM so it can be verified without a browser.

const MAX_GUIDE_WIDTH = 520;
const MAX_GUIDE_HEIGHT = 180;

/**
 * Calculates the visible scanning guide for the current scanner dimensions.
 *
 * Compact portrait screens receive a wider 2.6:1 barcode-shaped guide. Larger
 * and landscape containers retain the desktop proportions without allowing
 * either dimension to become excessive on a large scanner surface.
 *
 * @param width Scanner width in CSS pixels.
 * @param height Scanner height in CSS pixels.
 */
export function guideBox(width: number, height: number): Box {
    const usesCompactPortraitGuide = width <= 640 && height > width;
    const usesShortGuide = height / width < 0.75;
    const guideWidth = Math.min(
        width * (usesCompactPortraitGuide ? 0.86 : 0.64),
        MAX_GUIDE_WIDTH,
    );
    const guideHeight = usesCompactPortraitGuide
        ? Math.min(guideWidth / 2.6, height * 0.24, MAX_GUIDE_HEIGHT)
        : Math.min(guideWidth / 2.2, height * 0.42, MAX_GUIDE_HEIGHT);
    let guideY = height * 0.26;

    if (usesShortGuide) {
        guideY = (height - guideHeight) / 2;
    } else if (usesCompactPortraitGuide) {
        guideY = (height - guideHeight) * 0.38;
    }

    return {
        x: (width - guideWidth) / 2,
        y: guideY,
        width: guideWidth,
        height: guideHeight,
    };
}

/**
 * Describes how an object-cover video is scaled and cropped in its container.
 *
 * @param displayWidth Video element width in CSS pixels.
 * @param displayHeight Video element height in CSS pixels.
 * @param videoWidth Intrinsic video width in pixels.
 * @param videoHeight Intrinsic video height in pixels.
 */
export function coverTransform(
    displayWidth: number,
    displayHeight: number,
    videoWidth: number,
    videoHeight: number,
): { scale: number; offsetX: number; offsetY: number } {
    const scale = Math.max(displayWidth / videoWidth, displayHeight / videoHeight);

    return {
        scale,
        offsetX: (displayWidth - (videoWidth * scale)) / 2,
        offsetY: (displayHeight - (videoHeight * scale)) / 2,
    };
}

/**
 * Converts the on-screen guide into the source-video region sent to the detector.
 *
 * @param guide Guide position in scanner coordinates.
 * @param display Video element dimensions in CSS pixels.
 * @param video Intrinsic video dimensions.
 * @param maxCaptureWidth Maximum detector canvas width.
 */
export function guideCaptureRegion(
    guide: Box,
    display: Dimensions,
    video: Dimensions,
    maxCaptureWidth: number,
): CaptureRegion {
    const transform = coverTransform(display.width, display.height, video.width, video.height);
    const left = Math.max(0, (guide.x - transform.offsetX) / transform.scale);
    const top = Math.max(0, (guide.y - transform.offsetY) / transform.scale);
    const right = Math.min(video.width, (guide.x + guide.width - transform.offsetX) / transform.scale);
    const bottom = Math.min(video.height, (guide.y + guide.height - transform.offsetY) / transform.scale);
    const width = right - left;
    const height = bottom - top;
    const outputScale = Math.min(1, maxCaptureWidth / width);

    return {
        left,
        top,
        width,
        height,
        outputScale,
        outputWidth: Math.round(width * outputScale),
        outputHeight: Math.round(height * outputScale),
    };
}

/**
 * Maps detector points from the cropped canvas back to source-video pixels.
 *
 * @param points Points returned by BarcodeDetector.
 * @param capture Capture region used for detection.
 */
export function capturePointsToVideo(points: BarcodeCorners, capture: CaptureRegion): BarcodeCorners {
    return points.map((point) => ({
        x: capture.left + (point.x / capture.outputScale),
        y: capture.top + (point.y / capture.outputScale),
    })) as BarcodeCorners;
}

/**
 * Maps source-video points onto the object-cover video element.
 *
 * @param points Points in source-video pixels.
 * @param display Video element dimensions in CSS pixels.
 * @param video Intrinsic video dimensions.
 */
export function videoPointsToScreen(
    points: BarcodeCorners,
    display: Dimensions,
    video: Dimensions,
): BarcodeCorners {
    const transform = coverTransform(display.width, display.height, video.width, video.height);

    return points.map((point) => ({
        x: transform.offsetX + (point.x * transform.scale),
        y: transform.offsetY + (point.y * transform.scale),
    })) as BarcodeCorners;
}

/**
 * Builds a padded, rotated outline from four barcode corner points.
 *
 * Corner order must be top-left, top-right, bottom-right, bottom-left.
 *
 * @param points Barcode corners on screen.
 */
export function measuredBarcodeBox(points: BarcodeCorners): { box: Box; angle: number } {
    const [topLeft, topRight, bottomRight, bottomLeft] = points;
    const distance = (first: Point, second: Point): number => Math.hypot(
        second.x - first.x,
        second.y - first.y,
    );
    const barcodeWidth = (distance(topLeft, topRight) + distance(bottomLeft, bottomRight)) / 2;
    const barcodeHeight = (distance(topLeft, bottomLeft) + distance(topRight, bottomRight)) / 2;
    const padding = Math.min(40, Math.max(18, Math.min(barcodeWidth, barcodeHeight) * 0.38));
    const width = barcodeWidth + (padding * 2);
    const height = barcodeHeight + (padding * 2);
    const center = points.reduce((total, point) => ({
        x: total.x + (point.x / points.length),
        y: total.y + (point.y / points.length),
    }), { x: 0, y: 0 });

    return {
        box: {
            x: center.x - (width / 2),
            y: center.y - (height / 2),
            width,
            height,
        },
        angle: Math.atan2(topRight.y - topLeft.y, topRight.x - topLeft.x),
    };
}
