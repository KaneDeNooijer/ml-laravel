/**
 * Scanner lifecycle orchestrator connecting camera, detector and outline modules.
 */

import {
    captureFrozenFrame,
    clearFrozenFrame,
    focusCamera,
    nextVideoFrame,
    releaseStream,
    VIDEO_CONSTRAINTS,
} from './camera';
import { detectBarcode, readyDetector } from './detector';
import {
    guideBox,
    measuredBarcodeBox,
    videoPointsToScreen,
} from './geometry';
import { createOutlineController } from './outline';
import type {
    BarcodeDetectorInstance,
    BarcodeScannedDetail,
    Box,
    DetectedBarcode,
    MountedScanner,
    ScannerState,
} from './types';

/**
 * Finds required component markup and fails with a useful selector when the
 * Blade contract and TypeScript implementation drift apart.
 *
 * @param root Scanner component root.
 * @param selector Selector relative to the scanner root.
 */
function requiredElement<T extends Element>(root: HTMLElement, selector: string): T {
    const element = root.querySelector<T>(selector);

    if (element === null) {
        throw new Error(`Barcode scanner element is missing: ${selector}`);
    }

    return element;
}

/**
 * Returns a required canvas context with an error that identifies its purpose.
 *
 * @param canvas Canvas that owns the requested context.
 * @param options Optional rendering-context configuration.
 */
function requiredCanvasContext(
    canvas: HTMLCanvasElement,
    options?: CanvasRenderingContext2DSettings,
): CanvasRenderingContext2D {
    const context = canvas.getContext('2d', options);

    if (context === null) {
        throw new Error('Barcode scanner could not create a 2D canvas context.');
    }

    return context;
}

/**
 * Starts and owns one scanner component.
 *
 * The scanner starts automatically and owns its camera, listeners and
 * asynchronous work until destroy is called.
 *
 * @param root Scanner component root.
 */
export function mountScanner(root: HTMLElement): MountedScanner {
    const video = requiredElement<HTMLVideoElement>(root, '[data-camera-video]');
    const frozenFrame = requiredElement<HTMLCanvasElement>(root, '[data-camera-frame]');
    const outline = requiredElement<HTMLElement>(root, '[data-barcode-outline]');
    const message = requiredElement<HTMLElement>(root, '[data-barcode-message]');
    const messageAnnouncement = requiredElement<HTMLElement>(root, '[data-barcode-message-announcement]');
    const detectionCanvas = document.createElement('canvas');
    const detectionContext = requiredCanvasContext(detectionCanvas, { willReadFrequently: true });
    const frozenFrameContext = requiredCanvasContext(frozenFrame);
    const listenerController = new AbortController();
    const outlineController = createOutlineController({
        root,
        outline,
        message,
        successRing: requiredElement<HTMLElement>(root, '[data-barcode-success-ring]'),
        overlay: requiredElement<SVGSVGElement>(root, '[data-scan-overlay]'),
        maskBackground: requiredElement<SVGRectElement>(root, '[data-scan-mask-background]'),
        maskCutout: requiredElement<SVGRectElement>(root, '[data-scan-mask-cutout]'),
        overlayBackground: requiredElement<SVGRectElement>(root, '[data-scan-overlay-background]'),
    });

    let activeStream: MediaStream | null = null;
    let enabled = true;
    let hasShownIntro = false;
    let runId = 0;

    /**
     * Updates the visual state exposed to component CSS.
     *
     * @param state New scanner state.
     */
    const setState = (state: ScannerState): void => {
        root.dataset.state = state;
        outline.hidden = !['searching', 'detected', 'success', 'complete'].includes(state);
        messageAnnouncement.textContent = state === 'detected'
            ? ''
            : ['success', 'complete'].includes(state)
                ? message.dataset.successMessage ?? ''
                : message.dataset.defaultMessage ?? '';
    };

    /**
     * Calculates the guide against the scanner's latest dimensions.
     */
    const currentGuide = (): Box => guideBox(root.clientWidth, root.clientHeight);

    /**
     * Displays the responsive guide used for both user positioning and capture.
     */
    const showGuide = (): void => {
        const guide = currentGuide();

        if (hasShownIntro) {
            outlineController.place(guide);
        } else {
            hasShownIntro = true;
            void outlineController.reveal(guide);
        }

        setState('searching');
    };

    const resizeObserver = new ResizeObserver(() => {
        if (root.dataset.state === 'searching') {
            showGuide();
        }
    });

    /**
     * Stops a stream and removes it as the active stream only when this scanner
     * still owns that exact instance.
     *
     * @param stream Stream belonging to a specific asynchronous scanner run.
     */
    const releaseOwnedStream = (stream: MediaStream | null): void => {
        releaseStream(stream);

        if (activeStream === stream) {
            activeStream = null;
        }
    };

    /**
     * Invalidates pending work, releases the camera and resets rendered media.
     */
    const stop = (): void => {
        runId += 1;
        const stream = activeStream;

        releaseOwnedStream(stream);
        video.pause();
        video.srcObject = null;
        clearFrozenFrame(video, frozenFrame, frozenFrameContext);
        outlineController.reset();
    };

    /**
     * Converts detected corners to screen coordinates and morphs the guide onto
     * the barcode's padded position and rotation.
     *
     * @param barcode Confirmed detector result in source-video coordinates.
     */
    const placeDetectedBarcode = (barcode: DetectedBarcode): Promise<void> => {
        const display = { width: video.clientWidth, height: video.clientHeight };
        const videoDimensions = { width: video.videoWidth, height: video.videoHeight };
        const points = videoPointsToScreen(barcode.corners, display, videoDimensions);
        const measured = measuredBarcodeBox(points);

        return outlineController.animateTo(measured.box, measured.angle);
    };

    /**
     * Publishes the confirmed barcode, freezes the frame and runs the success
     * sequence. The event fires first so consumers can begin their lookup while
     * the visual confirmation is playing.
     *
     * @param barcode Confirmed detector result.
     * @param stream Stream belonging to the confirming scanner run.
     * @param run Captured asynchronous run identifier.
     */
    const finish = async (
        barcode: DetectedBarcode,
        stream: MediaStream,
        run: number,
    ): Promise<void> => {
        if (run !== runId || activeStream !== stream) {
            return;
        }

        root.dispatchEvent(new CustomEvent<BarcodeScannedDetail>('barcode-scanned', {
            bubbles: true,
            detail: {
                barcode: barcode.value,
                format: barcode.format,
                cornerPoints: barcode.corners.map((point) => ({ ...point })),
            },
        }));

        if (run !== runId || activeStream !== stream) {
            return;
        }

        captureFrozenFrame(video, frozenFrame, frozenFrameContext);
        const outlineAnimation = placeDetectedBarcode(barcode);

        frozenFrame.hidden = false;
        video.hidden = true;
        releaseOwnedStream(stream);
        video.srcObject = null;
        setState('detected');
        await outlineAnimation;

        if (run !== runId) {
            return;
        }

        setState('success');
        await outlineController.animateSuccess();

        if (run === runId) {
            setState('complete');
        }
    };

    /**
     * Searches successive frames and verifies the first result once more on a
     * paused frame before accepting it.
     *
     * @param detector Active native or polyfilled barcode detector.
     * @param stream Stream belonging to the active scanner run.
     * @param run Captured asynchronous run identifier.
     */
    const scan = async (
        detector: BarcodeDetectorInstance,
        stream: MediaStream,
        run: number,
    ): Promise<void> => {
        while (activeStream === stream && run === runId) {
            const barcode = await detectBarcode(
                detector,
                video,
                detectionCanvas,
                detectionContext,
                currentGuide(),
            );

            if (run !== runId || activeStream !== stream) {
                return;
            }

            if (barcode === null) {
                await nextVideoFrame(video);

                continue;
            }

            video.pause();
            const confirmedBarcode = await detectBarcode(
                detector,
                video,
                detectionCanvas,
                detectionContext,
                currentGuide(),
            );

            if (run !== runId || activeStream !== stream) {
                return;
            }

            if (confirmedBarcode === null || confirmedBarcode.value !== barcode.value) {
                await video.play();

                continue;
            }

            await finish(confirmedBarcode, stream, run);

            return;
        }
    };

    /**
     * Requests camera access and begins scanning for the current component run.
     */
    const start = async (): Promise<void> => {
        if (!enabled) {
            return;
        }

        stop();
        const run = ++runId;
        setState('starting');

        try {
            const detector = await readyDetector();

            if (run !== runId) {
                return;
            }

            const stream = await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: VIDEO_CONSTRAINTS,
            });

            if (run !== runId) {
                releaseStream(stream);

                return;
            }

            activeStream = stream;
            clearFrozenFrame(video, frozenFrame, frozenFrameContext);
            video.srcObject = stream;
            await video.play();

            if (run !== runId || activeStream !== stream) {
                releaseOwnedStream(stream);

                return;
            }

            await focusCamera(stream);

            if (run !== runId || activeStream !== stream) {
                releaseOwnedStream(stream);

                return;
            }

            showGuide();
            await scan(detector, stream, run);
        } catch (error: unknown) {
            if (run !== runId) {
                return;
            }

            console.error('[barcode-scanner]', error);
            stop();
            setState('error');
        }
    };

    /**
     * Pauses scanning without releasing the camera or unmounting the component.
     */
    const disable = (): void => {
        if (!enabled) {
            return;
        }

        enabled = false;
        runId += 1;
        setState('disabled');
    };

    /**
     * Resumes a scanner that was paused through disable, reusing its active
     * camera stream when one is still available.
     */
    const enable = (): void => {
        if (enabled) {
            return;
        }

        enabled = true;

        if (activeStream === null) {
            void start();

            return;
        }

        const stream = activeStream;
        const run = ++runId;

        void Promise.resolve().then(async () => {
            try {
                const detector = await readyDetector();
                await video.play();

                if (run !== runId || activeStream !== stream) {
                    return;
                }

                showGuide();
                await scan(detector, stream, run);
            } catch (error: unknown) {
                if (run !== runId) {
                    return;
                }

                console.error('[barcode-scanner]', error);
                stop();
                setState('error');
            }
        });
    };

    window.addEventListener('pagehide', stop, { signal: listenerController.signal });
    root.addEventListener('barcode-scanner:disable', disable, { signal: listenerController.signal });
    root.addEventListener('barcode-scanner:enable', enable, { signal: listenerController.signal });

    resizeObserver.observe(root);
    void start();

    return {
        enable,
        disable,

        /**
         * Releases all component resources and allows the root to be mounted again.
         */
        destroy(): void {
            enabled = false;
            stop();
            listenerController.abort();
            resizeObserver.disconnect();
        },
    };
}
