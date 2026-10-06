/**
 * Synchronized HTML border and SVG cutout rendering for scanner feedback.
 */

import type { Box } from './types';

/** DOM elements required by the outline renderer. */
interface OutlineElements {
    root: HTMLElement;
    outline: HTMLElement;
    message: HTMLElement;
    successRing: HTMLElement;
    overlay: SVGSVGElement;
    maskBackground: SVGRectElement;
    maskCutout: SVGRectElement;
    overlayBackground: SVGRectElement;
}

/** Public operations exposed to the scanner orchestrator. */
export interface OutlineController {
    place(box: Box, angle?: number): void;
    reveal(box: Box): Promise<void>;
    animateTo(box: Box, angle: number): Promise<void>;
    animateSuccess(): Promise<void>;
    cancel(): void;
    reset(): void;
}

/**
 * Owns the visible outline and SVG cutout as one geometry. Keeping these layers
 * together prevents their transitions from drifting apart.
 *
 * @param elements DOM elements used to render the scanner outline.
 */
export function createOutlineController(elements: OutlineElements): OutlineController {
    const {
        root,
        outline,
        message,
        successRing,
        overlay,
        maskBackground,
        maskCutout,
        overlayBackground,
    } = elements;

    let animationFrame: number | null = null;
    let animationResolver: (() => void) | null = null;
    let placedBox: Box | null = null;
    let placedAngle = 0;
    let messageMaxWidth = 0;

    /**
     * Anchors the persistent instruction below the normal scanning guide while
     * preventing it from crossing the scanner's horizontal edge. Detection and
     * success animations intentionally do not update this anchor.
     *
     * @param box Current outline geometry.
     * @param angle Rotation in radians.
     */
    const positionMessage = (box: Box, angle: number): void => {
        const centerX = box.x + (box.width / 2);
        const centerY = box.y + (box.height / 2);
        const rotatedHalfHeight = (Math.abs(box.width * Math.sin(angle))
            + Math.abs(box.height * Math.cos(angle))) / 2;

        message.style.maxWidth = `${Math.min(messageMaxWidth || box.width, root.clientWidth - 32)}px`;
        message.hidden = false;

        const halfMessageWidth = message.offsetWidth / 2;
        const messageCenterX = Math.min(
            root.clientWidth - halfMessageWidth - 16,
            Math.max(halfMessageWidth + 16, centerX),
        );

        message.style.left = `${messageCenterX}px`;
        message.style.top = `${centerY + rotatedHalfHeight + 16}px`;
    };

    /**
     * Keeps the dimmed SVG surround aligned with the visible outline.
     *
     * @param box Current outline geometry.
     * @param angle Rotation in radians.
     */
    const syncOverlay = ({ x, y, width, height }: Box, angle = 0): void => {
        const centerX = x + (width / 2);
        const centerY = y + (height / 2);
        const dimensions = { width: root.clientWidth, height: root.clientHeight };

        overlay.setAttribute('viewBox', `0 0 ${dimensions.width} ${dimensions.height}`);
        [maskBackground, overlayBackground].forEach((element) => {
            element.setAttribute('width', String(dimensions.width));
            element.setAttribute('height', String(dimensions.height));
        });
        maskCutout.setAttribute('x', String(x));
        maskCutout.setAttribute('y', String(y));
        maskCutout.setAttribute('width', String(width));
        maskCutout.setAttribute('height', String(height));
        maskCutout.setAttribute(
            'transform',
            `rotate(${angle * (180 / Math.PI)} ${centerX} ${centerY})`,
        );
    };

    /**
     * Renders the border and dimmed cutout from the same geometry.
     *
     * @param box Current outline geometry.
     * @param angle Rotation in radians.
     */
    const render = (box: Box, angle = 0): void => {
        outline.style.left = `${box.x}px`;
        outline.style.top = `${box.y}px`;
        outline.style.width = `${box.width}px`;
        outline.style.height = `${box.height}px`;
        outline.style.transform = `rotate(${angle}rad)`;
        syncOverlay(box, angle);
        placedBox = { ...box };
        placedAngle = angle;
    };

    /**
     * Cancels a running animation while resolving its pending promise.
     */
    const cancel = (): void => {
        if (animationFrame !== null) {
            cancelAnimationFrame(animationFrame);
            animationFrame = null;
        }

        animationResolver?.();
        animationResolver = null;
        successRing.style.opacity = '0';
        successRing.style.transform = '';
    };

    /**
     * Immediately positions and rotates the scanner outline.
     *
     * @param box Target outline geometry.
     * @param angle Rotation in radians.
     */
    const place = (box: Box, angle = 0): void => {
        cancel();
        messageMaxWidth = box.width;
        positionMessage(box, angle);
        render(box, angle);
    };

    /**
     * Reveals the initial guide by growing it from its center to the calculated
     * responsive dimensions. Reduced-motion users receive the final guide
     * immediately.
     *
     * @param box Calculated responsive guide geometry.
     */
    const reveal = (box: Box): Promise<void> => {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            place(box);

            return Promise.resolve();
        }

        cancel();
        messageMaxWidth = box.width;
        positionMessage(box, 0);
        render({
            x: box.x + (box.width / 2),
            y: box.y + (box.height / 2),
            width: 0,
            height: 0,
        });

        return animateTo(box, 0);
    };

    /**
     * Morphs both the outline and its dimmed cutout over the same frames.
     *
     * @param targetBox Target outline geometry.
     * @param targetAngle Target rotation in radians.
     */
    const animateTo = (targetBox: Box, targetAngle: number): Promise<void> => {
        cancel();

        if (placedBox === null) {
            render(targetBox, targetAngle);

            return Promise.resolve();
        }

        const startBox = { ...placedBox };
        const startAngle = placedAngle;
        const angleDifference = Math.atan2(
            Math.sin(targetAngle - startAngle),
            Math.cos(targetAngle - startAngle),
        );

        return new Promise((resolve) => {
            const startedAt = performance.now();
            animationResolver = resolve;

            const step = (now: number): void => {
                const progress = Math.min((now - startedAt) / 300, 1);
                const easedProgress = 1 - ((1 - progress) ** 3);

                render({
                    x: startBox.x + ((targetBox.x - startBox.x) * easedProgress),
                    y: startBox.y + ((targetBox.y - startBox.y) * easedProgress),
                    width: startBox.width + ((targetBox.width - startBox.width) * easedProgress),
                    height: startBox.height + ((targetBox.height - startBox.height) * easedProgress),
                }, startAngle + (angleDifference * easedProgress));

                if (progress < 1) {
                    animationFrame = requestAnimationFrame(step);

                    return;
                }

                animationFrame = null;
                animationResolver = null;
                resolve();
            };

            animationFrame = requestAnimationFrame(step);
        });
    };

    /**
     * Pulses the confirmed geometry while keeping the border, green fill and
     * surrounding SVG cutout on the same animation frames. An independent ring
     * expands from the original barcode geometry without inheriting the pulse.
     */
    const animateSuccess = (): Promise<void> => {
        cancel();

        if (placedBox === null || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return Promise.resolve();
        }

        const baseBox = { ...placedBox };
        const baseAngle = placedAngle;
        const centerX = baseBox.x + (baseBox.width / 2);
        const centerY = baseBox.y + (baseBox.height / 2);
        const keyframes = [
            { progress: 0, scale: 1 },
            { progress: 0.33, scale: 1.1 },
            { progress: 0.66, scale: 0.96 },
            { progress: 1, scale: 1 },
        ];

        successRing.style.left = `${baseBox.x}px`;
        successRing.style.top = `${baseBox.y}px`;
        successRing.style.width = `${baseBox.width}px`;
        successRing.style.height = `${baseBox.height}px`;

        return new Promise((resolve) => {
            const startedAt = performance.now();
            animationResolver = resolve;

            const step = (now: number): void => {
                const elapsed = now - startedAt;
                const progress = Math.min(elapsed / 650, 1);
                const endIndex = Math.max(
                    1,
                    keyframes.findIndex((keyframe) => progress <= keyframe.progress),
                );
                const startKeyframe = keyframes[endIndex - 1];
                const endKeyframe = keyframes[endIndex];
                const segmentProgress = (progress - startKeyframe.progress)
                    / (endKeyframe.progress - startKeyframe.progress);
                const easedProgress = (1 - Math.cos(segmentProgress * Math.PI)) / 2;
                const scale = startKeyframe.scale
                    + ((endKeyframe.scale - startKeyframe.scale) * easedProgress);
                const width = baseBox.width * scale;
                const height = baseBox.height * scale;
                const ringProgress = Math.min(elapsed / 1000, 1);
                const ringScale = 1 + (0.75 * (1 - ((1 - ringProgress) ** 3)));
                const ringOpacity = (1 - ringProgress) ** 1.4;

                render({
                    x: centerX - (width / 2),
                    y: centerY - (height / 2),
                    width,
                    height,
                }, baseAngle);
                successRing.style.opacity = String(ringOpacity);
                successRing.style.transform = `rotate(${baseAngle}rad) scale(${ringScale})`;

                if (ringProgress < 1) {
                    animationFrame = requestAnimationFrame(step);

                    return;
                }

                animationFrame = null;
                animationResolver = null;
                successRing.style.opacity = '0';
                successRing.style.transform = '';
                resolve();
            };

            animationFrame = requestAnimationFrame(step);
        });
    };

    /**
     * Clears animation state so a fresh scanner run starts from its guide.
     */
    const reset = (): void => {
        cancel();
        placedBox = null;
        placedAngle = 0;
        messageMaxWidth = 0;
        outline.style.transform = '';
        message.hidden = true;
    };

    return { place, reveal, animateTo, animateSuccess, cancel, reset };
}
