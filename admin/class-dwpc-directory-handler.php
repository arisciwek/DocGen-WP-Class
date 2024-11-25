<?php
/**
 * Directory Handler Class
 *
 * @package     DocGen_WPClass
 * @subpackage  Admin
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: admin/class-dwpc-directory-handler.php
 * 
 * Description: Utility class untuk menangani operasi directory.
 *              Handles directory testing, creation, cleanup,
 *              dan file management tasks.
 *              Menyediakan fungsi-fungsi helper untuk validasi path,
 *              scanning template, statistik directory dan manajemen file.
 * 
 * Changelog:
 * 1.0.0 - 2024-11-24 12:10:10
 * - Initial implementation with comprehensive directory management
 * - Added validation and security checks
 * - Added template scanning and validation
 * - Added directory statistics and cleanup utilities
 * - Added scheduled cleanup tasks
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class DocGen_WPClass_Directory_Handler {
    /**
     * Maximum age for temp files in hours
     * @var int
     */
    private $max_temp_age = 24;

    /**
     * Allowed file extensions
     * @var array
     */
    private $allowed_extensions = array('docx', 'odt');
    
    /**
     * Type of directory being validated
     * @var string
     */
    private $current_dir_type = '';

    /**
     * Constructor
     */
    public function __construct() {
        if (!wp_next_scheduled('docgen_wpclass_cleanup_temp')) {
            wp_schedule_event(time(), 'daily', 'docgen_wpclass_cleanup_temp');
        }
        
        add_action('docgen_wpclass_cleanup_temp', array($this, 'cleanup_temp_files'));
    }

    /**
     * Set current directory type for context-aware validation
     */
    public function set_directory_type($type) {
        $this->current_dir_type = $type;
    }

    /**
     * Validate directory path with improved checks
     * @param string $path Directory path to validate
     * @return bool|WP_Error True if valid, WP_Error if invalid
     */
    public function validate_directory_path($path) {
        // Basic path validation
        if (empty($path)) {
            return new WP_Error(
                'empty_path',
                __('Directory path cannot be empty', 'docgen-implementation')
            );
        }

        // Normalize path for consistent checking
        $path = wp_normalize_path($path);
        
        // Get WordPress root paths
        $wp_root = wp_normalize_path(ABSPATH);
        $wp_content = wp_normalize_path(WP_CONTENT_DIR);
        $uploads_dir = wp_normalize_path(wp_upload_dir()['basedir']);

        // Check path within WordPress directories
        $is_in_wp = (
            strpos($path, $wp_root) === 0 ||
            strpos($path, $wp_content) === 0 ||
            strpos($path, $uploads_dir) === 0
        );

        if (!$is_in_wp) {
            return new WP_Error(
                'invalid_path',
                __('Directory must be within WordPress installation', 'docgen-implementation')
            );
        }

        // Check for directory traversal
        if (strpos($path, '..') !== false) {
            return new WP_Error(
                'invalid_path',
                __('Directory path contains invalid navigation', 'docgen-implementation')
            );
        }

        // Check if directory exists or can be created
        if (!file_exists($path)) {
            $parent_dir = dirname($path);
            if (!is_writable($parent_dir)) {
                return new WP_Error(
                    'not_writable',
                    __('Parent directory is not writable', 'docgen-implementation')
                );
            }
        } else if (!is_writable($path)) {
            return new WP_Error(
                'not_writable',
                __('Directory exists but is not writable', 'docgen-implementation')
            );
        }

        return true;
    }


    /**
     * Create directory with improved error handling
     * @param string $path Directory path
     * @param int $permissions Directory permissions (octal)
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function create_directory($path, $permissions = 0755) {
        // Validate path first
        $validation = $this->validate_directory_path($path);
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Create directory if it doesn't exist
        if (!file_exists($path)) {
            if (!wp_mkdir_p($path)) {
                return new WP_Error(
                    'create_failed',
                    __('Failed to create directory', 'docgen-implementation')
                );
            }

            // Set directory permissions
            if (!@chmod($path, $permissions)) {
                error_log('Warning: Failed to set directory permissions for: ' . $path);
            }

            // Create index.php for security
            @file_put_contents($path . '/index.php', '<?php // Silence is golden.');
        }

        return true;
    }

    /**
     * Get directory size
     * @param string $path Directory path
     * @return int|WP_Error Size in bytes or WP_Error
     */
    public function get_directory_size($path) {
        if (!is_dir($path)) {
            return new WP_Error(
                'invalid_directory',
                __('Invalid directory', 'docgen-implementation')
            );
        }

        $size = 0;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path)
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * Get directory statistics
     * @param string $path Directory path
     * @return array|WP_Error Directory stats or error
     */
    public function get_directory_stats($path) {
        if (!is_dir($path)) {
            return new WP_Error(
                'invalid_directory',
                __('Invalid directory', 'docgen-implementation')
            );
        }

        $stats = array(
            'total_files' => 0,
            'total_size' => 0,
            'last_modified' => 0,
            'is_writable' => is_writable($path),
            'free_space' => disk_free_space($path),
            'by_extension' => array()
        );

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path)
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $stats['total_files']++;
                $stats['total_size'] += $file->getSize();
                $stats['last_modified'] = max($stats['last_modified'], $file->getMTime());

                $ext = strtolower($file->getExtension());
                if (!isset($stats['by_extension'][$ext])) {
                    $stats['by_extension'][$ext] = 0;
                }
                $stats['by_extension'][$ext]++;
            }
        }

        return $stats;
    }

    /**
     * Scan for template files
     * @param string $path Directory path
     * @return array|WP_Error Array of template files or error
     */
    public function scan_template_files($path) {
        if (!is_dir($path)) {
            return new WP_Error(
                'invalid_directory',
                __('Invalid directory', 'docgen-implementation')
            );
        }

        $templates = array();
        
        foreach ($this->allowed_extensions as $ext) {
            $files = glob($path . '/*.' . $ext);
            if ($files) {
                foreach ($files as $file) {
                    $templates[] = array(
                        'name' => basename($file),
                        'path' => $file,
                        'size' => filesize($file),
                        'modified' => filemtime($file),
                        'type' => $ext,
                        'is_valid' => $this->validate_template_file($file)
                    );
                }
            }
        }

        return $templates;
    }

    /**
     * Validate template file
     * @param string $file File path
     * @return bool True if valid template
     */
    public function validate_template_file($file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $this->allowed_extensions)) {
            return false;
        }

        // Check if file is a valid zip archive (DOCX/ODT are zip files)
        $zip = new ZipArchive();
        if ($zip->open($file) !== true) {
            return false;
        }

        // Check for required files based on type
        $required_files = ($ext === 'docx') ? 
            array('[Content_Types].xml', 'word/document.xml') :
            array('META-INF/manifest.xml', 'content.xml');

        foreach ($required_files as $required) {
            if ($zip->locateName($required) === false) {
                $zip->close();
                return false;
            }
        }

        $zip->close();
        return true;
    }

    /**
     * Clean directory
     * @param string $path Directory path
     * @param array $options Cleanup options
     * @return bool|WP_Error True on success or error
     */
    public function clean_directory($path, $options = array()) {
        $defaults = array(
            'older_than' => 24, // hours
            'extensions' => array(), // empty = all files
            'keep_latest' => 5, // number of latest files to keep
            'recursive' => false
        );

        $options = wp_parse_args($options, $defaults);

        if (!is_dir($path)) {
            return new WP_Error(
                'invalid_directory',
                __('Invalid directory', 'docgen-implementation')
            );
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            $options['recursive'] ? RecursiveIteratorIterator::SELF_FIRST : RecursiveIteratorIterator::LEAVES_ONLY
        );

        $deleted = 0;
        $failed = 0;
        $skipped = 0;

        // Get files list with timestamps
        $file_list = array();
        foreach ($files as $file) {
            if ($file->isFile()) {
                $ext = strtolower($file->getExtension());
                if (!empty($options['extensions']) && !in_array($ext, $options['extensions'])) {
                    continue;
                }
                $file_list[$file->getPathname()] = $file->getMTime();
            }
        }

        // Sort by modified time, newest first
        arsort($file_list);

        // Keep latest files if specified
        if ($options['keep_latest'] > 0) {
            $file_list = array_slice($file_list, $options['keep_latest']);
        }

        // Process remaining files
        foreach ($file_list as $file => $mtime) {
            // Skip if file is newer than specified age
            if ((time() - $mtime) < ($options['older_than'] * 3600)) {
                $skipped++;
                continue;
            }

            if (@unlink($file)) {
                $deleted++;
            } else {
                $failed++;
            }
        }

        return array(
            'deleted' => $deleted,
            'failed' => $failed,
            'skipped' => $skipped
        );
    }

}
