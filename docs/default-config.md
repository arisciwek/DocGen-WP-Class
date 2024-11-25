# Default config

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
     * Default settings
     */
    private $default_settings = [
        'temp_dir' => 'docgen-temp',        // Default value untuk form settings
        'template_dir' => 'docgen-templates', // Default value untuk form settings
        'output_format' => 'docx',
        'debug_mode' => false
    ];

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
        // Ambil settings dari database atau gunakan default jika belum ada
        $this->settings = get_option('docgen_wpclass_settings', $this->default_settings);
    }

    /**
     * Setup dasar untuk DocGen
     */
    public function setup($config = []) {
        // Merge config dengan default settings
        $config = wp_parse_args($config, $this->default_settings);

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
     * Get default settings
     */
    public function get_default_settings() {
        return $this->default_settings;
    }

    /**
     * Get settings saat ini
     */
    public function get_settings() {
        return $this->settings;
    }

    /**
     * Update settings
     */
    public function update_settings($new_settings) {
        $this->settings = wp_parse_args($new_settings, $this->settings);
        update_option('docgen_wpclass_settings', $this->settings);
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

Dengan perubahan ini:

1. Default settings tetap menggunakan 'docgen-temp' dan 'docgen-templates'
2. Plugin lain bisa menggunakan settings default atau custom:

```php
// Menggunakan default settings
docgen_wpclass()->setup();

// Atau dengan custom settings
docgen_wpclass()->setup([
    'temp_dir' => 'custom-temp',
    'template_dir' => 'custom-templates'
]);
```

3. Form settings di admin panel bisa menampilkan nilai default dan menyimpan perubahan user:

```php
// Di form settings
$settings = docgen_wpclass()->get_settings();
$default_settings = docgen_wpclass()->get_default_settings();

echo '<input type="text" name="temp_dir" 
    value="' . esc_attr($settings['temp_dir']) . '" 
    placeholder="' . esc_attr($default_settings['temp_dir']) . '">';
```

Ini memberi fleksibilitas untuk:
- Menggunakan nilai default di awal
- Menyimpan konfigurasi custom di database
- Menampilkan form settings yang bisa disesuaikan user
- Memudahkan reset ke nilai default jika diperlukan
