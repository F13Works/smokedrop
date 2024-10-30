<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SmokeDrop {
    /**
     * Properties to store instances of admin and updater classes.
     */
    private $admin;
    private $updater;

    /**
     * Constructor: Load necessary class files and initialize components.
     */
    public function __construct() {
        // Include required class files
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/admin/class-smokedrop-admin.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-smokedrop-updater.php';
    }

    /**
     * Run the plugin by initializing the necessary components.
     */
    public function smokedrop_run() {
        // Instantiate classes
        $this->admin = new SmokeDrop_Admin();
        $this->updater = new SmokeDrop_Updater();

        // Run necessary methods
        $this->admin->smokedrop_admin_init();
        $this->updater->smokedrop_updater_init();
    }
}
