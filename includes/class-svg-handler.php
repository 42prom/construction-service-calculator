<?php
/**
 * Handle SVG uploads and processing
 *
 * @link       https://github.com/42prom
 * @since      1.0.0
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/includes
 */

/**
 * Handle SVG uploads and processing.
 *
 * Safe handling of SVG uploads, sanitization, and storage.
 *
 * @package    Construction_Service_Calculator
 * @subpackage Construction_Service_Calculator/includes
 * @author     Mikheili Nakeuri
 */
class Construction_Service_Calculator_SVG_Handler {

    /**
     * Allowed SVG attributes for sanitization.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $allowed_attributes    Array of allowed SVG attributes.
     */
    private static $allowed_attributes = array(
        'class', 'clip-path', 'clip-rule', 'fill', 'fill-opacity', 'fill-rule',
        'filter', 'id', 'mask', 'opacity', 'stroke', 'stroke-dasharray',
        'stroke-dashoffset', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit',
        'stroke-opacity', 'stroke-width', 'style', 'systemLanguage', 'transform',
        'href', 'vector-effect', 'width', 'height', 'x', 'y', 'cx', 'cy', 'r',
        'rx', 'ry', 'd', 'dx', 'dy', 'font-family', 'font-size', 'font-style',
        'font-weight', 'letter-spacing', 'text-anchor', 'text-decoration',
        'text-rendering', 'unicode-bidi', 'word-spacing', 'writing-mode',
        'requiredFeatures', 'requiredExtensions', 'systemLanguage',
        'xml:space', 'xmlns', 'data-name'
    );

    /**
     * Allowed SVG tags for sanitization.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $allowed_tags    Array of allowed SVG tags.
     */
    private static $allowed_tags = array(
        'svg', 'g', 'path', 'circle', 'ellipse', 'line', 'polyline',
        'polygon', 'rect', 'text', 'tspan', 'title', 'desc', 'defs',
        'linearGradient', 'radialGradient', 'stop', 'clipPath', 'mask',
        'pattern', 'filter', 'feBlend', 'feComposite', 'feColorMatrix',
        'feComponentTransfer', 'feConvolveMatrix', 'feDiffuseLighting',
        'feDisplacementMap', 'feDistantLight', 'feDropShadow', 'feFlood',
        'feFuncA', 'feFuncB', 'feFuncG', 'feFuncR', 'feGaussianBlur',
        'feImage', 'feMerge', 'feMergeNode', 'feMorphology', 'feOffset',
        'fePointLight', 'feSpecularLighting', 'feSpotLight', 'feTile',
        'feTurbulence', 'use', 'symbol'
    );

    /**
     * Add SVG to allowed mime types.
     *
     * @since    1.0.0
     * @param    array    $mimes    Mime types keyed by the file extension regex.
     * @return   array              Modified list of mime types.
     */
    public static function add_svg_mime_type($mimes) {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }

    /**
     * Sanitize SVG content
     *
     * @since    1.0.0
     * @param    string    $svg    SVG content to sanitize.
     * @return   string            Sanitized SVG content.
     */
    public static function sanitize_svg($svg) {
        // Load the SVG into DOMDocument
        $dom = new DOMDocument();
        $dom->loadXML($svg, LIBXML_NOERROR | LIBXML_NOWARNING);
        
        // Sanitize all elements recursively
        self::sanitize_element($dom->documentElement);
        
        // Return the sanitized SVG
        return $dom->saveXML($dom->documentElement);
    }
    
    /**
     * Sanitize a single SVG element and its children.
     *
     * @since    1.0.0
     * @access   private
     * @param    DOMElement    $element    The SVG element to sanitize.
     */
    private static function sanitize_element($element) {
        if (!$element) {
            return;
        }
        
        // Check if element tag is allowed
        if (!in_array($element->tagName, self::$allowed_tags)) {
            $element->parentNode->removeChild($element);
            return;
        }
        
        // Sanitize attributes
        $attributes_to_remove = array();
        foreach ($element->attributes as $attr) {
            if (!in_array($attr->name, self::$allowed_attributes)) {
                $attributes_to_remove[] = $attr->name;
            }
            
            // Check for JavaScript in attributes
            if (preg_match('/^\s*javascript\s*:/i', $attr->value)) {
                $attributes_to_remove[] = $attr->name;
            }
        }
        
        // Remove unsafe attributes
        foreach ($attributes_to_remove as $attr_name) {
            $element->removeAttribute($attr_name);
        }
        
        // Sanitize all child elements
        $children = array();
        for ($i = 0; $i < $element->childNodes->length; $i++) {
            $children[] = $element->childNodes->item($i);
        }
        
        foreach ($children as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                self::sanitize_element($child);
            }
        }
    }
    
    /**
     * Upload and process an SVG file.
     *
     * @since    1.0.0
     * @param    array    $file       The file from $_FILES.
     * @param    string   $category   Optional category for organizing icons.
     * @return   array                Upload result (success/error).
     */
    public static function upload_svg($file, $category = '') {
        // Verify file is an SVG
        $filetype = wp_check_filetype($file['name']);
        if ($filetype['type'] !== 'image/svg+xml') {
            return array(
                'success' => false,
                'message' => __('Only SVG files are allowed.', 'construction-service-calculator')
            );
        }
        
        // Read file content
        $svg_content = file_get_contents($file['tmp_name']);
        if (!$svg_content) {
            return array(
                'success' => false,
                'message' => __('Could not read the SVG file.', 'construction-service-calculator')
            );
        }
        
        // Sanitize SVG content
        $sanitized_svg = self::sanitize_svg($svg_content);
        if (!$sanitized_svg) {
            return array(
                'success' => false,
                'message' => __('Invalid SVG file.', 'construction-service-calculator')
            );
        }
        
        // Create uploads directory if it doesn't exist
        $upload_dir = wp_upload_dir();
        $svg_dir = $upload_dir['basedir'] . '/csc-svg-icons';
        if (!file_exists($svg_dir)) {
            wp_mkdir_p($svg_dir);
            
            // Create an index.php file to prevent directory listing
            $index_file = $svg_dir . '/index.php';
            file_put_contents($index_file, '<?php // Silence is golden');
        }
        
        // Add category subdirectory if provided
        if (!empty($category)) {
            $svg_dir .= '/' . sanitize_file_name($category);
            if (!file_exists($svg_dir)) {
                wp_mkdir_p($svg_dir);
            }
        }
        
        // Generate a unique filename
        $filename = sanitize_file_name($file['name']);
        $filename = wp_unique_filename($svg_dir, $filename);
        $file_path = $svg_dir . '/' . $filename;
        
        // Save the sanitized SVG
        $saved = file_put_contents($file_path, $sanitized_svg);
        if (!$saved) {
            return array(
                'success' => false,
                'message' => __('Could not save the SVG file.', 'construction-service-calculator')
            );
        }
        
        // Get the URL for the saved file
        $svg_url = $upload_dir['baseurl'] . '/csc-svg-icons';
        if (!empty($category)) {
            $svg_url .= '/' . sanitize_file_name($category);
        }
        $svg_url .= '/' . $filename;
        
        return array(
            'success' => true,
            'file_path' => $file_path,
            'file_url' => $svg_url,
            'filename' => $filename,
            'svg_content' => $sanitized_svg
        );
    }
    
    /**
     * Get SVG icon library.
     *
     * @since    1.0.0
     * @param    string    $category    Optional category to filter icons.
     * @return   array                  List of SVG icons.
     */
    public static function get_svg_library($category = '') {
        $upload_dir = wp_upload_dir();
        $svg_dir = $upload_dir['basedir'] . '/csc-svg-icons';
        
        // Add category subdirectory if provided
        if (!empty($category)) {
            $svg_dir .= '/' . sanitize_file_name($category);
        }
        
        // Check if directory exists
        if (!file_exists($svg_dir) || !is_dir($svg_dir)) {
            return array();
        }
        
        // Get all SVG files
        $svg_files = glob($svg_dir . '/*.svg');
        if (empty($svg_files)) {
            return array();
        }
        
        $icons = array();
        $svg_url = $upload_dir['baseurl'] . '/csc-svg-icons';
        if (!empty($category)) {
            $svg_url .= '/' . sanitize_file_name($category);
        }
        
        foreach ($svg_files as $file) {
            $filename = basename($file);
            $file_url = $svg_url . '/' . $filename;
            $svg_content = file_get_contents($file);
            
            $icons[] = array(
                'filename' => $filename,
                'file_path' => $file,
                'file_url' => $file_url,
                'svg_content' => $svg_content
            );
        }
        
        return $icons;
    }
    
    /**
     * Get SVG content by URL.
     *
     * @since    1.0.0
     * @param    string    $url    The URL of the SVG file.
     * @return   string            The SVG content or empty string on failure.
     */
    public static function get_svg_content($url) {
        // Convert URL to local path
        $upload_dir = wp_upload_dir();
        $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);
        
        // Check if file exists
        if (!file_exists($file_path)) {
            return '';
        }
        
        // Read and return SVG content
        return file_get_contents($file_path);
    }
}