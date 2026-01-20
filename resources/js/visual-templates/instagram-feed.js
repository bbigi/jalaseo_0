/**
 * Instagram Feed Templates (4:5 - 1080x1350)
 * 3 Variants: Modern, Bold, Minimal
 */

import { Dimensions, ColorPresets } from './schema.js';

const baseConfig = {
    channel: 'instagram',
    size: '4:5',
    ...Dimensions['4:5'],
};

/**
 * Variant 1: Modern (Blue gradient with image overlay)
 */
export const InstagramFeedModern = {
    ...baseConfig,
    id: 'ig-feed-modern',
    name: 'Modern Blue',
    backgroundColor: ColorPresets.modern.secondary,

    objects: [
        // Background gradient rectangle
        {
            type: 'rect',
            left: 0,
            top: 0,
            width: 1080,
            height: 1350,
            fill: ColorPresets.modern.primary,
            opacity: 1,
        },

        // Featured Image (top section)
        {
            type: 'image',
            left: 0,
            top: 0,
            width: 1080,
            height: 700,
            src: '{{featuredImage}}',
            opacity: 0.85,
            cropX: 0,
            cropY: 0,
        },

        // Overlay gradient
        {
            type: 'rect',
            left: 0,
            top: 550,
            width: 1080,
            height: 800,
            fill: 'linear-gradient(to bottom, transparent, #2563eb)',
            opacity: 0.9,
        },

        // Headline
        {
            type: 'text',
            left: 60,
            top: 800,
            width: 960,
            height: 200,
            text: '{{headline}}',
            fontFamily: 'Inter',
            fontSize: 72,
            fontWeight: 'bold',
            fill: '#ffffff',
            textAlign: 'left',
            lineHeight: 1.2,
        },

        // Subheadline
        {
            type: 'text',
            left: 60,
            top: 1050,
            width: 960,
            height: 100,
            text: '{{subheadline}}',
            fontFamily: 'Inter',
            fontSize: 32,
            fontWeight: 'normal',
            fill: '#f3f4f6',
            textAlign: 'left',
            lineHeight: 1.4,
        },

        // Logo/Brand (bottom right)
        {
            type: 'text',
            left: 860,
            top: 1260,
            width: 160,
            height: 40,
            text: 'aneWP',
            fontFamily: 'Inter',
            fontSize: 24,
            fontWeight: 'bold',
            fill: '#fbbf24',
            textAlign: 'right',
        },
    ],
};

/**
 * Variant 2: Bold (Red/Dark with centered text)
 */
export const InstagramFeedBold = {
    ...baseConfig,
    id: 'ig-feed-bold',
    name: 'Bold Red',
    backgroundColor: ColorPresets.bold.secondary,

    objects: [
        // Dark background
        {
            type: 'rect',
            left: 0,
            top: 0,
            width: 1080,
            height: 1350,
            fill: '#1f2937',
            opacity: 1,
        },

        // Red accent bar
        {
            type: 'rect',
            left: 0,
            top: 0,
            width: 1080,
            height: 20,
            fill: '#dc2626',
            opacity: 1,
        },

        // Headline (centered)
        {
            type: 'text',
            left: 60,
            top: 400,
            width: 960,
            height: 300,
            text: '{{headline}}',
            fontFamily: 'Inter',
            fontSize: 80,
            fontWeight: 'bold',
            fill: '#ffffff',
            textAlign: 'center',
            lineHeight: 1.1,
        },

        // Accent line
        {
            type: 'rect',
            left: 440,
            top: 750,
            width: 200,
            height: 4,
            fill: '#fbbf24',
            opacity: 1,
        },

        // Subheadline
        {
            type: 'text',
            left: 60,
            top: 800,
            width: 960,
            height: 150,
            text: '{{subheadline}}',
            fontFamily: 'Inter',
            fontSize: 36,
            fontWeight: 'normal',
            fill: '#d1d5db',
            textAlign: 'center',
            lineHeight: 1.4,
        },

        // Featured Image (bottom, cropped circle)
        {
            type: 'circle',
            left: 390,
            top: 1000,
            radius: 150,
            fill: 'pattern',
            stroke: '#dc2626',
            strokeWidth: 8,
            // Image will be clipped to circle
        },
    ],
};

/**
 * Variant 3: Minimal (Black/White clean design)
 */
export const InstagramFeedMinimal = {
    ...baseConfig,
    id: 'ig-feed-minimal',
    name: 'Minimal Clean',
    backgroundColor: '#ffffff',

    objects: [
        // White background
        {
            type: 'rect',
            left: 0,
            top: 0,
            width: 1080,
            height: 1350,
            fill: '#ffffff',
            opacity: 1,
        },

        // Featured Image (top, full width)
        {
            type: 'image',
            left: 0,
            top: 0,
            width: 1080,
            height: 800,
            src: '{{featuredImage}}',
            opacity: 1,
        },

        // Headline (on white background)
        {
            type: 'text',
            left: 80,
            top: 880,
            width: 920,
            height: 250,
            text: '{{headline}}',
            fontFamily: 'Inter',
            fontSize: 64,
            fontWeight: 'bold',
            fill: '#000000',
            textAlign: 'left',
            lineHeight: 1.2,
        },

        // Subheadline
        {
            type: 'text',
            left: 80,
            top: 1150,
            width: 920,
            height: 100,
            text: '{{subheadline}}',
            fontFamily: 'Inter',
            fontSize: 28,
            fontWeight: 'normal',
            fill: '#6b7280',
            textAlign: 'left',
            lineHeight: 1.5,
        },

        // Minimal accent dot
        {
            type: 'circle',
            left: 80,
            top: 1280,
            radius: 12,
            fill: '#000000',
        },
    ],
};

/**
 * Variant 4: News 1 (Full image with red gradient overlay + headline only)
 */
export const InstagramFeedNews1 = {
    ...baseConfig,
    id: 'ig-feed-news1',
    name: 'News 1',
    backgroundColor: '#000000',

    objects: [
        // Featured Image (full canvas)
        {
            type: 'image',
            left: 0,
            top: 0,
            width: 1080,
            height: 1350,
            src: '{{featuredImage}}',
            opacity: 1,
            cropX: 0,
            cropY: 0,
        },

        // Red gradient overlay (bottom 40%)
        {
            type: 'rect',
            left: 0,
            top: 810,
            width: 1080,
            height: 540,
            fill: 'linear-gradient(to bottom, transparent, rgba(220, 38, 38, 0.95))',
            opacity: 1,
        },

        // Headline (bottom section, white bold)
        {
            type: 'text',
            left: 60,
            top: 1000,
            width: 960,
            height: 300,
            text: '{{headline}}',
            fontFamily: 'Inter',
            fontSize: 56,
            fontWeight: 'bold',
            fill: '#ffffff',
            textAlign: 'left',
            lineHeight: 1.2,
        },
    ],
};

export default [InstagramFeedModern, InstagramFeedBold, InstagramFeedMinimal, InstagramFeedNews1];
