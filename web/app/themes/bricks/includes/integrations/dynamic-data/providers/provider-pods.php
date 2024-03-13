<?php
namespace Bricks\Integrations\Dynamic_Data\Providers;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Provider_Pods extends Base {

	public static function load_me() {
		return class_exists( 'PodsInit' );
	}

	public function register_tags() {
		$fields   = self::get_fields();
		$contexts = self::get_fields_by_context();

		foreach ( $fields as $field ) {
			$type = $field['type'];

			if ( ! isset( $contexts[ $type ] ) ) {
				continue;
			}

			foreach ( $contexts[ $type ] as $context ) {

				$key = 'pods_' . $field['object'] . '_' . $field['name'];

				$name = self::CONTEXT_TEXT === $context ? $key : $key . '_' . $context;

				$this->tags[ $name ] = [
					'name'     => '{' . $name . '}',
					'label'    => $field['label'],
					'group'    => $field['group'],
					'field'    => $field,
					'provider' => $this->name,
				];

				if ( self::CONTEXT_TEXT !== $context ) {
					$this->tags[ $name ]['deprecated'] = 1;
				}
			}
		}
	}

	public static function get_fields() {
		if ( ! function_exists( 'pods_api' ) ) {
			return [];
		}

		$strict = false;

		// Loads all the pods
		$pods = pods_api()->load_pods();

		$pods_fields = [];

		foreach ( $pods as $pod ) {
			if ( ! isset( $pod['type'] ) || 'post_type' !== $pod['type'] ) {
				continue;
			}

			foreach ( $pod['fields'] as $field ) {
				$args = [
					'label'  => $field['label'],
					'name'   => $field['name'],
					'type'   => $field['type'],
					'group'  => 'Pods (' . $pod['label'] . ')',
					'object' => $pod['name'],
					'pod_id' => $pod['id'],
				];

				if ( $field['type'] == 'pick' ) {
					$args['pick_object'] = $field['pick_object'];
					$args['pick_val']    = isset( $field['pick_val'] ) ? $field['pick_val'] : '';
				}

				$pods_fields[] = $args;
			}

		}

		return $pods_fields;
	}

	public function get_tag_value( $tag, $post, $args, $context ) {

		$post_id = isset( $post->ID ) ? $post->ID : '';

		$field = $this->tags[ $tag ]['field'];

		// STEP: Check for filter args
		$filters = $this->get_filters_from_args( $args );

		// STEP: Get the value
		if ( in_array( $field['type'], [ 'boolean', 'date', 'datetime', 'time' ] ) ) {
			$value = pods_field_display( $field['object'], $post_id, $field['name'] );
		} else {
			$value = pods_field( $field['object'], $post_id, $field['name'] );
		}

		switch ( $field['type'] ) {

			case 'code':
				$theme_styles = Theme_Styles::$active_settings;
				$classes      = isset( $theme_styles['code']['prettify'] ) ? 'prettyprint ' . $theme_styles['code']['prettify'] : '';
				$value        = '<pre class="' . $classes . '"><code>' . esc_html( $value ) . '</code></pre>';
				break;

			case 'file':
				$filters['object_type'] = 'media';

				if ( empty( $filters['image'] ) ) {
					$filters['link'] = true;
				}

				$value = isset( $value['ID'] ) ? [ $value['ID'] ] : wp_list_pluck( $value, 'ID' );
				break;

			case 'pick':
				if ( isset( $field['pick_object'] ) ) {
					// No need to prepare the custom simple type
					// $field['pick_object'] == 'custom-simple'

					// List of posts
					if ( $field['pick_object'] == 'post_type' ) {
						$filters['object_type'] = 'post';

						$value = isset( $value['ID'] ) ? [ $value['ID'] ] : wp_list_pluck( $value, 'ID' );
					}

					// List of terms
					elseif ( $field['pick_object'] == 'taxonomy' ) {
						$filters['object_type'] = 'term';
						$filters['taxonomy']    = $field['pick_val'];

						$value = isset( $value['term_id'] ) ? [ $value['term_id'] ] : wp_list_pluck( $value, 'term_id' );
					}

					// List of users
					elseif ( $field['pick_object'] == 'user' ) {
						$filters['object_type'] = 'user';

						$value = isset( $value['ID'] ) ? [ $value['ID'] ] : wp_list_pluck( $value, 'ID' );
					}
				}

				break;

		}

		// STEP: Apply context (text, link, image, media)
		$value = $this->format_value_for_context( $value, $tag, $post_id, $filters, $context );

		return $value;
	}

	/**
	 * Get all fields supported and their contexts
	 *
	 * @return array
	 */
	private static function get_fields_by_context() {
		$fields = [
			// Text
			'text'      => [ self::CONTEXT_TEXT ],
			'website'   => [ self::CONTEXT_TEXT, self::CONTEXT_LINK ],
			'phone'     => [ self::CONTEXT_TEXT ],
			'email'     => [ self::CONTEXT_TEXT, self::CONTEXT_LINK ],
			'password'  => [ self::CONTEXT_TEXT ],

			// Paragraph
			'paragraph' => [ self::CONTEXT_TEXT ],
			'wysiwyg'   => [ self::CONTEXT_TEXT ],
			'code'      => [ self::CONTEXT_TEXT ],

			// Date/Time
			'datetime'  => [ self::CONTEXT_TEXT ],
			'date'      => [ self::CONTEXT_TEXT ],
			'time'      => [ self::CONTEXT_TEXT ],

			// Number
			'number'    => [ self::CONTEXT_TEXT ],
			'currency'  => [ self::CONTEXT_TEXT ],

			// Relationship / Media
			'file'      => [ self::CONTEXT_TEXT, self::CONTEXT_LINK, self::CONTEXT_IMAGE, self::CONTEXT_VIDEO, self::CONTEXT_MEDIA ],
			'oembed'    => [ self::CONTEXT_TEXT, self::CONTEXT_LINK, self::CONTEXT_VIDEO, self::CONTEXT_MEDIA ],
			'pick'      => [ self::CONTEXT_TEXT, self::CONTEXT_LINK ], // relationship

			// Other
			'boolean'   => [ self::CONTEXT_TEXT ],
			'color'     => [ self::CONTEXT_TEXT ],
		];

		return $fields;
	}

}
