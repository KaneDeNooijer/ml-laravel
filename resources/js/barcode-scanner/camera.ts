/**
 * Camera-specific constraints and media helpers for the barcode scanner.
 */

/** Preferred rear-camera resolution, frame rate and facing direction. */
export const VIDEO_CONSTRAINTS: MediaTrackConstraints = {
    facingMode: { ideal: 'environment' },
    width: { ideal: 1280, max: 1920 },
    height: { ideal: 720, max: 1080 },
    frameRate: { ideal: 30 },
};

/** Optional camera capabilities that are not yet part of every DOM type set. */
interface FocusCapabilities extends MediaTrackCapabilities {
    focusMode?: string[];
}

/**
 * Enables continuous autofocus when the active browser and camera expose it.
 *
 * @param stream Active camera stream.
 */
export async function focusCamera(stream: MediaStream): Promise<void> {
    const cameraTrack = stream.getVideoTracks().at(0);
    const capabilities = cameraTrack?.getCapabilities?.() as FocusCapabilities | undefined;

    if (!capabilities?.focusMode?.includes('continuous')) {
        return;
    }

    try {
        await cameraTrack?.applyConstraints({
            advanced: [{ focusMode: 'continuous' } as MediaTrackConstraintSet],
        });
    } catch {
        // Continuous focus is an optional browser capability.
    }
}

/**
 * Waits for a presented video frame, with an animation-frame fallback.
 *
 * @param video Live camera video.
 */
export function nextVideoFrame(video: HTMLVideoElement): Promise<void> {
    return new Promise((resolve) => {
        const videoWithCallback = video as HTMLVideoElement & {
            requestVideoFrameCallback?: (callback: () => void) => number;
        };

        if (videoWithCallback.requestVideoFrameCallback !== undefined) {
            videoWithCallback.requestVideoFrameCallback(resolve);

            return;
        }

        requestAnimationFrame(() => resolve());
    });
}

/**
 * Copies the confirmed paused frame before its camera tracks are released.
 *
 * @param video Video containing the confirmed camera frame.
 * @param canvas Canvas displayed after the stream is released.
 * @param context Drawing context belonging to the frozen-frame canvas.
 */
export function captureFrozenFrame(
    video: HTMLVideoElement,
    canvas: HTMLCanvasElement,
    context: CanvasRenderingContext2D,
): void {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
}

/**
 * Removes a previous frozen result and returns to the live video element.
 *
 * @param video Live camera video.
 * @param canvas Frozen-frame canvas.
 * @param context Drawing context belonging to the frozen-frame canvas.
 */
export function clearFrozenFrame(
    video: HTMLVideoElement,
    canvas: HTMLCanvasElement,
    context: CanvasRenderingContext2D,
): void {
    canvas.hidden = true;
    video.hidden = false;
    context.clearRect(0, 0, canvas.width, canvas.height);
}

/**
 * Stops every track belonging to a camera stream.
 *
 * @param stream Stream that is no longer owned by an active scanner run.
 */
export function releaseStream(stream: MediaStream | null): void {
    stream?.getTracks().forEach((track) => track.stop());
}
