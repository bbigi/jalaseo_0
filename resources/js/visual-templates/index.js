/**
 * Visual Template Library - Index
 *
 * Exports all templates for easy import
 */

import instagramFeedTemplates from './instagram-feed.js';
import instagramStoryTemplates from './instagram-story.js';
import socialSquareTemplates from './social-square.js';

/**
 * All available templates
 */
export const AllTemplates = [
    ...instagramFeedTemplates,
    ...instagramStoryTemplates,
    ...socialSquareTemplates,
];

/**
 * Get templates by channel and size
 */
export function getTemplates(channel, size) {
    return AllTemplates.filter(t => t.channel === channel && t.size === size);
}

/**
 * Get template by ID
 */
export function getTemplateById(id) {
    return AllTemplates.find(t => t.id === id);
}

/**
 * Get templates by size only
 */
export function getTemplatesBySize(size) {
    return AllTemplates.filter(t => t.size === size);
}

/**
 * Template categories for UI grouping
 */
export const TemplatesByCategory = {
    instagram: {
        feed: instagramFeedTemplates,
        story: instagramStoryTemplates,
    },
    facebook: socialSquareTemplates.filter(t => t.channel === 'facebook'),
    linkedin: socialSquareTemplates.filter(t => t.channel === 'linkedin'),
};

export default AllTemplates;
