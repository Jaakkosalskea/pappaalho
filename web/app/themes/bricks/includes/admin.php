<?php
namespace Bricks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Admin {
	const EDITING_CAP = 'edit_posts';

	public function __construct() {
		// add_action( 'wp_dashboard_setup', [$this, 'wp_dashboard_setup'] );

		add_action( 'after_switch_theme', [ $this, 'set_default_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'gutenberg_scripts' ] );

		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
		add_action( 'admin_notices', [ $this, 'admin_notice_regenerate_css_files' ] );
		add_action( 'wp_ajax_bricks_dismiss_admin_notice_regenerate_css_files', [ $this, 'dismiss_admin_notice_regenerate_css_files' ] );

		add_filter( 'admin_footer_text', [ $this, 'admin_footer_text' ], 10, 1 );
		add_filter( 'display_post_states', [ $this, 'add_post_state' ], 10, 2 );

		add_filter( 'admin_body_class', [ $this, 'admin_body_class' ] );
		add_filter( 'image_size_names_choose', [ $this, 'image_size_names_choose' ] );

		add_action( 'admin_init', [ $this, 'save_editor_mode' ] );
		add_filter( 'admin_url', [ $this, 'admin_url' ] );

		add_action( 'wp_ajax_bricks_import_global_settings', [ $this, 'import_global_settings' ] );
		add_action( 'wp_ajax_bricks_export_global_settings', [ $this, 'export_global_settings' ] );
		add_action( 'wp_ajax_bricks_save_settings', [ $this, 'save_settings' ] );
		add_action( 'wp_ajax_bricks_reset_settings', [ $this, 'reset_settings' ] );

		add_action( 'edit_form_after_title', [ $this, 'builder_tab_html' ], 10, 2 );
		add_filter( 'page_row_actions', [ $this, 'row_actions' ], 10, 2 );
		add_filter( 'post_row_actions', [ $this, 'row_actions' ], 10, 2 );

		add_filter( 'manage_' . BRICKS_DB_TEMPLATE_SLUG . '_posts_columns', [ $this, 'bricks_template_posts_columns' ] );
		add_action( 'manage_' . BRICKS_DB_TEMPLATE_SLUG . '_posts_custom_column', [ $this, 'bricks_template_posts_custom_column' ], 10, 2 );

		// Export template
		add_filter( 'bulk_actions-edit-bricks_template', [ $this, 'bricks_template_bulk_action_export' ] );
		add_filter( 'handle_bulk_actions-edit-bricks_template', [ $this, 'bricks_template_handle_bulk_action_export' ], 10, 3 );

		// Import template
		add_action( 'admin_footer', [ $this, 'import_templates_form' ] );

		// Add template type meta box
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'meta_box_save_post' ] );
	}

	/**
	 * Add meta box: Template type
	 *
	 * @since 1.0
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'meta-box-template-type',
			esc_html__( 'Template Type', 'bricks' ),
			[ $this, 'meta_box_template_type' ],
			BRICKS_DB_TEMPLATE_SLUG,
			'side',
			'high'
		);
	}

	/**
	 * Meta box: Template type render
	 *
	 * @since 1.0
	 */
	public function meta_box_template_type( $post ) {
		$template_type = get_post_meta( $post->ID, BRICKS_DB_TEMPLATE_TYPE, true );

		$template_types_options = Setup::$control_options['templateTypes'];
		?>
		<p><label for="bricks_template_type"><?php esc_html_e( 'Select template type:', 'bricks' ); ?></label></p>
		<select name="bricks_template_type" id="bricks_template_type" style="width: 100%">
			<option value=""><?php esc_html_e( 'Select', 'bricks' ); ?></option>
			<?php
			foreach ( $template_types_options as $key => $value ) {
				echo '<option value=' . $key . ' ' . selected( $key, $template_type ) . '>' . $value . '</option>';
			}
			?>
		</select>
		<?php
	}

	/**
	 * Meta box: Save/delete template type
	 *
	 * @since 1.0
	 */
	public function meta_box_save_post( $post_id ) {
		if ( isset( $_POST['bricks_template_type'] ) && ! empty( $_POST['bricks_template_type'] ) ) {
			update_post_meta(
				$post_id,
				BRICKS_DB_TEMPLATE_TYPE,
				$_POST['bricks_template_type']
			);
		}
	}

	/**
	 * Register dashboard widget
	 *
	 * NOTE: Not in use, yet.
	 *
	 * @since 1.0
	 */
	public function wp_dashboard_setup() {
		wp_add_dashboard_widget(
			'bricks_dashboard_widget',
			esc_html__( 'Bricks News', 'bricks' ),
			[ $this, 'dashboard_widget' ]
		);

		// Move Bricks dashboard widget to the top
		// https://codex.wordpress.org/Dashboard_Widgets_API#Advanced:_Forcing_your_widget_to_the_top
		global $wp_meta_boxes;

		$normal_dashboard                             = $wp_meta_boxes['dashboard']['normal']['core'];
		$sorted_dashboard                             = array_merge( [ 'bricks_dashboard_widget' => $normal_dashboard['bricks_dashboard_widget'] ], $normal_dashboard );
		$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
	}

	/**
	 * Render dashboard widget
	 *
	 * @since 1.0
	 */
	public function dashboard_widget() {
		// Get remote feed from Bricks blog
		$feed = Api::get_feed();

		if ( count( $feed ) ) {
			echo '<ul class="bricks-dashboard-feed-wrapper">';

			foreach ( $feed as $post ) {
				echo '<li>';
				echo '<a href="' . $post['permalink'] . '?utm_source=wp-admin&utm_medium=wp-dashboard-widget&utm_campaign=feed" target="_blank">' . $post['title'] . '</a>';
				echo '<p>' . $post['excerpt'] . '</p>';
				echo '</li>';
			}

			echo '</ul>';
		}
	}

	/**
	 * Post custom column
	 *
	 * @since 1.0
	 */
	public function posts_custom_column( $column, $post_id ) {
		if ( $column === 'template' ) {
			$post_template_id = 0;

			if ( $post_template_id ) {
				echo '<a href="' . Helpers::get_builder_edit_link( $post_id ) . '" target="_blank">' . $post_template['title'] . '</a>';
			} else {
				echo '-';
			}
		}
	}

	/**
	 * Add bulk action "Export"
	 *
	 * @since 1.0
	 */
	public function bricks_template_bulk_action_export( $actions ) {
		$actions[ BRICKS_EXPORT_TEMPLATES ] = esc_html__( 'Export', 'bricks' );

		return $actions;
	}

	/**
	 * Handle bulk action "Export"
	 *
	 * @param string $redirect_url Redirect URL.
	 * @param string $doaction     Action to run.
	 * @param array  $items        Items to run action on.
	 *
	 * @since 1.0
	 */
	public function bricks_template_handle_bulk_action_export( $redirect_url, $doaction, $items ) {
		if ( $doaction === BRICKS_EXPORT_TEMPLATES ) {
			$this->export_templates( $items );
		}

		return $redirect_url;
	}

	/**
	 * Export templates
	 *
	 * @param array $items Items to export.
	 *
	 * @since 1.0
	 */
	public function export_templates( $template_ids ) {
		$files = [];

		$wp_upload_dir = wp_upload_dir();

		$temp_path = trailingslashit( $wp_upload_dir['basedir'] ) . BRICKS_TEMP_DIR;

		// Create temp path if it doesn't exist
		wp_mkdir_p( $temp_path );

		foreach ( $template_ids as $template_id ) {
			$file_data         = Templates::export_template( false, $template_id );
			$file_path         = trailingslashit( $temp_path ) . $file_data['name'];
			$file_put_contents = file_put_contents( $file_path, $file_data['content'] );

			$files[] = [
				'path' => $file_path,
				'name' => $file_data['name'],
			];
		}

		// Check if ZipArchive PHP extension exists
		if ( ! class_exists( '\ZipArchive' ) ) {
			return new \WP_Error( 'ziparchive_error', 'Error: ZipArchive PHP extension does not exist.' );
		}

		// Create ZIP file
		$zip_filename = 'templates-' . date( 'Y-m-d' ) . '.zip';
		$zip_path     = trailingslashit( $temp_path ) . $zip_filename;
		$zip_archive  = new \ZipArchive();
		$zip_archive->open( $zip_path, \ZipArchive::CREATE );

		foreach ( $files as $file ) {
			$zip_archive->addFile( $file['path'], $file['name'] );
		}

		$zip_archive->close();

		// Delete template JSON files
		foreach ( $files as $file ) {
			unlink( $file['path'] );
		}

		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=' . $zip_filename );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . filesize( $zip_path ) );

		@ob_end_flush();

		@readfile( $zip_path );

		unlink( $zip_path );

		die;
	}

	/**
	 * Import templates form
	 *
	 * @since 1.0
	 */
	public function import_templates_form() {
		global $current_screen;

		if ( ! $current_screen ) {
			return;
		}

		// Show import templates form on "My Templates" admin page
		if ( $current_screen->id === 'edit-' . BRICKS_DB_TEMPLATE_SLUG ) {
			?>
		<div id="bricks-admin-import-wrapper">
			<a id="bricks-admin-import-action" class="page-title-action bricks-admin-import-toggle"><?php esc_html_e( 'Import template', 'bricks' ); ?></a>

			<div id="bricks-admin-import-form-wrapper">
				<p><?php esc_html_e( 'Select and import your template JSON/ZIP file from your computer.', 'bricks' ); ?></p>

				<form id="bricks-admin-import-form" method="post" enctype="multipart/form-data">
					<p><input type="file" name="files" id="bricks_import_files" accept=".json,application/json,.zip,application/octet-stream,application/zip,application/x-zip,application/x-zip-compressed" multiple required></p>

					<input type="submit" class="button button-primary button-large" value="<?php echo esc_attr__( 'Import template', 'bricks' ); ?>">
					<button class="button button-large bricks-admin-import-toggle"><?php esc_html_e( 'Cancel', 'bricks' ); ?></button>

					<input type="hidden" name="action" value="bricks_import_template">
				</form>

				<i class="close bricks-admin-import-toggle dashicons dashicons-no-alt"></i>
			</div>
		</div>
			<?php
		}
	}

	/**
	 * Import global settings
	 *
	 * @since 1.0
	 */
	public function import_global_settings() {
		Ajax::verify_request();

		// Load WP_WP_Filesystem for temp file URL access
		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		// Import single JSON file
		$files    = $_FILES['files']['tmp_name'];
		$settings = [];
		$updated  = false;

		foreach ( $files as $file ) {
			$settings = json_decode( $wp_filesystem->get_contents( $file ), true );
		}

		if ( is_array( $settings ) && count( $settings ) ) {
			$updated = update_option( BRICKS_DB_GLOBAL_SETTINGS, $settings );
		}

		wp_send_json_success(
			[
				'settings' => $settings,
				'updated'  => $updated,
			]
		);
	}

	/**
	 * Generate and download JSON file with global settings
	 *
	 * @since 1.0
	 */
	public static function export_global_settings() {
		// Verify nonce
		if ( ! check_ajax_referer( 'bricks-nonce', 'nonce', false ) ) {
			wp_send_json_error( 'verify_nonce: "bricks-nonce" is invalid.' );
		}

		// Get latest settings
		$settings    = get_option( BRICKS_DB_GLOBAL_SETTINGS, [] );
		$export_json = json_encode( $settings );

		header( 'Content-Description: File Transfer' );
		header( 'Content-type: application/txt' );
		header( 'Content-Disposition: attachment; filename="bricks-settings-' . date( 'Y-m-d' ) . '.json"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );

		echo $export_json;
		exit;
	}

	/**
	 * Save settings in WP dashboard on form 'save' submit
	 *
	 * @since 1.0
	 */
	public function save_settings() {
		parse_str( $_POST['formData'], $settings );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'You don\'t have sufficient permission to save settings.', 'bricks' ) ] );
		}

		$old_settings = Database::$global_settings;
		$new_settings = [];

		// If execute code is disabled, remove any execute capability that might be set
		if ( isset( $settings['executeCodeDisabled'] ) ) {
			unset( $settings['executeCodeCapabilities'] );
		}

		foreach ( $settings as $key => $value ) {
			if ( $key === 'builderLocale' && empty( $value ) ) {
				$value = 'en_US';
			}

			if ( $value == '' ) {
				continue;
			}

			if ( $value === 'on' ) {
				$value = true;
			}

			// Min. autosave interval: 15 seconds
			if ( $key === 'builderAutosaveInterval' && intval( $value ) < 15 ) {
				$value = 15;
			}

			if ( $key === 'builderCapabilities' ) {
				Capabilities::save_builder_capabilities( $value );

				// Don't save selected capabilities in Global Settings, but as user role capabilities
				continue;
			}

			if ( $key === 'uploadSvgCapabilities' ) {
				Capabilities::save_capabilities( Capabilities::UPLOAD_SVG, $value );

				// Don't save selected capabilities in Global Settings, but as user role capabilities
				continue;
			}

			if ( $key === 'executeCodeCapabilities' ) {
				Capabilities::save_capabilities( Capabilities::EXECUTE_CODE, $value );

				// Don't save selected capabilities in Global Settings, but as user role capabilities
				continue;
			}

			// Enciphered API keys: Save old value
			if ( is_string( $value ) && strpos( $value, 'xxxx' ) !== false ) {
				$new_settings[ $key ] = $old_settings[ $key ];
			} else {
				$new_settings[ $key ] = $value;
			}
		}

		if ( empty( $settings['uploadSvgCapabilities'] ) ) {
			Capabilities::save_capabilities( Capabilities::UPLOAD_SVG );
		}

		if ( empty( $settings['executeCodeCapabilities'] ) ) {
			Capabilities::save_capabilities( Capabilities::EXECUTE_CODE );
		}

		update_option( BRICKS_DB_GLOBAL_SETTINGS, $new_settings );

		// Sync Mailchimp and Sendgrid lists (@since 1.0)
		$mailchimp_lists = \Bricks\Integrations\Form\Actions\Mailchimp::sync_lists();
		$sendgrid_lists  = \Bricks\Integrations\Form\Actions\Sendgrid::sync_lists();

		// Download remote templates from server and store as db option
		Templates::get_remote_templates_data();

		wp_send_json_success(
			[
				'new_settings'    => $new_settings,
				'mailchimp_lists' => $mailchimp_lists,
				'sendgrid_lists'  => $sendgrid_lists,
			]
		);
	}

	/**
	 * Reset settings in WP dashboard on form 'reset' submit
	 *
	 * @since 1.0
	 */
	public function reset_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'You don\'t have sufficient permission to reset settings.', 'bricks' ) ] );
		}

		delete_option( BRICKS_DB_GLOBAL_SETTINGS );
		delete_option( 'bricks_mailchimp_lists' );
		delete_option( 'bricks_sendgrid_lists' );

		self::set_default_settings();

		Capabilities::set_defaults();

		wp_send_json_success();
	}

	/**
	 * Template columns
	 *
	 * @since 1.0
	 */
	public function bricks_template_posts_columns( $columns ) {
		$columns = [
			'cb'                          => '<input type="checkbox" />',
			'title'                       => esc_html__( 'Title', 'bricks' ),
			'template_type'               => esc_html__( 'Type', 'bricks' ),
			'template_conditions'         => esc_html__( 'Conditions', 'bricks' ),
			BRICKS_DB_TEMPLATE_TAX_BUNDLE => '<a href="' . admin_url( 'edit-tags.php?taxonomy=' . BRICKS_DB_TEMPLATE_TAX_BUNDLE ) . '">' . esc_html__( 'Bundle', 'bricks' ) . '</a>',
			BRICKS_DB_TEMPLATE_TAX_TAG    => '<a href="' . admin_url( 'edit-tags.php?taxonomy=' . BRICKS_DB_TEMPLATE_TAX_TAG ) . '">' . esc_html__( 'Tags', 'bricks' ) . '</a>',
			'shortcode'                   => esc_html__( 'Shortcode', 'bricks' ),
			'author'                      => esc_html__( 'Author', 'bricks' ),
			'date'                        => esc_html__( 'Date', 'bricks' ),
		];

		return $columns;
	}

	/**
	 * Template custom column
	 *
	 * @since 1.0
	 */
	public function bricks_template_posts_custom_column( $column, $post_id ) {
		$template_type = get_post_meta( $post_id, BRICKS_DB_TEMPLATE_TYPE, true );

		// Template conditions
		if ( $column === 'template_conditions' ) {
			$settings_template_controls = isset( Settings::$controls['template'] ) ? Settings::$controls['template']['controls'] : false;

			$template_settings   = Helpers::get_template_settings( $post_id );
			$template_conditions = isset( $template_settings['templateConditions'] ) && is_array( $template_settings['templateConditions'] ) ? $template_settings['templateConditions'] : [];

			// STEP: No template conditions found: Check for default template (by template type, must be published)
			if ( ! count( $template_conditions ) && ! Database::get_setting( 'defaultTemplatesDisabled', false ) && get_post_status( $post_id ) === 'publish' ) {
				// Check if template type in a default template type
				if ( in_array( $template_type, Database::$default_template_types ) ) {
					$default_condition = '';

					switch ( $template_type ) {
						case 'header':
						case 'footer':
							$default_condition = esc_html__( 'Entire website', 'bricks' );
							break;

						case 'archive':
							$default_condition = esc_html__( 'All archives', 'bricks' );
							break;

						case 'search':
							$default_condition = esc_html__( 'Search results', 'bricks' );
							break;

						case 'error':
							$default_condition = esc_html__( 'Error page', 'bricks' );
							break;

						// WooCommerce
						case 'wc_archive':
							$default_condition = esc_html__( 'Product archive', 'bricks' );
							break;

						case 'wc_product':
							$default_condition = esc_html__( 'Single product', 'bricks' );
							break;

						case 'wc_cart':
							$default_condition = esc_html__( 'Cart', 'bricks' );
							break;

						case 'wc_cart_empty':
							$default_condition = esc_html__( 'Empty cart', 'bricks' );
							break;

						case 'wc_form_checkout':
							$default_condition = esc_html__( 'Checkout', 'bricks' );
							break;

						case 'wc_form_pay':
							$default_condition = esc_html__( 'Pay', 'bricks' );
							break;

						case 'wc_thankyou':
							$default_condition = esc_html__( 'Thank you', 'bricks' );
							break;

						case 'wc_order_receipt':
							$default_condition = esc_html__( 'Order receipt', 'bricks' );
							break;
					}

					if ( $default_condition ) {
						echo esc_html__( 'Default', 'bricks' ) . ': ' . $default_condition;
					}

					return;
				}
			}

			$conditions = [];

			if ( count( $template_conditions ) ) {
				foreach ( $template_conditions as $template_condition ) {
					$sub_conditions = [];
					$main_condition = '';

					if ( isset( $template_condition['main'] ) ) {
						$main_condition = $settings_template_controls['templateConditions']['fields']['main']['options'][ $template_condition['main'] ];

						switch ( $template_condition['main'] ) {
							case 'ids':
								if ( isset( $template_condition['ids'] ) && is_array( $template_condition['ids'] ) ) {
									foreach ( $template_condition['ids'] as $id ) {
										$sub_conditions[] = get_the_title( $id );
									}
								}
								break;

							case 'postType':
								if ( isset( $template_condition['postType'] ) && is_array( $template_condition['postType'] ) ) {
									foreach ( $template_condition['postType'] as $post_type ) {
										$post_type_object = get_post_type_object( $post_type );

										if ( $post_type_object ) {
											$sub_conditions[] = $post_type_object->labels->singular_name;
										} else {
											$sub_conditions[] = ucfirst( $post_type );
										}
									}
								}
								break;

							case 'archiveType':
								if ( isset( $template_condition['archiveType'] ) && is_array( $template_condition['archiveType'] ) ) {
									foreach ( $template_condition['archiveType'] as $archive_type ) {
										$sub_conditions[] = $settings_template_controls['templateConditions']['fields']['archiveType']['options'][ $archive_type ];
									}
								}
								break;

							case 'terms':
								if ( isset( $template_condition['terms'] ) && is_array( $template_condition['terms'] ) ) {
									foreach ( $template_condition['terms'] as $term_parts ) {
										$term_parts = explode( '::', $term_parts );
										$taxonomy   = $term_parts[0];
										$term_id    = $term_parts[1];

										$term = get_term_by( 'id', $term_id, $taxonomy );

										if ( gettype( $term ) === 'object' ) {
											$sub_conditions[] = $term->name;
										}
									}
								}
								break;
						}
					} else {
						echo '-';
					}

					$main_condition = isset( $template_condition['exclude'] ) ? esc_html__( 'Exclude', 'bricks' ) . ': ' . $main_condition : $main_condition;

					if ( count( $sub_conditions ) ) {
						$conditions[] = $main_condition . ' (' . join( ', ', $sub_conditions ) . ')';
					} else {
						$conditions[] = $main_condition;
					}
				}
			} else {
				echo '-';
			}

			if ( count( $conditions ) ) {
				echo '<ul>';

				foreach ( $conditions as $condition ) {
					echo '<li>' . $condition . '</li>';
				}

				echo '</ul>';
			}
		}

		// Template type
		elseif ( $column === 'template_type' ) {
			$template_types = Setup::$control_options['templateTypes'];

			echo array_key_exists( $template_type, $template_types ) ? $template_types[ $template_type ] : '-';
		}

		// Template bundle
		elseif ( $column === BRICKS_DB_TEMPLATE_TAX_BUNDLE ) {
			$template_bundles = get_the_terms( $post_id, BRICKS_DB_TEMPLATE_TAX_BUNDLE );

			if ( is_array( $template_bundles ) ) {
				$bundle_url = [];

				foreach ( $template_bundles as $bundle ) {
					$bundle_list_url = admin_url( 'edit.php?post_type=' . BRICKS_DB_TEMPLATE_SLUG . '&template_bundle=' . $bundle->slug );
					$bundle_edit_url = get_edit_tag_link( $bundle->term_id, BRICKS_DB_TEMPLATE_TAX_BUNDLE );

					$bundle_url[] = '<a href="' . esc_url( $bundle_list_url ) . '">' . $bundle->name . '</a> (<a href="' . $bundle_edit_url . '">' . esc_html( 'edit', 'bricks' ) . '</a>)';
				}

				echo join( ', ', $bundle_url );
			} else {
				echo '-';
			}
		}

		// Template tag
		elseif ( $column === BRICKS_DB_TEMPLATE_TAX_TAG ) {
			$template_tags = get_the_terms( $post_id, BRICKS_DB_TEMPLATE_TAX_TAG );

			if ( is_array( $template_tags ) ) {
				$tag_url = [];

				foreach ( $template_tags as $tag ) {
					$tag_list_url = admin_url( 'edit.php?post_type=' . BRICKS_DB_TEMPLATE_SLUG . '&template_tag=' . $tag->slug );
					$tag_edit_url = get_edit_tag_link( $tag->term_id, BRICKS_DB_TEMPLATE_TAX_TAG );

					$tag_url[] = '<a href="' . esc_url( $tag_list_url ) . '">' . $tag->name . '</a> (<a href="' . $tag_edit_url . '">' . esc_html( 'edit', 'bricks' ) . '</a>)';
				}

				echo join( ', ', $tag_url );
			} else {
				echo '-';
			}
		}

		// Template shortcode
		elseif ( $column === 'shortcode' ) {
			$shortcode = '[bricks_template id="' . $post_id . '"]';

			echo '<input type="text" size="' . strlen( $shortcode ) . '" class="bricks-copy-to-clipboard" readonly data-success="' . esc_html__( 'Copied to clipboard', 'bricks' ) . '" value="' . esc_attr( $shortcode ) . '">';
		}

		return $column;
	}

	/**
	 * Set default settings
	 *
	 * @since 1.0
	 */
	public static function set_default_settings() {
		add_option(
			BRICKS_DB_GLOBAL_SETTINGS,
			[
				'postTypes'              => [ 'page' ],
				'builderMode'            => 'dark',
				'builderToolbarLogoLink' => 'current',
			]
		);
	}

	public function gutenberg_scripts() {
		if ( Helpers::is_post_type_supported() && Capabilities::current_user_can_use_builder() ) {
			wp_enqueue_script( 'bricks-gutenberg', BRICKS_URL_ASSETS . 'js/gutenberg.min.js', [ 'jquery' ], filemtime( BRICKS_PATH_ASSETS . 'js/gutenberg.min.js' ), true );
		}
	}

	/**
	 * Admin scripts and styles
	 *
	 * @since 1.0
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_style( 'bricks-admin', BRICKS_URL_ASSETS . 'css/admin.min.css', [], filemtime( BRICKS_PATH_ASSETS . 'css/admin.min.css' ) );

		if ( is_rtl() ) {
			wp_enqueue_style( 'bricks-admin-rtl', BRICKS_URL_ASSETS . 'css/admin-rtl.min.css', [ 'bricks-admin' ], filemtime( BRICKS_PATH_ASSETS . 'css/admin-rtl.min.css' ) );
		}

		wp_enqueue_script( 'bricks-admin', BRICKS_URL_ASSETS . 'js/admin.min.js', [ 'jquery' ], filemtime( BRICKS_PATH_ASSETS . 'js/admin.min.js' ), true );

		wp_localize_script(
			'bricks-admin',
			'bricksData',
			[
				'title'               => BRICKS_NAME,
				'ajaxUrl'             => admin_url( 'admin-ajax.php' ),
				'postId'              => get_the_ID(),
				'nonce'               => wp_create_nonce( 'bricks-nonce' ),
				'currentScreen'       => get_current_screen(),
				'cofirmResetSettings' => esc_html__( 'You are about to reset all Bricks global settings. Do you wish to proceed?', 'bricks' ),
				'deleteBricksDataUrl' => Helpers::delete_bricks_data_by_post_id(),
			]
		);
	}

	/**
	 * Admin menu
	 *
	 * @since 1.0
	 */
	public function admin_menu() {
		// Return: Current user has no access to Bricks
		if ( Capabilities::current_user_has_no_access() ) {
			return;
		}

		$menu_icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMzVweCIgaGVpZ2h0PSI0NXB4IiB2aWV3Qm94PSIwIDAgMzUgNDUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8IS0tIEdlbmVyYXRvcjogU2tldGNoIDU5LjEgKDg2MTQ0KSAtIGh0dHBzOi8vc2tldGNoLmNvbSAtLT4KICAgIDx0aXRsZT5iPC90aXRsZT4KICAgIDxkZXNjPkNyZWF0ZWQgd2l0aCBTa2V0Y2guPC9kZXNjPgogICAgPGcgaWQ9IkxvZ29zLC1GYXZpY29uIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4KICAgICAgICA8ZyBpZD0iRmF2aWNvbi0oNjR4NjQpIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtMTYuMDAwMDAwLCAtMTEuMDAwMDAwKSIgZmlsbD0iIzIxMjEyMSIgZmlsbC1ydWxlPSJub256ZXJvIj4KICAgICAgICAgICAgPHBhdGggZD0iTTI1LjE4NzUsMTEuMzQzNzUgTDI1LjkzNzUsMTEuODEyNSBMMjUuOTM3NSwyNC44NDM3NSBDMjguNTgzMzQ2NiwyMy4wOTM3NDEzIDMxLjUxMDQwMDYsMjIuMjE4NzUgMzQuNzE4NzUsMjIuMjE4NzUgQzM5LjM0Mzc3MzEsMjIuMjE4NzUgNDMuMTc3MDY4MSwyMy44MzMzMTcyIDQ2LjIxODc1LDI3LjA2MjUgQzQ5LjIxODc2NSwzMC4yOTE2ODI4IDUwLjcxODc1LDM0LjI3MDgwOTcgNTAuNzE4NzUsMzkgQzUwLjcxODc1LDQzLjc1MDAyMzcgNDkuMjA4MzQ4NCw0Ny43MjkxNTA2IDQ2LjE4NzUsNTAuOTM3NSBDNDMuMTQ1ODE4MSw1NC4xNjY2ODI4IDM5LjMyMjkzOTcsNTUuNzgxMjUgMzQuNzE4NzUsNTUuNzgxMjUgQzMwLjY5Nzg5NjYsNTUuNzgxMjUgMjcuMjYwNDMwOSw1NC4zNDM3NjQ0IDI0LjQwNjI1LDUxLjQ2ODc1IEwyNC40MDYyNSw1NSBMMTYuMDMxMjUsNTUgTDE2LjAzMTI1LDEyLjM3NSBMMjUuMTg3NSwxMS4zNDM3NSBaIE0zMy4xMjUsMzAuNjg3NSBDMzAuOTE2NjU1NiwzMC42ODc1IDI5LjA3MjkyNDEsMzEuNDM3NDkyNSAyNy41OTM3NSwzMi45Mzc1IEMyNi4xMTQ1NzU5LDM0LjQ3OTE3NDQgMjUuMzc1LDM2LjQ5OTk4NzUgMjUuMzc1LDM5IEMyNS4zNzUsNDEuNTAwMDEyNSAyNi4xMTQ1NzU5LDQzLjUxMDQwOTEgMjcuNTkzNzUsNDUuMDMxMjUgQzI5LjA1MjA5MDYsNDYuNTUyMDkwOSAzMC44OTU4MjIyLDQ3LjMxMjUgMzMuMTI1LDQ3LjMxMjUgQzM1LjQ3OTE3ODQsNDcuMzEyNSAzNy4zODU0MDk0LDQ2LjUyMDg0MTMgMzguODQzNzUsNDQuOTM3NSBDNDAuMjgxMjU3Miw0My4zNzQ5OTIyIDQxLDQxLjM5NTg0NTMgNDEsMzkgQzQxLDM2LjYwNDE1NDcgNDAuMjcwODQwNiwzNC42MTQ1OTEzIDM4LjgxMjUsMzMuMDMxMjUgQzM3LjM1NDE1OTQsMzEuNDY4NzQyMiAzNS40NTgzNDUsMzAuNjg3NSAzMy4xMjUsMzAuNjg3NSBaIiBpZD0iYiI+PC9wYXRoPgogICAgICAgIDwvZz4KICAgIDwvZz4KPC9zdmc+';

		add_menu_page(
			BRICKS_NAME,
			BRICKS_NAME,
			self::EDITING_CAP,
			'bricks',
			[ $this, 'admin_screen_getting_started' ],
			$menu_icon,
			// 'dashicons-editor-bold',
			// BRICKS_URL_ASSETS . 'images/bricks-favicon-b.svg',
			2
		);

		add_submenu_page(
			'bricks',
			esc_html__( 'Getting Started', 'bricks' ),
			esc_html__( 'Getting Started', 'bricks' ),
			self::EDITING_CAP,
			'bricks',
			[ $this, 'admin_screen_getting_started' ]
		);

		add_submenu_page(
			'bricks',
			esc_html__( 'Templates', 'bricks' ),
			esc_html__( 'Templates', 'bricks' ),
			self::EDITING_CAP,
			'edit.php?post_type=' . BRICKS_DB_TEMPLATE_SLUG
		);

		add_submenu_page(
			'bricks',
			esc_html__( 'Settings', 'bricks' ),
			esc_html__( 'Settings', 'bricks' ),
			'manage_options',
			'bricks-settings',
			[ $this, 'admin_screen_settings' ]
		);

		add_submenu_page(
			'bricks',
			esc_html__( 'Custom Fonts', 'bricks' ),
			esc_html__( 'Custom Fonts', 'bricks' ),
			'manage_options',
			'edit.php?post_type=' . BRICKS_DB_CUSTOM_FONTS
		);

		add_submenu_page(
			'bricks',
			esc_html__( 'Sidebars', 'bricks' ),
			esc_html__( 'Sidebars', 'bricks' ),
			'manage_options',
			'bricks-sidebars',
			[ $this, 'admin_screen_sidebars' ]
		);

		add_submenu_page(
			'bricks',
			esc_html__( 'System Information', 'bricks' ),
			esc_html__( 'System Information', 'bricks' ),
			'manage_options',
			'bricks-system-information',
			[ $this, 'admin_screen_system_information' ]
		);

		add_submenu_page(
			'bricks',
			esc_html__( 'License', 'bricks' ),
			esc_html__( 'License', 'bricks' ),
			'manage_options',
			'bricks-license',
			[ $this, 'admin_screen_license' ]
		);
	}

	public function admin_screen_getting_started() {
		require_once 'admin/admin-screen-getting-started.php';
	}

	public function admin_screen_settings() {
		require_once 'admin/admin-screen-settings.php';
	}

	public function admin_screen_sidebars() {
		require_once 'admin/admin-screen-sidebars.php';
	}

	public function admin_screen_system_information() {
		require_once 'admin/admin-screen-system-information.php';
	}

	public function admin_screen_license() {
		require_once 'admin/admin-screen-license.php';
	}

	/**
	 * Dismiss admin notice about regenerating CSS files after theme update
	 *
	 * Opdate option entry to current theme version when dismissing notification to hide it until after next theme update.
	 *
	 * @since 1.3.7
	 */
	public function dismiss_admin_notice_regenerate_css_files() {
		$updated = update_option( BRICKS_CSS_FILES_LAST_GENERATED, BRICKS_VERSION );

		wp_send_json_success(
			[
				'updated'                   => $updated,
				'last_generated_in_version' => get_option( BRICKS_CSS_FILES_LAST_GENERATED ),
			]
		);
	}

	/**
	 * Admin notice: Show regenerate CSS files notification after Bricks theme update
	 *
	 * @since 1.3.7
	 */
	public function admin_notice_regenerate_css_files() {
		// STEP: Return if CSS loading method is not set to "External files"
		$css_loading_method = ! empty( Database::$global_settings['cssLoading'] ) ? Database::$global_settings['cssLoading'] : 'inline';

		if ( $css_loading_method !== 'file' ) {
			return;
		}

		// STEP: Return if CSS files have been generated for this version (meaning options entry matches installed version of Bricks)
		$css_files_last_generated_in_version = get_option( BRICKS_CSS_FILES_LAST_GENERATED );

		if ( version_compare( BRICKS_VERSION, $css_files_last_generated_in_version, '==' ) ) {
			return;
		}

		$text  = '<p>' . esc_html__( 'You are now running the latest version of Bricks. Please regenerate your external CSS files too!', 'bricks' ) . '</p>';
		$text .= '<a class="button" href="' . admin_url( 'admin.php?page=bricks-settings#tab-performance' ) . '">' . esc_html__( 'Go to: Bricks Settings', 'bricks' ) . '</a>';

		echo wp_kses_post( sprintf( '<div id="bricks-dismiss-regenerate-css-files" class="notice notice-info is-dismissible">%s</div>', wpautop( $text ) ) );
	}

	/**
	 * Admin notices
	 *
	 * @since 1.0
	 */
	public function admin_notices() {
		if ( empty( $_GET['bricks_notice'] ) ) {
			return;
		}

		$type = 'warning';
		$text = '';

		switch ( $_GET['bricks_notice'] ) {
			case 'settings_saved':
				// Bricks settings saved
				$text = esc_html__( 'Settings saved', 'bricks' ) . '.';
				$type = 'success';
				break;

			case 'settings_resetted':
				// Bricks settings resetted
				$text = esc_html__( 'Settings resetted', 'bricks' ) . '.';
				$type = 'success';
				break;

			case 'error_role_manager':
				// User role not allowed to use builder
				$user = wp_get_current_user();
				$role = isset( $user->roles[0] ) ? $user->roles[0] : '';
				$text = sprintf( esc_html__( 'Your user role "%s" is not allowed to edit with Bricks. Please get in touch with the site admin to change it.', 'bricks' ), $role );
				break;

			case 'error_post_type':
				// Post type is not enabled for Bricks
				$post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';
				$text      = sprintf( esc_html__( 'Bricks is not enabled for post type "%s". Go to "Bricks > Settings" to enable this post type.', 'bricks' ), $post_type );
				break;

			case 'post_meta_deleted':
				$text = sprintf( esc_html__( 'Bricks data for "%s" deleted.', 'bricks' ), get_the_title() );
				$type = 'success';
				break;
		}

		$html = sprintf( '<div class="notice notice-' . sanitize_html_class( $type, 'warning' ) . ' is-dismissible">%s</div>', wpautop( $text ) );

		// echo wp_kses_post( $html );
		echo self::admin_notice_html( $type, $text );
	}

	public static function admin_notice_html( $type, $text, $dismissible = true ) {
		$classes = [ 'notice', "notice-$type" ];

		if ( $dismissible ) {
			$classes[] = 'is-dismissible';
		}

		return wp_kses_post( sprintf( '<div class="' . implode( ' ', $classes ) . '">%s</div>', wpautop( $text ) ) );
	}

	/**
	 * Admin footer text
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public function admin_footer_text( $text ) {
		$text = sprintf( __( 'Thank you for creating with <a href="%1$s" target="_blank">WordPress</a> and <a href="%2$s" target="_blank">Bricks</a>.' ), 'https://wordpress.org/', BRICKS_REMOTE_URL );

		return $text;
	}

	/**
	 * Add custom post state: "Bricks"
	 *
	 * If post has last been saved with Bricks (check post meta value: '_bricks_editor_mode')
	 *
	 * @param array    $post_states Array of post states.
	 * @param \WP_Post $post        Current post object.
	 *
	 * @since 1.0
	 */
	public function add_post_state( $post_states, $post ) {
		if (
			! Helpers::is_post_type_supported() ||
			! Capabilities::current_user_can_use_builder( $post->ID ) ||
			Helpers::get_editor_mode( $post->ID ) === 'wordpress'
		) {
			return $post_states;
		}

		$post_states['bricks'] = BRICKS_NAME;

		$data_type   = 'content';
		$is_template = get_post_type( $post->ID ) === BRICKS_DB_TEMPLATE_SLUG;

		if ( $is_template ) {
			$template_type = Templates::get_template_type( $post->ID );

			if ( $template_type === 'header' ) {
				$data_type = 'header';
			} elseif ( $template_type === 'footer' ) {
				$data_type = 'footer';
			}
		}

		// Checks for new data structure
		$has_container_data = get_post_meta( $post->ID, "_bricks_page_{$data_type}_2", true );

		// No Bricks container data: Remove 'Bricks' label
		if ( ! $has_container_data && ! $is_template ) {
			unset( $post_states['bricks'] );
		}

		return $post_states;
	}

	/**
	 * Add editor body class 'active'
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function admin_body_class( $classes ) {
		global $pagenow;

		if ( ! in_array( $pagenow, [ 'post.php', 'post-new.php' ] ) ) {
			return $classes;
		}

		$editor_mode = Helpers::get_editor_mode( get_the_ID() );

		if ( ! empty( $editor_mode ) ) {
			$classes .= ' ' . $editor_mode . '-editor-active';
		}

		return $classes;
	}

	/**
	 * Add custom image sizes to WordPress media library in admin area
	 *
	 * Also used to build dropdown of control 'images' for single image element.
	 *
	 * @since 1.0
	 */
	public function image_size_names_choose( $default_sizes ) {
		global $_wp_additional_image_sizes;
		$custom_image_sizes = [];

		foreach ( $_wp_additional_image_sizes as $key => $value ) {
			$key_array         = explode( '_', $key );
			$capitalized_array = [];

			foreach ( $key_array as $string ) {
				array_push( $capitalized_array, ucfirst( $string ) );
			}

			$custom_image_sizes[ $key ] = join( ' ', $capitalized_array );
		}

		return array_merge( $default_sizes, $custom_image_sizes );
	}

	/**
	 * Make sure 'editor_mode' URL param is not removed from admin URL
	 */
	public function admin_url( $link ) {
		if ( isset( $_REQUEST['editor_mode'] ) && ! empty( $_REQUEST['editor_mode'] ) ) {
			return add_query_arg(
				[
					'editor_mode' => $_REQUEST['editor_mode']
				],
				$link
			);
		}

		return $link;
	}

	/**
	 * Save Editor mode based on the admin bar links
	 *
	 * @see Setup->admin_bar_menu()
	 *
	 * @since 1.3.7
	 */
	public function save_editor_mode() {
		if ( empty( $_GET['editor_mode'] ) || ! isset( $_GET['action'] ) || empty( $_GET['post'] ) || ! isset( $_GET['_bricksmode'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['_bricksmode'], '_bricks_editor_mode_nonce' ) ) {
			return;
		}

		update_post_meta( $_GET['post'], BRICKS_DB_EDITOR_MODE, $_GET['editor_mode'] );
	}

	/**
	 * Builder tab HTML (toggle via builder tab)
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function builder_tab_html() {
		// Show builder-related HTML only if post type and user role is enabled for builder
		if ( ! Helpers::is_post_type_supported() || ! Capabilities::current_user_can_use_builder() ) {
			return;
		}

		// Delete post meta: content and editor mode
		if ( isset( $_GET['bricks_delete_post_meta'] ) ) {
			delete_post_meta( $_GET['bricks_delete_post_meta'], BRICKS_DB_PAGE_HEADER );
			delete_post_meta( $_GET['bricks_delete_post_meta'], BRICKS_DB_PAGE_CONTENT );
			delete_post_meta( $_GET['bricks_delete_post_meta'], BRICKS_DB_PAGE_FOOTER );
			delete_post_meta( $_GET['bricks_delete_post_meta'], BRICKS_DB_PAGE_SETTINGS );
			delete_post_meta( $_GET['bricks_delete_post_meta'], BRICKS_DB_EDITOR_MODE );
			delete_post_meta( $_GET['bricks_delete_post_meta'], BRICKS_DB_TEMPLATE_TYPE );
		}

		// Get editor mode
		$editor_mode = Helpers::get_editor_mode( get_the_ID() );
		?>

		<div id="bricks-editor" class="bricks-editor postarea wp-editor-expand">
			<?php wp_nonce_field( 'editor_mode', '_bricks_editor_mode_nonce' ); ?>
			<input type="hidden" id="bricks-editor-mode" name="_bricks_editor_mode" value="<?php echo esc_attr( $editor_mode ); ?>" />

			<div class="wp-core-ui wp-editor-wrap bricks-active">

				<?php if ( get_post_type() !== BRICKS_DB_TEMPLATE_SLUG ) { ?>
				<div class="wp-editor-tools">
					<div class="wp-editor-tabs">
						<button type="button" id="content-tmce" class="wp-switch-editor switch-tmce"><?php esc_html_e( 'Visual', 'bricks' ); ?></button>
						<button type="button" id="content-html" class="wp-switch-editor switch-html"><?php esc_html_e( 'Text', 'bricks' ); ?></button>
						<button type="button" id="content-bricks" class="wp-switch-editor switch-bricks"><?php echo BRICKS_NAME; ?></button>
					</div>
				</div>
				<?php } ?>

				<div class="wp-editor-container">
					<p><a href="<?php echo Helpers::get_builder_edit_link( get_the_ID() ); ?>" class="button button-primary button-hero"><?php esc_html_e( 'Edit with Bricks', 'bricks' ); ?></a></p>

					<?php if ( Database::get_setting( 'deleteBricksData', false ) ) { ?>
					<p><a href="<?php echo esc_url( Helpers::delete_bricks_data_by_post_id() ); ?>" class="bricks-delete-post-meta button" onclick="return confirm('<?php echo sprintf( esc_html__( 'Are you sure you want to delete the Bricks-generated data for this %s?', 'bricks' ), get_post_type() ); ?>')"><?php esc_html_e( 'Delete Bricks data', 'bricks' ); ?></a></p>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * "Edit with Bricks" link for post type 'page', 'post' and all other CPTs
	 *
	 * @since 1.0
	 */
	public function row_actions( $actions, $post ) {
		if ( Helpers::is_post_type_supported() && Capabilities::current_user_can_use_builder() ) {
			// Export template
			if ( get_post_type() === BRICKS_DB_TEMPLATE_SLUG ) {
				$export_template_url = admin_url( 'admin-ajax.php' );
				$export_template_url = add_query_arg(
					[
						'action'     => 'bricks_export_template',
						'nonce'      => wp_create_nonce( 'bricks-nonce' ),
						'templateId' => get_the_ID(),
					],
					$export_template_url
				);

				$actions['export_template'] = sprintf(
					'<a href="%s">%s</a>',
					$export_template_url,
					esc_html__( 'Export Template', 'bricks' )
				);
			}

			// Edit with Bricks
			$actions['edit_with_bricks'] = sprintf(
				'<a href="%s">%s</a>',
				Helpers::get_builder_edit_link( $post->ID ),
				esc_html__( 'Edit with Bricks', 'bricks' )
			);
		}

		return $actions;
	}
}