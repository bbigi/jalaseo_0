/**
 * Square Templates for Facebook & LinkedIn (1:1 - 1080x1080)
 * 2 Variants: Professional, Vibrant
 */

import { Dimensions, ColorPresets } from './schema.js';

const baseConfig = {
    size: '1:1',
    ...Dimensions['1:1'],
};

/**
 * Variant 1: Professional (LinkedIn-friendly)
 */
export const SocialSquareProfessional = {
    ...baseConfig,
    id: 'social-square-professional',
    name: 'Professional',
    channel: 'linkedin',
    backgroundColor: '#f3f4f6',

    objects: [
        // Light gray background
        {
            type: 'rect',
            left: 0,
            top: 0,
            width: 1080,
            height: 1080,
            fill: '#f3f4f6',
            opacity: 1,
        },

        // Blue accent bar (left)
        {
            type: 'rect',
            left: 0,
            top: 0,
            width: 20,
            height: 1080,
            fill: '#2563eb',
            opacity: 1,
        },

        // Featured Image (top section, rounded)
        {
            type: 'rect',
            left: 80,
            top: 80,
            width: 920,
            height: 460,
            fill: 'image',
            src: '{{featuredImage}}',
            rx: 20,
            ry: 20,
        },

        // White content card
        {
            type: 'rect',
            left: 80,
            top: 580,
            width: 920,
            height: 420,
            fill: '#ffffff',
            rx: 20,
            ry: 20,
            shadow: {
                color: 'rgba(0,0,0,0.1)',
                blur: 20,
                offsetX: 0,
                offsetY: 10,
            },
        },

        // Headline
        {
            type: 'text',
            left: 120,
            top: 640,
            width: 840,
            height: 200,
            text: '{{headline}}',
            fontFamily: 'Inter',
            fontSize: 52,
            fontWeight: 'bold',
            fill: '#1f2937',
            textAlign: 'left',
            lineHeight: 1.2,
        },

        // Subheadline
        {
            type: 'text',
            left: 120,
            top: 860,
            width: 840,
            height: 100,
            text: '{{subheadline}}',
            fontFamily: 'Inter',
            fontSize: 28,
            fontWeight: 'normal',
            fill: '#6b7280',
            textAlign: 'left',
            lineHeight: 1.4,
        },
    ],
};

/**
 * Variant 2: Vibrant (Facebook-friendly, colorful)
 */
export const SocialSquareVibrant = {
    ...baseConfig,
    id: 'social-square-vibrant',
    name: 'Vibrant',
    channel: 'facebook',
    backgroundColor: '#fbbf24',

    objects: [
        // Yellow background
        {
            type: 'rect',
            left: 0,
            top: 0,
            width: 1080,
            height: 1080,
            fill: '#fbbf24',
            opacity: 1,
        },

        // Featured Image (left half, clipped circle)
        {
            type: 'circle',
            left: 200,
            top: 540,
            radius: 320,
            fill: 'pattern',
            stroke: '#ffffff',
            strokeWidth: 12,
        },

        // White content section (right half)
        {
            type: 'rect',
            left: 540,
            top: 120,
            width: 480,
            height: 840,
            fill: '#ffffff',
            rx: 30,
            ry: 30,
        },

        // Headline (on white)
        {
            type: 'text',
            left: 580,
            top: 200,
            width: 400,
            height: 450,
            text: '{{headline}}',
            fontFamily: 'Inter',
            fontSize: 56,
            fontWeight: 'bold',
            fill: '#1f2937',
            textAlign: 'left',
            lineHeight: 1.15,
        },

        // Accent shape (decorative)
        {
            type: 'circle',
            left: 920,
            top: 700,
            radius: 80,
            fill: '#fbbf24',
            opacity: 0.3,
        },

        // Subheadline
        {
            type: 'text',
            left: 580,
            top: 720,
            width: 400,
            height: 180,
            text: '{{subheadline}}',
            fontFamily: 'Inter',
            fontSize: 26,
            fontWeight: 'normal',
            fill: '#6b7280',
            textAlign: 'left',
            lineHeight: 1.5,
        },
    ],
};

export default [SocialSquareProfessional, SocialSquareVibrant];
