# Main Class

Kita ingin pendekatan yang lebih straightforward dan praktis.


```php
<?php
/**
 * DocGen WPClass - Main Class
 */

class DocGen_WPClass {
    /**
     * Instance dari class (singleton pattern)
     */
    private static $instance = null;

    /**
     * Settings dari DocGen
     */
    private $settings = [];

    /**
     * Get instance dari class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->settings = get_option('docgen_wpclass_settings', []);
    }

    /**
     * Setup dasar untuk DocGen
     */
    public function setup($config = []) {
        // Default config
        $default_config = [
            'temp_dir' => 'docgen-temp',
            'template_dir' => 'docgen-templates',
            'output_format' => 'docx',
            'debug_mode' => false
        ];

        // Merge dengan custom config
        $config = wp_parse_args($config, $default_config);

        // Setup direktori
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'];

        $dirs = [
            'temp_dir' => trailingslashit($base_dir) . $config['temp_dir'],
            'template_dir' => trailingslashit($base_dir) . $config['template_dir']
        ];

        // Buat direktori jika belum ada
        foreach ($dirs as $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
                file_put_contents($dir . '/index.php', '<?php // Silence is golden');
            }
        }

        // Simpan settings
        $this->settings = array_merge($config, ['dirs' => $dirs]);
        update_option('docgen_wpclass_settings', $this->settings);

        return true;
    }

    /**
     * Generate dokumen
     */
    public function generate($template_name, $data) {
        // Implementasi dari generate document
        // Copy dari DocGen Implementation Plugin
    }

    /**
     * Validate dan handle direktori
     */
    public function validate_directory($path) {
        // Implementasi dari directory handler
        // Copy dari DocGen Implementation Plugin
    }

    /**
     * Handle template
     */
    public function handle_template($template_file) {
        // Implementasi dari template handler
        // Copy dari DocGen Implementation Plugin
    }

    // ... tambahan method lain yang dibutuhkan
}

/**
 * Helper function untuk mengakses DocGen
 */
function docgen_wpclass() {
    return DocGen_WPClass::get_instance();
}

```

## Penggunaan di plugin lain:

```php
// Di file activator plugin lain
class Your_Plugin_Activator {
    public static function activate() {
        // Setup DocGen dengan konfigurasi kustom
        docgen_wpclass()->setup([
            'temp_dir' => 'your-plugin-temp',
            'template_dir' => 'your-plugin-templates'
        ]);

        // Kode aktivasi plugin lainnya...
    }
}

// Penggunaan untuk generate dokumen
docgen_wpclass()->generate('template-name', $data);
```

Ini lebih sederhana karena:
1. Satu file utama yang berisi semua fungsionalitas
2. Tidak perlu abstract class atau trait
3. Mudah diintegrasikan ke plugin lain
4. Tetap mempertahankan fungsi-fungsi dari plugin asli
