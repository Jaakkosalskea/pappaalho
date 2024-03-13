<?php
namespace Bricks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Theme {
	public $capabilities;
	public $database;
	public $helpers;
	public $blocks;
	public $revisions;
	public $license;
	public $theme_styles;
	public $custom_fonts;
	public $settings;
	public $setup;
	public $search;
	public $ajax;
	public $svg;
	public $templates;
	public $heartbeat;
	public $converter;

	public $admin;
	public $feedback;

	public $api;
	public $elements;
	public $woocommerce;
	public $integrations_form;
	public $builder;
	public $frontend;

	public $assets;

	/**
	 * The one and only Theme instance
	 *
	 * @var Theme
	 */
	public static $instance = null;

	/**
	 * Autoload and init components
	 */
	public function __construct() {
		$this->autoloader();
		$this->init();
	}

	/**
	 * Autoload files
	 */
	private function autoloader() {
		require_once BRICKS_PATH . 'includes/autoloader.php';

		Autoloader::register();
	}

	/**
	 * Init components
	 */
	public function init() {
		Compatibility::register();

		$this->capabilities = new Capabilities();
		$this->database     = new Database();
		$this->helpers      = new Helpers();
		$this->blocks       = new Blocks();
		$this->revisions    = new Revisions();

		$this->license      = new License();
		$this->setup        = new Setup();
		$this->search       = new Search();
		$this->theme_styles = new Theme_Styles();
		$this->custom_fonts = new Custom_Fonts();

		// Load all elements in builder, but only requested elements on frontend
		$this->elements = new Elements();

		// Loads WooCommerce integration, if activated
		$this->woocommerce = new Woocommerce();

		$this->ajax      = new Ajax();
		$this->svg       = new Svg();
		$this->templates = new Templates();
		$this->settings  = new Settings();

		// Integrations
		$this->integrations_form = new Integrations\Form\Init();

		if ( is_admin() ) {
			$this->admin     = new Admin();
			$this->converter = new Converter();
			$this->feedback  = new Feedback();
		}

		$this->api = new Api();

		/**
		 * Dynamic Data
		 *
		 * Order matters: 'cmb2' before 'wp' so it can filter the custom fields correctly.
		 */
		Integrations\Dynamic_Data\Providers::register( [ 'cmb2', 'wp', 'woo', 'acf', 'pods', 'metabox', 'toolset', 'jetengine' ] );

		Integrations\Rank_Math\Rank_Math::register();

		// Check for builder instance inside Heartbeat class
		$this->heartbeat = new Heartbeat();

		if ( bricks_is_builder() ) {
			$this->builder = new Builder();
		} else {
			if ( ! is_admin() ) {
				$this->frontend = new Frontend();
			}
		}

		// @since 1.3.4
		$this->assets = new Assets();
	}

	/**
	 * Main Theme instance
	 *
	 * Ensure only one instance of Theme exists at any given time.
	 *
	 * @return object Theme The one and only Theme instance
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Theme ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

// Get the theme up and running
Theme::instance();
