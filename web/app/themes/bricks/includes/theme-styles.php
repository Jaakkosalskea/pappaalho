<?php
namespace Bricks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Theme_Styles {
	public static $styles = [];

	public static $active_id;
	public static $active_settings = [];

	public static $control_options = [];
	public static $control_groups  = [];
	public static $controls        = [];

	public function __construct() {
		add_action( 'wp', [ $this, 'set_controls' ] );
		add_action( 'wp', [ $this, 'load_set_styles' ] );

		add_action( 'wp_ajax_bricks_create_styles', [ $this, 'create_styles' ] );
		add_action( 'wp_ajax_bricks_delete_style', [ $this, 'delete_style' ] );
	}

	public static function set_controls() {
		self::$control_options = Setup::$control_options;
		self::$control_groups  = self::get_control_groups();
		self::$controls        = self::get_controls();
	}

	public static function load_set_styles( $post_id = 0 ) {
		self::load_styles();
		self::set_active_style( $post_id );
	}

	/**
	 * Load theme styles
	 */
	public static function load_styles() {
		// Load 'Styles' abstract base class
		require_once BRICKS_PATH . 'includes/theme-styles/base.php';

		// // NOTE: Undocumented
		self::$styles = apply_filters( 'bricks/theme_styles', get_option( BRICKS_DB_THEME_STYLES, [] ) );
	}

	/**
	 * Get control groups
	 */
	public static function get_control_groups() {
		$control_groups = [];

		// CONDITIONS

		$control_groups['conditions'] = [
			'title' => esc_html__( 'Conditions', 'bricks' ),
		];

		// GENERAL STYLES

		$control_groups['general'] = [
			'title' => esc_html__( 'General', 'bricks' ),
		];

		$control_groups['colors'] = [
			'title' => esc_html__( 'Colors', 'bricks' ),
		];

		$control_groups['content'] = [
			'title' => esc_html__( 'Content', 'bricks' ),
		];

		$control_groups['links'] = [
			'title' => esc_html__( 'Links', 'bricks' ),
		];

		$control_groups['typography'] = [
			'title' => esc_html__( 'Typography', 'bricks' ),
		];

		// LAYOUT ELEMENTS

		$control_groups['section'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Section', 'bricks' ),
		];

		$control_groups['container'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Container', 'bricks' ),
		];

		$control_groups['block'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Block', 'bricks' ),
		];

		$control_groups['div'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . 'Div',
		];

		// ELEMENT STYLES

		$control_groups['accordion'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Accordion', 'bricks' ),
		];

		$control_groups['alert'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Alert', 'bricks' ),
		];

		$control_groups['button'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Button', 'bricks' ),
		];

		$control_groups['carousel'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Carousel', 'bricks' ),
		];

		$control_groups['code'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Code', 'bricks' ),
		];

		$control_groups['counter'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Counter', 'bricks' ),
		];

		$control_groups['divider'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Divider', 'bricks' ),
		];

		$control_groups['form'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Form', 'bricks' ),
		];

		$control_groups['heading'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Heading', 'bricks' ),
		];

		$control_groups['icon-box'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Icon Box', 'bricks' ),
		];

		$control_groups['image'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Image', 'bricks' ),
		];

		$control_groups['image-gallery'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Image gallery', 'bricks' ),
		];

		$control_groups['list'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'List', 'bricks' ),
		];

		$control_groups['nav-menu'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Nav Menu', 'bricks' ),
		];

		$control_groups['post-content'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Post content', 'bricks' ),
		];

		$control_groups['post-meta'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Meta data', 'bricks' ),
		];

		$control_groups['post-navigation'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Post navigation', 'bricks' ),
		];

		$control_groups['related-posts'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Related posts', 'bricks' ),
		];

		$control_groups['post-taxonomy'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Taxonomy', 'bricks' ),
		];

		$control_groups['post-title'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Post title', 'bricks' ),
		];

		$control_groups['pricing-tables'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Pricing tables', 'bricks' ),
		];

		$control_groups['progress-bar'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Progress bar', 'bricks' ),
		];

		$control_groups['search'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Search', 'bricks' ),
		];

		$control_groups['sidebar'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Sidebar', 'bricks' ),
		];

		$control_groups['slider'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Slider', 'bricks' ),
		];

		$control_groups['social-icons'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Icon list', 'bricks' ),
		];

		$control_groups['svg'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - SVG',
		];

		$control_groups['tabs'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Tabs', 'bricks' ),
		];

		$control_groups['team-members'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Team members', 'bricks' ),
		];

		$control_groups['testimonials'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Testimonials', 'bricks' ),
		];

		$control_groups['text'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Text', 'bricks' ),
		];

		$control_groups['video'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'Video', 'bricks' ),
		];

		$control_groups['wordpress'] = [
			'title' => esc_html__( 'Element', 'bricks' ) . ' - ' . esc_html__( 'WordPress', 'bricks' ),
		];

		$control_groups = apply_filters( 'bricks/theme_styles/control_groups', $control_groups );

		return $control_groups;
	}

	/**
	 * Get all theme style controls
	 */
	public static function get_controls() {
		$theme_styles_controls = [];

		foreach ( glob( BRICKS_PATH . 'includes/theme-styles/controls/*.php' ) as $file ) {
			if ( ! is_readable( $file ) ) {
				continue;
			}

			$element          = require_once $file;
			$element_name     = isset( $element['name'] ) ? $element['name'] : '';
			$element_controls = isset( $element['controls'] ) ? $element['controls'] : '';

			if ( ! $element_name || ! is_array( $element_controls ) ) {
				continue;
			}

			foreach ( $element_controls as $control_key => $control ) {
				$control['group'] = $element_name;

				// Check for control property 'cssSelector': Prefix CSS 'selector' with element CSS class (e.g.: '.brxe-alert')
				$css_selector = isset( $element['cssSelector'] ) ? $element['cssSelector'] : '';

				if ( isset( $control['css'] ) && is_array( $control['css'] ) ) {
					foreach ( $control['css'] as $index => $value ) {
						// Append custom selector
						$custom_selector = isset( $control['css'][ $index ]['selector'] ) ? $control['css'][ $index ]['selector'] : '';

						if ( $custom_selector ) {
							// @since 1.4: Remove leading '&' (attach to element root)
							if ( strpos( $custom_selector, '&' ) !== false ) {
								$custom_selector = str_replace( '&', '', $custom_selector );

								$control['css'][ $index ]['selector'] = "{$css_selector}{$custom_selector}";
							} else {
								$control['css'][ $index ]['selector'] = "$css_selector $custom_selector";
							}
						} else {
							$control['css'][ $index ]['selector'] = $css_selector;
						}
					}
				}

				$theme_styles_controls[ $element_name ][ $control_key ] = $control;
			}
		}

		return apply_filters( 'bricks/theme_styles/controls', $theme_styles_controls );
	}

	/**
	 * Get controls data
	 */
	public static function get_controls_data() {
		return [
			'controlGroups' => self::$control_groups,
			'controls'      => self::$controls,
		];
	}

	/**
	 * Create new styles (create new one or import styles from file)
	 */
	public function create_styles() {
		Ajax::verify_request();

		if ( ! isset( $_POST['styles'] ) ) {
			wp_send_json_success();
		}

		$custom_styles = get_option( BRICKS_DB_THEME_STYLES, [] );
		$new_styles    = stripslashes_deep( $_POST['styles'] );

		if ( empty( $new_styles ) || ! is_array( $new_styles ) ) {
			wp_send_json_success();
		}

		foreach ( $new_styles as $style ) {
			if ( array_key_exists( $style['id'], $custom_styles ) ) {
				continue;
			}

			if ( isset( $style['oldId'] ) && array_key_exists( $style['oldId'], $custom_styles ) ) {
				$custom_styles[ $style['id'] ] = $custom_styles[ $style['oldId'] ];
				unset( $custom_styles[ $style['oldId'] ] );
				continue;
			}

			$custom_styles[ $style['id'] ] = [
				'label'    => $style['label'],
				'settings' => $style['settings'],
			];
		}

		update_option( BRICKS_DB_THEME_STYLES, $custom_styles );

		wp_send_json_success();
	}

	/**
	 * Delete custom style from db (by style ID)
	 */
	public function delete_style() {
		Ajax::verify_request();

		$custom_styles = get_option( BRICKS_DB_THEME_STYLES, [] );

		// Remove reset from custom styles
		if ( array_key_exists( $_POST['styleId'], $custom_styles ) ) {
			unset( $custom_styles[ $_POST['styleId'] ] );
		}

		// Save custom style in db option table
		update_option( BRICKS_DB_THEME_STYLES, $custom_styles );

		wp_send_json_success();
	}

	/**
	 * Get active theme style according to theme style conditions
	 *
	 * @param integer $post_id Template ID.
	 * @param boolean $return_id Set to true to return active theme style ID for this template (needed on template import)
	 */
	public static function set_active_style( $post_id = 0, $return_id = false ) {
		$styles = get_option( BRICKS_DB_THEME_STYLES, [] );

		if ( empty( $post_id ) || is_object( $post_id ) ) {
			$post_id = is_home() ? get_option( 'page_for_posts' ) : get_the_ID();
		}

		$post_type = get_post_type( $post_id );

		$preview_type = ''; // Only applicable to templates

		// Check if Bricks template has preview content
		if ( $post_id && $post_type === BRICKS_DB_TEMPLATE_SLUG && ! $return_id ) {
			$preview_type = Helpers::get_template_setting( 'templatePreviewType', $post_id );
			$preview_id   = Helpers::get_template_setting( 'templatePreviewPostId', $post_id );

			if ( ! empty( $preview_id ) ) {
				$post_id   = $preview_id;
				$post_type = get_post_type( $preview_id );
			}
		}

		// Hold the found styles with score 0.low XX.high [score => style id]
		$found_styles = [];

		// Check if any style condition is met (if so, return style ID to apply it to post)
		// 2 - Entire website (condition = any)
		// 7 - Post Type
		// 8 - Terms, specific archives, children of specific Post ID
		// 9 - Front page
		// 10 - Specific Post ID (best match)
		foreach ( $styles as $style_id => $style ) {
			$conditions = isset( $style['settings']['conditions']['conditions'] ) ? $style['settings']['conditions']['conditions'] : false;

			// Skip styles without conditions
			if ( ! is_array( $conditions ) ) {
				continue;
			}

			$found_styles = Database::screen_conditions( $found_styles, $style_id, $conditions, $post_id, $preview_type );
		}

		if ( ! empty( $found_styles ) ) {
			ksort( $found_styles, SORT_NUMERIC );
			self::$active_id = array_pop( $found_styles );

			if ( $return_id ) {
				return self::$active_id;
			}
		}

		// Set active style settings
		if ( self::$active_id ) {
			self::$active_settings = isset( self::$styles[ self::$active_id ]['settings'] ) ? self::$styles[ self::$active_id ]['settings'] : [];
		}
	}
}
