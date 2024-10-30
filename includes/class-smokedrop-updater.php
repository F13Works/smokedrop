<?php

/**
 * Prevent direct access to this file.
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class is dedicated for update SmokeDrop plugin from 
 * external web server.
 * 
 * @since       1.0.0
 * @package     SmokeDrop
 * @subpackage  SmokeDrop_Updater
 */
if( !class_exists( 'SmokeDrop_Updater' ) ) {
    class SmokeDrop_Updater {
        /**
         * Properties to store plugin-related data.
         */
        private $plugin_slug;
		private $plugin_file;
		private $version;
		private $cache_key;
		private $cache_allowed;

        /**
         * Constructor: Set up initial values and hook into WordPress's update filters.
         */
        public function smokedrop_updater_init() {
			$this->version       = SMOKEDROP_VERSION;
            $this->plugin_slug   = SMOKEDROP_SLUG;
			$this->plugin_file   = SMOKEDROP_BASEFILE;
			$this->cache_key     = SMOKEDROP_CACHE_KEY;
			$this->cache_allowed = SMOKEDROP_CACHE_ALLOWED;

			// Schedule the update check if not already scheduled
			if ( !wp_next_scheduled( 'smokedrop_update_schedule' ) ) {
				wp_schedule_event( time(), 'hourly', 'smokedrop_update_schedule' );
			}

			add_filter( 'plugins_api', array( $this, 'smokedrop_info' ), 20, 3 );
			add_filter( 'site_transient_update_plugins', array( $this, 'smokedrop_check_update' ) );
			add_action( 'smokedrop_update_schedule', array( $this, 'smokedrop_schedule_purge' ) );
			add_action( 'upgrader_process_complete', array( $this, 'smokedrop_purge' ), 10, 2 );
        }

		/**
         * Check for updates and inform WordPress if a newer version is available.
         * 
         * @param object $transient
         */
		public function smokedrop_check_update( $transient ) {
			if ( empty($transient->checked ) ) {
				return $transient;
			}

			$remote = $this->smokedrop_request();

			// Return earlier if the required parameters are not available.
			if ( 
				$remote === false
				|| !isset( $remote->version ) 
				|| !isset( $remote->requires ) 
				|| !isset( $remote->requires_php ) 
			) {
				return $transient;
			}

			// Compare the version.
			if(
				version_compare( $this->version, $remote->version, '<' )
				&& version_compare( $remote->requires, get_bloginfo( 'version' ), '<=' )
				&& version_compare( $remote->requires_php, PHP_VERSION, '<' )
			) {
				$res = new stdClass();

				$res->slug        = $this->plugin_slug;
				$res->plugin      = $this->plugin_file;
				$res->new_version = $remote->version;
				$res->tested      = $remote->tested;
				$res->package     = $remote->download_url;

				$res->icons = array(
					'1x' => $remote->icons->low,
					'2x' => $remote->icons->high
				);
				$res->banners = array(
					'low'  => $remote->banners->low,
					'high' => $remote->banners->high
				);

				$transient->response[ $res->plugin ] = $res;
            }

			return $transient;
		}

        /**
         * Purge the cache after the update process is completed.
         * 
         * @param object $upgrader
         * @param array $options
         */
        public function smokedrop_purge( $upgrader, $options ){
			if (
				$this->cache_allowed
				&& 'update' === $options['action']
				&& 'plugin' === $options['type']
			) {
				// Just clean the cache when new plugin version is installed
				delete_site_transient( $this->cache_key );
			}
		}

		/**
		 * Delete the update transient.
		 */
		public function smokedrop_schedule_purge() {
			// Use cache_key for deletion.
			delete_site_transient( $this->cache_key );
		}

		/**
         * Provide plugin information to WordPress when requested.
         * 
         * @param object|false $res
         * @param string $action
         * @param object $args
         */
        public function smokedrop_info( $res, $action, $args ) {
			// Do nothing if you're not getting plugin information right now
			if( 'plugin_information' !== $action ) {
				return $res;
			}

			// Do nothing if it is not our plugin
			if( $this->plugin_slug !== $args->slug ) {
				return $res;
			}

			// Get updates
			$remote = $this->smokedrop_request();

			if( !$remote ) {
				return $res;
			}

			$res = new stdClass();

			$res->name           = $remote->name;
			$res->slug           = $remote->slug;
			$res->version        = $remote->version;
			$res->tested         = $remote->tested;
			$res->requires       = $remote->requires;
			$res->author         = $remote->author;
			$res->author_profile = $remote->author_profile;
			$res->download_link  = $remote->download_url;
			$res->trunk          = $remote->download_url;
			$res->requires_php   = $remote->requires_php;
			$res->last_updated   = $remote->last_updated;

			if ( !empty( $remote->sections ) ) {
				$res->sections = array(
					'description'  => $remote->sections->description,
					'installation' => $remote->sections->installation,
					'changelog'    => $remote->sections->changelog
				);
			}

			if ( !empty( $remote->icons ) ) {
				$res->icons = array(
					'1x' => $remote->icons->low,
					'2x' => $remote->icons->high
				);
			}

			if( !empty( $remote->banners ) ) {
				$res->banners = array(
					'low'  => $remote->banners->low,
					'high' => $remote->banners->high
				);
			}

			return $res;
		}

        /**
         * Request the update information from the external server.
         */
        private function smokedrop_request() {
            $cache_trans = get_transient( $this->cache_key );

			if( false === $cache_trans || !$this->cache_allowed ) {
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
					return false;
				}

				// Cache the response for the specified duration.
				set_transient( $this->cache_key, $request, DAY_IN_SECONDS  );
			}

			return json_decode( wp_remote_retrieve_body( $request ) );
        }
    }
}
