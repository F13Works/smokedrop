<?php

/**
 * Prevent direct access to this file.
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles all admin-related functionality.
 * 
 * @since       1.0.0
 * @package     SmokeDrop
 * @subpackage  SmokeDrop_Admin
 */

if( !class_exists( 'SmokeDrop_Admin' ) ) {
    class SmokeDrop_Admin {
        /**
         * Properties to store plugin-related data.
         */
        private $version;
        private $plugin_slug;

        /**
         * Initialize admin functionality by adding WordPress hooks.
         */
        public function smokedrop_admin_init() {
            $this->version     = SMOKEDROP_VERSION;
            $this->plugin_slug = SMOKEDROP_SLUG . '-admin';

            // Add a custom menu to the admin dashboard
            add_action( 'admin_menu', array( $this, 'smokedrop_admin_menu' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'smokedrop_enqueue_styles' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'smokedrop_enqueue_scripts' ) );
            add_action( 'wp_ajax_smokedrop_check_update', array( $this, 'smokedrop_get_update' ) );
            add_action( 'wp_ajax_nopriv_smokedrop_check_update', array( $this, 'smokedrop_get_update' ) );
        }

        /**
         * Register the stylesheets for the admin area.
         */
        public function smokedrop_enqueue_styles( $hook ) {
            if ( $hook !== 'toplevel_page_smokedrop-settings' ) {
                return;
            }

            wp_enqueue_style( $this->plugin_slug, 
                plugin_dir_url( __FILE__ ) . 'css/smokedrop-admin.css', 
                array(), $this->version, 'all' 
            );
        }

        /**
         * Register the JavaScript for the admin area.
         */
        public function smokedrop_enqueue_scripts( $hook ) {
            if ( $hook !== 'toplevel_page_smokedrop-settings' ) {
                return;
            }

            wp_enqueue_script( $this->plugin_slug, 
                plugin_dir_url( __FILE__ ) . 'js/smokedrop-admin.js', 
                array( 'jquery' ), $this->version, true 
            );

            // Pass AJAX URL to the script.
            wp_localize_script( $this->plugin_slug, 'smokedrop_ajax', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'smokedrop_nonce' )
            ) );
        }

        /**
         * Create hook callback function for plugin admin menu.
         */
        public function smokedrop_admin_menu() {
            // Get the base64 SVG icon.
            $svg_icon = $this->smokedrop_menu_icon();
            
            // Add the main menu.
            add_menu_page(
                __( 'SmokeDrop Settings', 'smokedrop' ),
                __( 'SmokeDrop', 'smokedrop' ),
                'manage_options',
                'smokedrop-settings',
                array( $this, 'smokedrop_admin_settings' ),
                $svg_icon,
                6
            );
        }

        /**
         * Callback function for the "SmokeDrop" menu page.
         */
        public function smokedrop_admin_settings() {
            include plugin_dir_path( __FILE__ ) . 'partials/smokedrop-admin-display.php';
        }

        /**
         * Create a function to make a request to the external server
         * to check plugin update available or not.
         */
        public function smokedrop_get_update() {
            // Validate the nonce.
            if ( !check_ajax_referer( 'smokedrop_nonce', '_ajax_nonce', false ) ) {
                wp_send_json_error( 
                    array(  'message' => 'Nonce error, please refresh the page.' ) 
                );
                return;
            }

            $request = wp_remote_get(
                'https://raw.githubusercontent.com/F13Works/smokedrop/refs/heads/master/manifest.json',
                array(
                    'timeout' => 15,
                    'headers' => array(
                        'Accept' => 'application/json'
                    )
                )
            );

            if(
                is_wp_error( $request )
                || 200 !== wp_remote_retrieve_response_code( $request )
                || empty( wp_remote_retrieve_body( $request ) )
            ) {
                wp_send_json_error( 
                    array( 'message' => 'Could not connect to the update server.' ) 
                );
            }

            // Decode the JSON data received from API.
            $data = json_decode( wp_remote_retrieve_body( $request ) );

            // If data and version is not available return error.
            if ( empty( $data ) || !isset( $data->version ) ) {
                wp_send_json_error( 
                    array( 'message' => 'Invalid update data received.' ) 
                );
            }

            // Comapre the version.
            if ( version_compare( $this->version, $data->version, '<' ) ) {
                $message = sprintf(
                    'A new version (%s) is available! <a href="%s">Click here to update</a>',
                    esc_html( $data->version ),
                    esc_url( admin_url( 'plugins.php' ) )
                );
                wp_send_json_success( 
                    array( 'message' => $message ) 
                );
            } else {
                wp_send_json_success( 
                    array( 'message' => 'You are using the latest version.' ) 
                );
            }

            return;
        }

        /**
         * Create Base64 SVG icon for plugin admin menu.
         */
        private function smokedrop_menu_icon( $base64 = true ) {
            $svg_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256" viewBox="0 0 256 256"><path d="M124.785 16.916c-1.807 1.603-4.271 4.866-5.475 7.25-4.732 9.363-7.199 15.054-7.855 18.125-.377 1.765-1.012 3.659-1.41 4.209s-1.509 4.6-2.47 9-2.026 8.675-2.367 9.5-.865 8.475-1.164 17c-.36 10.269-.915 15.5-1.643 15.5-.605 0-6.498-5.177-13.096-11.503-9.248-8.869-14.838-13.244-24.401-19.1-20.131-12.328-27.323-14.78-30.521-10.406-1.824 2.495-1.762 11.182.117 16.509.825 2.338 1.5 5.348 1.5 6.688 0 3.764 6.363 24.519 8.592 28.027.775 1.218 1.408 2.706 1.408 3.306s1.414 3.878 3.143 7.285C55.509 130.853 56 131.932 56 133.367c0 1.214-2.866 1.523-16.75 1.804-18.342.373-22.628 1.504-23.829 6.289-.943 3.758 4.076 11.954 12.579 20.542 10.075 10.175 13.524 12.738 26.25 19.506 2.613 1.389 4.75 2.986 4.75 3.549s-4.104 4.937-9.12 9.72c-9.795 9.341-11.173 11.486-9.596 14.947 3.693 8.105 45.346 9.877 64.824 2.759 2.416-.883 5.967-2.07 7.892-2.637s3.928-1.44 4.45-1.939c1.551-1.48 4.411-1.035 5.416.843.515.963 1.258 3.893 1.651 6.512.392 2.62 1.112 5.009 1.598 5.309.487.301.885 1.69.885 3.087s.633 3.958 1.406 5.691c.774 1.733 1.664 4.318 1.98 5.744.71 3.209 5.121 7.745 8.311 8.546 2.924.734 5.946-1.739 6.783-5.549.387-1.759-.642-6.396-2.943-13.273-3.622-10.825-4.305-15.225-2.531-16.321 1.007-.622 7.518 1.22 9.601 2.716 1.099.79 3.032 1.26 17.393 4.233 13.465 2.787 39.545 1.324 49.25-2.764 6.501-2.738 5.119-7.102-5.751-18.162-.551-.561-3.139-2.737-5.75-4.836S200 185.519 200 185.095s2.404-1.948 5.342-3.386c18.514-9.066 37.776-28.656 38.059-38.709.09-3.177-.313-3.708-4.363-5.75-4.047-2.041-5.884-2.25-19.75-2.25-14.36 0-15.288-.115-15.288-1.902 0-1.047 2.468-6.784 5.484-12.75s5.49-11.359 5.5-11.984c.009-.624.688-2.424 1.51-4 .821-1.575 1.496-3.786 1.5-4.914.003-1.127.43-2.477.949-3s1.393-2.75 1.943-4.95 1.48-5.8 2.066-8c1.549-5.81 4.008-19.581 4.03-22.559.049-6.841-5.705-7.9-16.45-3.028-8.655 3.924-24.927 14.006-29.554 18.31-1.937 1.803-5.987 5.296-9 7.763s-6.378 5.612-7.478 6.989c-3.52 4.406-5.548 6.113-6.536 5.502-.53-.327-.973-3.606-.984-7.286s-.688-10.516-1.503-15.191a3645 3645 0 0 1-2.585-15c-.606-3.575-1.454-6.95-1.884-7.5s-1.097-2.91-1.482-5.245-1.111-4.498-1.613-4.809-.913-1.221-.913-2.025c0-2.806-8.159-18.447-11.434-21.92-4.022-4.264-6.486-4.398-10.781-.585" fill="#fffcfc"/></svg>';
            // Create Base64 SVG icon.
            if ( $base64 ) {
                // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- This encoding is intended.
                return 'data:image/svg+xml;base64,' . base64_encode( $svg_icon );
            }
            return $svg_icon;
        }
    }
}
