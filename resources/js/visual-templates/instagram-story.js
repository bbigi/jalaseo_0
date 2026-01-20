/**
 * Instagram Story Templates (9:16 - 1080x1920)
 * 2 Variants: Breaking, Feature
 */

import { Dimensions, ColorPresets } from './schema.js';

const baseConfig = {
    channel: 'instagram',
    size: '9:16',
    ...Dimensions['9:16'],
};

/**
 * Variant 1: Breaking (Alert-style for urgent news)
 */
export const InstagramStoryBreaking = {
    ...baseConfig,
    id: 'ig-story-breaking',
    name: 'Breaking News',
    backgroundColor: '#000000',

    objects: [
        // Black background
        {
            type: 'rect',
            left: 0,
            top: 0,
            width: 1080,
            height: 1920,
            fill: '#000000',
            opacity: 1,
        },

        // Red alert bar (top)
        {
            type: 'rect',
            left: 0,
            top: 0,
            width: 1080,
            height: 120,
            fill: '#dc2626',
            opacity: 1,
        },

        // "BREAKING" label
        {
            type: 'text',
            left: 60,
            top: 30,
            width: 960,
            height: 60,
            text: 'BREAKING',
            fontFamily: 'Inter',
            fontSize: 42,
            fontWeight: 'bold',
            fill: '#ffffff',
            textAlign: 'left',
            letterSpacing: 4,
        },

        // Featured Image (middle section)
        {
            type: 'image',
            left: 0,
            top: 200,
            width: 1080,
            height: 1080,
            src: '{{featuredImage}}',
            opacity: 1,
        },

        // Dark gradient overlay (bottom)
        {
            type: 'rect',
            left: 0,
            top: 1100,
            width: 1080,
            height: 820,
            fill: 'linear-gradient(to bottom, transparent, #000000)',
            opacity: 1,
        },

        // Headline (bottom)
        {
            type: 'text',
            left: 60,
            top: 1400,
            width: 960,
            height: 300,
            text: '{{headline}}',
            fontFamily: 'Inter',
            fontSize: 68,
            fontWeight: 'bold',
            fill: '#ffffff',
            textAlign: 'left',
            lineHeight: 1.1,
        },

        // Swipe up arrow
        {
            type: 'text',
            left: 440,
            top: 1800,
            width: 200,
            height: 60,
            text: '▲ SWIPE UP',
            fontFamily: 'Inter',
            fontSize: 20,
            fontWeight: 'bold',
            fill: '#fbbf24',
            textAlign: 'center',
        },
    ],
};

/**
 * Variant 2: Feature (Clean vertical layout)
 */
export const InstagramStoryFeature = {
    ...baseConfig,
    id: 'ig-story-feature',
    name: 'Feature Story',
    backgroundColor: '#ffffff',

    objects: [
        // White background
        {
            type: 'rect',
            left: 0,
            top: 0,
            width: 1080,
            height: 1920,
            fill: '#ffffff',
            opacity: 1,
        },

        // Blue header section
        {
            type: 'rect',
            left: 0,
            top: 0,
            width: 1080,
            height: 500,
            fill: '#2563eb',
            opacity: 1,
        },

        // Headline (on blue background)
        {
            type: 'text',
            left: 80,
            top: 120,
            width: 920,
            height: 300,
            text: '{{headline}}',
            fontFamily: 'Inter',
            fontSize: 72,
            fontWeight: 'bold',
            fill: '#ffffff',
            textAlign: 'left',
            lineHeight: 1.15,
        },

        // Featured Image (middle)
        {
            type: 'image',
            left: 80,
            top: 550,
            width: 920,
            height: 920,
            src: '{{featuredImage}}',
            opacity: 1,
        },

        // Subheadline (below image)
        {
            type: 'text',
            left: 80,
            top: 1520,
            width: 920,
            height: 250,
            text: '{{subheadline}}',
            fontFamily: 'Inter',
            fontSize: 42,
            fontWeight: 'normal',
            fill: '#1f2937',
            textAlign: 'left',
            lineHeight: 1.4,
        },

        // Brand badge (bottom)
        {
            type: 'rect',
            left: 80,
            top: 1800,
            width: 150,
            height: 60,
            fill: '#fbbf24',
            rx: 30,
            ry: 30,
        },

        {
            type: 'text',
            left: 95,
            top: 1815,
            width: 120,
            height: 30,
            text: 'aneWP',
            fontFamily: 'Inter',
            fontSize: 24,
            fontWeight: 'bold',
            fill: '#000000',
            textAlign: 'center',
        },
    ],
};

export default [InstagramStoryBreaking, InstagramStoryFeature];
