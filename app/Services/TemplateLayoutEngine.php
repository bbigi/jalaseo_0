<?php

namespace App\Services;

use App\Models\UserBranding;

class TemplateLayoutEngine
{
    protected $ratioDimensions = [
        '1:1'   => ['width' => 1080, 'height' => 1080],
        '4:5'   => ['width' => 1080, 'height' => 1350],
        '16:9'  => ['width' => 1920, 'height' => 1080],
        '4:3'   => ['width' => 1440, 'height' => 1080],
        '9:16'  => ['width' => 1080, 'height' => 1920],
    ];

    /**
     * Calculate layout from template rules + ratio + custom settings
     */
    public function calculateLayout(array $template, string $ratio, array $customSettings = [], ?UserBranding $branding = null): array
    {
        $dimensions = $this->ratioDimensions[$ratio] ?? $this->ratioDimensions['1:1'];
        $width = $dimensions['width'];
        $height = $dimensions['height'];

        // Merge default settings with custom
        $settings = array_merge($template['defaultSettings'] ?? [], $customSettings);

        // CRITICAL: Force backgroundColor to use template default (never from branding or custom)
        // This ensures accent line is always visible against light background
        if (isset($template['defaultSettings']['backgroundColor'])) {
            $settings['backgroundColor'] = $template['defaultSettings']['backgroundColor'];
        }

        // CRITICAL: Force footerTextColor to use template default (always white for contrast)
        // This ensures text is readable against dynamic footer background color
        if (isset($template['defaultSettings']['footerTextColor'])) {
            $settings['footerTextColor'] = $template['defaultSettings']['footerTextColor'];
        }

        // Apply branding if available (don't override if already set in customSettings)
        if ($branding) {
            if (empty($settings['brandLogo']) && $branding->logo) {
                $settings['brandLogo'] = asset('storage/' . $branding->logo->file_path);
            }
            $settings['primaryColor'] = $branding->primary_color;
            $settings['secondaryColor'] = $branding->secondary_color;
            $settings['defaultFont'] = $branding->default_font;

            // Dynamic color overrides for News Template 1 (if not manually customized)
            // Background stays light/neutral (don't override with brand color)
            // Only override accent line and footer with brand colors
            if (!isset($customSettings['accentLineColor'])) {
                $settings['accentLineColor'] = $branding->primary_color;
            }
            if (!isset($customSettings['footerBackgroundColor'])) {
                // Use primary color for footer (not darker, keep it vibrant)
                $settings['footerBackgroundColor'] = $branding->primary_color;
            }

            // Dynamic color overrides for News Overlay
            // Always override with brand color (same as News Template 1 pattern for accentLineColor/footerBackgroundColor)
            // User can manually override via color picker, which will be saved in canvas_state (not custom_settings)
            $settings['overlayColor'] = $branding->primary_color;
        }

        // CRITICAL: Set placeholder image if featuredImage is empty
        // This ensures image placeholder renders with correct dimensions from layoutRules
        if (empty($settings['featuredImage'])) {
            $settings['featuredImage'] = asset('images/placeholder.jpg');
        }

        $objects = [];

        foreach ($template['layoutRules'] as $key => $rule) {
            $obj = $this->processLayoutRule($rule, $width, $height, $settings);
            if ($obj) {
                $obj['objectType'] = $key;
                $objects[] = $obj;
            }
        }

        // CRITICAL: Sort objects by zIndex (low to high) for proper layering
        usort($objects, function($a, $b) {
            return ($a['zIndex'] ?? 999) <=> ($b['zIndex'] ?? 999);
        });

        return [
            'dimensions' => $dimensions,
            'objects' => $objects,
            'settings' => $settings,
        ];
    }

    /**
     * Process single layout rule to absolute pixels
     */
    protected function processLayoutRule(array $rule, int $canvasWidth, int $canvasHeight, array $settings): ?array
    {
        $obj = [
            'type' => $rule['type'],
            'zIndex' => $rule['zIndex'] ?? 1,
        ];

        // Replace template variables FIRST (recursive for nested arrays/objects)
        $rule = $this->replaceTemplateVariables($rule, $settings);

        // Calculate position based on position type
        $position = $rule['position'] ?? 'custom';

        switch ($position) {
            case 'fullscreen':
                $obj['left'] = 0;
                $obj['top'] = 0;
                $obj['width'] = $canvasWidth;
                $obj['height'] = $canvasHeight;
                break;

            case 'top':
                $obj['left'] = 0;
                $obj['top'] = 0;
                $obj['width'] = $canvasWidth;
                $obj['height'] = $this->percentToPixel($rule['heightPercent'] ?? 100, $canvasHeight);
                break;

            case 'bottom':
                $heightPx = $this->percentToPixel($rule['heightPercent'] ?? 40, $canvasHeight);
                $bottomMarginPx = isset($rule['bottomMargin']) ? $this->percentToPixel($rule['bottomMargin'], $canvasHeight) : 0;
                $obj['left'] = 0;
                $obj['top'] = $canvasHeight - $heightPx - $bottomMarginPx;
                $obj['width'] = $canvasWidth;
                $obj['height'] = $heightPx;
                break;

            case 'top-right':
                $marginPx = $this->percentToPixel($rule['marginPercent'] ?? 3.7, $canvasWidth);
                $obj['left'] = $canvasWidth - $marginPx - ($rule['maxWidthPercent'] ? $this->percentToPixel($rule['maxWidthPercent'], $canvasWidth) : 200);
                $obj['top'] = $marginPx;
                break;

            case 'top-left':
                $marginPx = $this->percentToPixel($rule['marginPercent'] ?? 3.7, $canvasWidth);
                $obj['left'] = $marginPx;
                $obj['top'] = $marginPx;
                break;

            case 'top-center':
                $marginPx = $this->percentToPixel($rule['marginPercent'] ?? 3.7, $canvasWidth);
                $logoWidth = $rule['maxWidthPercent'] ? $this->percentToPixel($rule['maxWidthPercent'], $canvasWidth) : 200;
                $obj['left'] = ($canvasWidth - $logoWidth) / 2;
                $obj['top'] = $marginPx;
                break;

            case 'bottom-right':
                $marginPx = $this->percentToPixel($rule['marginPercent'] ?? 3.7, $canvasWidth);
                $obj['left'] = $canvasWidth - $marginPx - ($rule['maxWidthPercent'] ? $this->percentToPixel($rule['maxWidthPercent'], $canvasWidth) : 200);
                $obj['top'] = $canvasHeight - $marginPx - 100;
                break;

            case 'bottom-left':
                $marginPx = $this->percentToPixel($rule['marginPercent'] ?? 3.7, $canvasWidth);
                $obj['left'] = $marginPx;
                $obj['top'] = $canvasHeight - $marginPx - 100;
                break;

            case 'custom':
                // For custom position, parse percentage-based or pixel-based values
                if (isset($rule['left'])) {
                    $obj['left'] = $this->parseValue($rule['left'], $canvasWidth);
                }
                if (isset($rule['right'])) {
                    // Calculate left from right position
                    $obj['left'] = $canvasWidth - $this->parseValue($rule['right'], $canvasWidth);
                }
                if (isset($rule['top'])) {
                    $obj['top'] = $this->parseValue($rule['top'], $canvasHeight);
                }
                if (isset($rule['bottom'])) {
                    // Calculate top from bottom position
                    $obj['top'] = $canvasHeight - $this->parseValue($rule['bottom'], $canvasHeight);
                }
                if (isset($rule['width'])) {
                    $obj['width'] = $this->parseValue($rule['width'], $canvasWidth);
                }
                if (isset($rule['height'])) {
                    $obj['height'] = $this->parseValue($rule['height'], $canvasHeight);
                }
                if (isset($rule['heightPercent'])) {
                    $obj['height'] = $this->percentToPixel($rule['heightPercent'], $canvasHeight);
                }
                if (isset($rule['widthPercent'])) {
                    $obj['width'] = $this->percentToPixel($rule['widthPercent'], $canvasWidth);
                }
                if (isset($rule['heightPx'])) {
                    $obj['height'] = $rule['heightPx'];
                }
                if (isset($rule['widthPx'])) {
                    $obj['width'] = $rule['widthPx'];
                }
                break;
        }

        // Override with percentage-based dimensions if provided (BEFORE copying other props)
        if (isset($rule['widthPercent'])) {
            $obj['width'] = $this->percentToPixel($rule['widthPercent'], $canvasWidth);
        }
        if (isset($rule['paddingPercent'])) {
            $padding = $this->percentToPixel($rule['paddingPercent'], $canvasWidth);
            $obj['left'] = $padding;
            $obj['width'] = $canvasWidth - ($padding * 2);
        }

        // Center horizontally if requested (after width is calculated)
        if (isset($rule['centerHorizontal']) && $rule['centerHorizontal'] === true && isset($obj['width'])) {
            $obj['left'] = ($canvasWidth - $obj['width']) / 2;
        }

        // Apply offset (for shadow effect) - AFTER centering
        if (isset($rule['offsetX'])) {
            $obj['left'] = ($obj['left'] ?? 0) + $rule['offsetX'];
        }
        if (isset($rule['offsetY'])) {
            $obj['top'] = ($obj['top'] ?? 0) + $rule['offsetY'];
        }

        // Copy other properties (excluding width/height to avoid overwriting calculated values)
        $copyProps = ['fill', 'opacity', 'fontSize', 'fontFamily', 'fontWeight', 'textAlign', 'lineHeight', 'text', 'src', 'objectType', 'gradient', 'rx', 'ry'];
        foreach ($copyProps as $prop) {
            if (isset($rule[$prop])) {
                $obj[$prop] = $rule[$prop];
            }
        }

        // Handle logo type
        if ($rule['type'] === 'logo') {
            if (!empty($settings['brandLogo'])) {
                $obj['type'] = 'image';
                $obj['objectType'] = 'logo';
                $obj['src'] = $settings['brandLogo'];
                $obj['width'] = $this->percentToPixel($rule['maxWidthPercent'] ?? 18.5, $canvasWidth);
                $obj['height'] = null; // Auto-scale
            } else {
                return null; // Skip if no logo
            }
        }

        return $obj;
    }

    /**
     * Recursively replace template variables in nested arrays/objects
     */
    protected function replaceTemplateVariables($data, array $settings)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replaceTemplateVariables($value, $settings);
            }
            return $data;
        }

        if (is_string($data) && preg_match('/^\{\{(\w+)\}\}$/', $data, $matches)) {
            $varName = $matches[1];
            return $settings[$varName] ?? $data;
        }

        return $data;
    }

    /**
     * Convert percentage to pixel
     */
    protected function percentToPixel(float $percent, int $base): int
    {
        return (int) round(($percent / 100) * $base);
    }

    /**
     * Parse value from template (supports "5%", "100px", or numeric values)
     */
    protected function parseValue($value, int $base): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value)) {
            // Handle percentage (e.g., "5%", "90%")
            if (str_ends_with($value, '%')) {
                $percent = (float) rtrim($value, '%');
                return $this->percentToPixel($percent, $base);
            }

            // Handle pixel values (e.g., "100px")
            if (str_ends_with($value, 'px')) {
                return (int) rtrim($value, 'px');
            }

            // Fallback: treat as numeric
            return (int) $value;
        }

        return 0;
    }

    /**
     * Get dimensions for ratio
     */
    public function getDimensions(string $ratio): array
    {
        return $this->ratioDimensions[$ratio] ?? $this->ratioDimensions['1:1'];
    }

    /**
     * Darken a hex color by a percentage
     */
    protected function darkenColor(string $hex, int $percent): string
    {
        // Remove # if present
        $hex = ltrim($hex, '#');

        // Convert to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Darken
        $r = max(0, $r - ($r * $percent / 100));
        $g = max(0, $g - ($g * $percent / 100));
        $b = max(0, $b - ($b * $percent / 100));

        // Convert back to hex
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
