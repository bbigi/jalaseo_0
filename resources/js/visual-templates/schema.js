/**
 * Visual Template JSON Schema
 *
 * Each template is a JSON object that Fabric.js can render
 * Templates support variable replacement from ContentDNA
 */

export const TemplateSchema = {
    // Template metadata
    id: 'string',              // Unique template ID
    name: 'string',            // Display name
    size: 'string',            // "4:5", "9:16", "1:1"
    width: 'number',           // Pixel width (e.g., 1080)
    height: 'number',          // Pixel height (e.g., 1350)
    channel: 'string',         // "instagram", "facebook", "linkedin"

    // Fabric.js canvas configuration
    backgroundColor: 'string', // Hex color

    // Layers (Fabric.js objects)
    objects: [
        {
            type: 'rect|circle|text|image',

            // Common properties
            left: 'number',
            top: 'number',
            width: 'number',
            height: 'number',
            fill: 'string',
            opacity: 'number',

            // Text-specific
            text: 'string',           // Supports {{headline}}, {{subheadline}} variables
            fontFamily: 'string',
            fontSize: 'number',
            fontWeight: 'string',
            textAlign: 'string',
            lineHeight: 'number',

            // Image-specific
            src: 'string',            // "{{featuredImage}}" for dynamic image
            cropX: 'number',
            cropY: 'number',
        }
    ],

    // Variable mappings (ContentDNA → Template)
    variables: {
        headline: 'string',
        subheadline: 'string',
        featuredImage: 'string',
        keywords: 'array',
    }
};

/**
 * Template Categories
 */
export const TemplateCategories = {
    INSTAGRAM_FEED: 'instagram_feed',      // 4:5 (1080x1350)
    INSTAGRAM_STORY: 'instagram_story',    // 9:16 (1080x1920)
    FACEBOOK_POST: 'facebook_post',        // 1:1 (1080x1080)
    LINKEDIN_POST: 'linkedin_post',        // 1:1 (1080x1080)
};

/**
 * Standard Dimensions
 */
export const Dimensions = {
    '4:5': { width: 1080, height: 1350 },
    '9:16': { width: 1080, height: 1920 },
    '1:1': { width: 1080, height: 1080 },
};

/**
 * Color Presets (for template variants)
 */
export const ColorPresets = {
    modern: {
        primary: '#2563eb',    // Blue
        secondary: '#f3f4f6',  // Light Gray
        accent: '#fbbf24',     // Yellow
        text: '#1f2937',       // Dark Gray
    },
    bold: {
        primary: '#dc2626',    // Red
        secondary: '#1f2937',  // Dark Gray
        accent: '#fbbf24',     // Yellow
        text: '#ffffff',       // White
    },
    minimal: {
        primary: '#000000',    // Black
        secondary: '#ffffff',  // White
        accent: '#6b7280',     // Gray
        text: '#1f2937',       // Dark Gray
    },
};
