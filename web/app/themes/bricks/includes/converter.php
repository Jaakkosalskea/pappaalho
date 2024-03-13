<?php
namespace Bricks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * TODO: Can't convert globalElements into nestable elements
 * As each global element is considered one individual array item.
 * So nestable elements like 'slider-nested' aren't allowed be to be saved as a global element!
 */
class Converter {
	public function __construct() {
		add_action( 'wp_ajax_bricks_get_converter_items', [ $this, 'get_converter_items' ] );
		add_action( 'wp_ajax_bricks_run_converter', [ $this, 'run_converter' ] );
	}

	/**
	 * Get all items that need to run through converter
	 *
	 * - themeStyles
	 * - globalSettings
	 * - globalClasses
	 * - globalElements
	 * - template IDs (+ their page settings)
	 * - post IDs (+ their page settings)
	 *
	 * @since 1.4
	 */
	public function get_converter_items() {
		$items_to_convert = [];

		$convert = is_array( $_POST['convert'] ) ? $_POST['convert'] : [];

		// Always add '_position': relative if top/right/left/bottom value is set (@since 1.5)
		$convert[] = 'positionRelative';

		// Convert theme styles
		if ( in_array( 'container', $_POST['convert'] ) ) {
			$items_to_convert[] = 'themeStyles';
		}

		// Convert element IDs & classes
		if ( in_array( 'elementClasses', $_POST['convert'] ) ) {
			$items_to_convert[] = 'globalSettings';
		}

		// Global classes (for any converter action)
		$items_to_convert[] = 'globalClasses';

		// Global elements (for any converter action)
		$items_to_convert[] = 'globalElements';

		// Get template & post IDs (for any converter action)

		// Get IDs of all Bricks templates
		$template_ids     = Templates::get_all_template_ids();
		$items_to_convert = array_merge( $items_to_convert, $template_ids );

		// Get IDs of all Bricks data posts
		$post_ids         = Helpers::get_all_bricks_post_ids();
		$items_to_convert = array_merge( $items_to_convert, $post_ids );

		wp_send_json_success(
			[
				'items'   => $items_to_convert,
				'convert' => $convert,
			]
		);
	}

	/**
	 * Run converter
	 *
	 * @param string $data Source string to apply search & replace for.
	 *
	 * @return string
	 *
	 * @since 1.4   Convert element IDs & class names for 1.4 ('bricks-element-' to 'brxe-')
	 * @since 1.5 Convert elements to nestable elements
	 */
	public function run_converter() {
		$data    = isset( $_POST['data'] ) ? $_POST['data'] : false;
		$convert = isset( $_POST['convert'] ) ? $_POST['convert'] : [];
		$updated = [];
		$label   = '';

		switch ( $data ) {
			case 'themeStyles':
				$theme_styles       = get_option( BRICKS_DB_THEME_STYLES, [] );
				$converter_response = self::convert( $theme_styles, 'themeStyles', $convert );

				if ( isset( $converter_response['count'] ) && $converter_response['count'] > 0 ) {
					$label            = esc_html__( 'Theme Styles', 'bricks' );
					$updated[ $data ] = update_option( BRICKS_DB_THEME_STYLES, $converter_response['data'] );
				}
				break;

			case 'globalSettings':
				$converter_response = self::convert( Database::$global_settings, 'globalSettings', $convert );

				if ( isset( $converter_response['count'] ) && $converter_response['count'] > 0 ) {
					$label            = esc_html__( 'Global settings', 'bricks' ) . ' (' . esc_html__( 'Custom CSS', 'bricks' ) . ')';
					$updated[ $data ] = update_option( BRICKS_DB_GLOBAL_SETTINGS, $converter_response['data'] );
				}
				break;

			case 'globalClasses':
				$global_classes     = get_option( BRICKS_DB_GLOBAL_CLASSES, [] );
				$converter_response = self::convert( $global_classes, 'globalClasses', $convert );

				if ( isset( $converter_response['count'] ) && $converter_response['count'] > 0 ) {
					$label            = esc_html__( 'Global classes', 'bricks' );
					$updated[ $data ] = update_option( BRICKS_DB_GLOBAL_CLASSES, $converter_response['data'] );
				}
				break;

			case 'globalElements':
				$global_elements    = get_option( BRICKS_DB_GLOBAL_ELEMENTS, [] );
				$converter_response = self::convert( $global_elements, 'globalElements', $convert );

				if ( isset( $converter_response['count'] ) && $converter_response['count'] > 0 ) {
					$label            = esc_html__( 'Global elements', 'bricks' );
					$updated[ $data ] = update_option( BRICKS_DB_GLOBAL_ELEMENTS, $converter_response['data'] );
				}
				break;

			// Individual post + any possible page settings (that has Bricks data OR is Bricks template)
			default:
				$post_id       = $data;
				$post_type     = get_post_type( $post_id );
				$elements      = false;
				$post_meta_key = false;

				// Get content type (header, content, footer) & elements
				if ( $post_type === BRICKS_DB_TEMPLATE_SLUG ) {
					$elements = get_post_meta( $post_id, BRICKS_DB_PAGE_HEADER, true );

					if ( $elements ) {
						$post_meta_key = BRICKS_DB_PAGE_HEADER;
					} else {
						$elements = get_post_meta( $post_id, BRICKS_DB_PAGE_FOOTER, true );

						if ( $elements ) {
							$post_meta_key = BRICKS_DB_PAGE_FOOTER;
						}
					}
				}

				// No 'header', nor footer' data: Check for 'content' post meta
				if ( ! $elements ) {
					$elements = get_post_meta( $post_id, BRICKS_DB_PAGE_CONTENT, true );

					if ( $elements ) {
						$post_meta_key = BRICKS_DB_PAGE_CONTENT;
					}
				}

				if ( $elements && $post_meta_key ) {
					$converter_response = self::convert( $elements, $post_id, $convert );

					// Update post if change was made (check: count)
					if ( isset( $converter_response['count'] ) && $converter_response['count'] > 0 ) {
						$elements = is_array( $converter_response['data'] ) ? $converter_response['data'] : false;

						if ( $elements ) {
							// Update Bricks data post meta
							$updated[ $data ] = update_post_meta( $post_id, $post_meta_key, $elements );

							// Generate label to show in Bricks settings
							$post_type_object = get_post_type_object( $post_type );
							$post_type        = $post_type_object ? $post_type_object->labels->singular_name : $post_type;

							$label = "$post_type: " . get_the_title( $post_id );
						}
					}

					// Convert: Page settings
					$page_settings = get_post_meta( $post_id, BRICKS_DB_PAGE_SETTINGS, true );

					if ( $page_settings ) {
						$converter_response = self::convert( $page_settings, 'pageSettings', $convert );

						if ( isset( $converter_response['count'] ) && $converter_response['count'] > 0 ) {
							$updated[ $data ] = update_post_meta( $post_id, BRICKS_DB_PAGE_SETTINGS, $converter_response['data'] );

							if ( $label ) {
								$label .= ' (+ ' . esc_html__( 'Page settings', 'bricks' ) . ')';
							} else {
								$post_type_object = get_post_type_object( $post_type );
								$post_type        = $post_type_object ? $post_type_object->labels->singular_name : $post_type;

								$label = "$post_type: " . get_the_title( $post_id ) . ' (' . esc_html__( 'Page settings', 'bricks' ) . ')';
							}
						}
					}
				}
		}

		wp_send_json_success(
			[
				'data'    => $data,
				'updated' => $updated,
				'label'   => $label,
			]
		);
	}

	/**
	 * Convert plain element to nestable elements
	 *
	 * Slider > Nestable slider
	 * Testimonial > Nestable slider
	 * Carousel > Nestable slider
	 *
	 * @return array
	 *
	 * @since 1.5
	 */
	private function convert_to_nestable_elements( $elements, $count ) {
		foreach ( $elements as $index => $element ) {
			// 'slider', 'testimonial', 'carousel' to 'slider-nested'
			if ( in_array( $element['name'], [ 'slider', 'testimonial', 'carousel' ] ) ) {
				$nestable_elements  = self::convert_to_nestable_slider( $element );
				$elements_to_remove = 1;
				array_splice( $elements, $index, $elements_to_remove, $nestable_elements );
				array_values( $elements );
				$count += 1;
			}
		}

		return [
			'elements' => $elements,
			'count'    => $count,
		];
	}

	/**
	 * Convert slider/testimonials element to nestable slider
	 *
	 * @return array $elements Elements array with new nestable slider + child elements.
	 *
	 * @since 1.5
	 */
	private function convert_to_nestable_slider( $element ) {
		$element_name      = $element['name'];
		$settings          = $element['settings'];
		$nestable_elements = [];

		$slider = [
			'id'       => $element['id'],
			'parent'   => $element['parent'],
			'name'     => 'slider-nested',
			'children' => [],
			'settings' => [],
		];

		if ( ! empty( $element['global'] ) ) {
			$slider['global'] = $element['global'];
		}

		if ( $element_name === 'slider' ) {
			if ( ! empty( $settings['contentAlignHorizontal'] ) ) {
				$nestable_element['settings']['slideAlignHorizontal'] = $settings['contentAlignHorizontal'];
			}

			if ( ! empty( $settings['contentAlignVertical'] ) ) {
				$nestable_element['settings']['slideAlignVertical'] = $settings['contentAlignVertical'];
			}
		} elseif ( $element_name === 'testimonials' ) {
			$slider['label'] = esc_html__( 'Testimonials', 'bricks' ) . ' (' . esc_html__( 'Nestable', 'bricks' ) . ')';
		} elseif ( $element_name === 'carousel' ) {
			$slider['label'] = esc_html__( 'Carousel', 'bricks' ) . ' (' . esc_html__( 'Nestable', 'bricks' ) . ')';
		}

		$slides   = ! empty( $settings['items'] ) ? $settings['items'] : [];
		$has_loop = isset( $settings['hasLoop'] );

		// Carousel: No individual slides (media: 'items' OR query: 'query')
		if ( $element_name === 'carousel' ) {
			$carousel_type = ! empty( $settings['type'] ) ? $settings['type'] : 'media';

			if ( $carousel_type === 'media' ) {
				$slides = ! empty( $settings['items']['images'] ) ? $settings['items']['images'] : [];
			}

			// Don't merge into new 'slider-nested', which has it's own 'type' setting
			unset( $settings['type'] );

			if ( $carousel_type === 'posts' ) {
				$has_loop = true;
				$slides   = ! empty( $settings['fields'] ) ? $settings['fields'] : [];
			}
		}

		// STEP: Generate individual slides
		foreach ( $slides as $index => $slide ) {
			// Is query loop: Skip any slide after first one
			if ( $has_loop && $index !== 0 ) {
				continue;
			}

			$child_label = esc_html__( 'Slide', 'bricks' );

			if ( $element_name === 'testimonials' ) {
				$child_label = esc_html__( 'Testimonial', 'bricks' );
			}

			// Direct child element (= slide)
			$child_element = [
				'id'       => Helpers::generate_random_id( false ),
				'name'     => 'block',
				'label'    => $child_label,
				'parent'   => $element['id'],
				'children' => [],
				'settings' => [],
			];

			// Populate first child div element with 'hasLoop' & 'query' settings
			if ( $has_loop ) {
				$child_element['settings']['hasLoop'] = true;

				if ( ! empty( $settings['query'] ) ) {
					$child_element['settings']['query'] = $settings['query'];
				}
			}

			// Add new Div element ID to nestable 'children'
			$slider['children'][] = $child_element['id'];

			// STEP: Convert in-slide elements
			if ( $element_name === 'slider' ) {
				if ( ! empty( $slide['background'] ) ) {
					$child_element['settings']['_background'] = $slide['background'];
				}

				// Heading element = 'title'
				if ( ! empty( $slide['title'] ) ) {
					$heading_element = [
						'id'       => Helpers::generate_random_id( false ),
						'name'     => 'heading',
						'parent'   => $child_element['id'],
						'settings' => [
							'text' => $slide['title'],
						],
					];

					if ( ! empty( $slide['titleTag'] ) ) {
						$heading_element['settings']['tag'] = $slide['titleTag'];
					}

					$child_element['children'][] = $heading_element['id'];

					$nestable_elements[] = $heading_element;
				}

				// Text element = 'content'
				if ( ! empty( $slide['content'] ) ) {
					$text_element = [
						'id'       => Helpers::generate_random_id( false ),
						'name'     => 'text',
						'parent'   => $child_element['id'],
						'settings' => [
							'text' => $slide['content'],
						],
					];

					$child_element['children'][] = $text_element['id'];

					$nestable_elements[] = $text_element;
				}

				// Button element = 'buttonText'
				if ( ! empty( $slide['buttonText'] ) ) {
					$button_element = [
						'id'       => Helpers::generate_random_id( false ),
						'name'     => 'button',
						'parent'   => $child_element['id'],
						'settings' => [
							'text' => $slide['buttonText'],
						],
					];

					if ( ! empty( $slide['buttonStyle'] ) ) {
						$button_element['settings']['style'] = $slide['buttonStyle'];
					}

					if ( ! empty( $slide['buttonSize'] ) ) {
						$button_element['settings']['size'] = $slide['buttonSize'];
					}

					if ( ! empty( $slide['buttonWidth'] ) ) {
						$button_element['settings']['_widthMin'] = $slide['buttonWidth'];
					}

					if ( ! empty( $slide['buttonLink'] ) ) {
						$button_element['settings']['link'] = $slide['buttonLink'];
					}

					if ( ! empty( $slide['buttonBackground'] ) ) {
						$button_element['settings']['_background'] = [
							'color' => $slide['buttonBackground'],
						];
					}

					if ( ! empty( $slide['buttonBorder'] ) ) {
						$button_element['settings']['_border'] = $slide['buttonBorder'];
					}

					if ( ! empty( $slide['buttonBoxShadow'] ) ) {
						$button_element['settings']['_boxShadow'] = $slide['buttonBoxShadow'];
					}

					if ( ! empty( $slide['buttonTypography'] ) ) {
						$button_element['settings']['_typography'] = $slide['buttonTypography'];
					}

					$child_element['children'][] = $button_element['id'];

					$nestable_elements[] = $button_element;
				}
			} elseif ( $element_name === 'testimonials' ) {
				// Text element = 'content'
				if ( ! empty( $slide['content'] ) ) {
					$text_element = [
						'id'       => Helpers::generate_random_id( false ),
						'name'     => 'text',
						'label'    => esc_html__( 'Content', 'bricks' ),
						'parent'   => $child_element['id'],
						'settings' => [
							'text' => $slide['content'],
						],
					];

					$child_element['children'][] = $text_element['id'];

					$nestable_elements[] = $text_element;
				}

				// Heading element = 'name'
				if ( ! empty( $slide['name'] ) ) {
					$heading_element = [
						'id'       => Helpers::generate_random_id( false ),
						'name'     => 'heading',
						'label'    => esc_html__( 'Name', 'bricks' ),
						'parent'   => $child_element['id'],
						'settings' => [
							'text' => $slide['name'],
							'tag'  => 'h5',
						],
					];

					$child_element['children'][] = $heading_element['id'];

					$nestable_elements[] = $heading_element;
				}

				// Text basic element = 'title'
				if ( ! empty( $slide['title'] ) ) {
					$text_element = [
						'id'       => Helpers::generate_random_id( false ),
						'name'     => 'text-basic',
						'label'    => esc_html__( 'Title', 'bricks' ),
						'parent'   => $child_element['id'],
						'settings' => [
							'text' => $slide['title'],
						],
					];

					$child_element['children'][] = $text_element['id'];

					$nestable_elements[] = $text_element;
				}

				// Image element = 'image'
				if ( ! empty( $slide['image'] ) ) {
					$image_element = [
						'id'       => Helpers::generate_random_id( false ),
						'name'     => 'image',
						'parent'   => $child_element['id'],
						'settings' => [
							'image'   => $slide['image'],
							'_width'  => ! empty( $settings['imageSize'] ) ? $settings['imageSize'] : '60px',
							'_height' => ! empty( $settings['imageSize'] ) ? $settings['imageSize'] : '60px',
						],
					];

					if ( ! empty( $settings['imageBorder'] ) ) {
						$image_element['settings']['_border'] = $settings['imageBorder'];
					}

					if ( ! empty( $settings['imageBoxShadow'] ) ) {
						$image_element['settings']['_boxShadow'] = $settings['imageBoxShadow'];
					}

					$child_element['children'][] = $image_element['id'];

					$nestable_elements[] = $image_element;
				}
			}

			// Carousel type 'media' (= image gallery)
			elseif ( $element_name === 'carousel' && $carousel_type === 'media' ) {
				$image_element = [
					'id'       => Helpers::generate_random_id( false ),
					'name'     => 'image',
					'parent'   => $child_element['id'],
					'settings' => [
						'image' => $slide,
					],
				];

				$child_element['children'][] = $image_element['id'];

				$nestable_elements[] = $image_element;
			}

			// Carousel type 'posts' (= query)
			elseif ( $element_name === 'carousel' && $carousel_type === 'posts' && ! empty( $settings['fields'] ) ) {
				// Loop over fields to create per-slide  elements
				foreach ( $settings['fields'] as $field ) {
					$html_tag   = ! empty( $field['tag'] ) ? $field['tag'] : 'div';
					$text       = ! empty( $field['dynamicData'] ) ? $field['dynamicData'] : false;
					$is_heading = is_numeric( $html_tag );

					if ( ! $text ) {
						continue;
					}

					$field_element = [
						'id'       => Helpers::generate_random_id( false ),
						'name'     => $is_heading ? 'heading' : 'text-basic',
						'parent'   => $child_element['id'],
						'settings' => [
							'text' => $text,
						],
					];

					if ( $html_tag ) {
						$field_element['settings']['tag'] = $html_tag;
					}

					if ( ! empty( $field['dynamicMargin'] ) ) {
						$field_element['settings']['_margin'] = $field['dynamicMargin'];
					}

					if ( ! empty( $field['dynamicPadding'] ) ) {
						$field_element['settings']['_padding'] = $field['dynamicPadding'];
					}

					if ( ! empty( $field['dynamicBackground'] ) ) {
						$field_element['settings']['_background'] = [
							'color' => $field['dynamicBackground'],
						];
					}

					if ( ! empty( $field['dynamicBorder'] ) ) {
						$field_element['settings']['_border'] = $field['dynamicBorder'];
					}

					if ( ! empty( $field['dynamicTypography'] ) ) {
						$field_element['settings']['_typography'] = $field['dynamicTypography'];
					}

					$child_element['children'][] = $field_element['id'];

					$nestable_elements[] = $field_element;
				}
			}

			$nestable_elements[] = $child_element;
		}

		// STEP: Get slider settings by checking against nestable slider controls for all breakpoints (swiperJS to splideJS)
		$element_controls = Elements::get_element( [ 'name' => 'slider-nested' ], 'controls' );

		foreach ( array_keys( $element_controls ) as $control_key ) {
			if ( ! empty( $settings[ $control_key ] ) ) {
				$slider['settings'][ $control_key ] = $settings[ $control_key ];
			}

			foreach ( Setup::$breakpoints as $breakpoint ) {
				$control_key = "{$control_key}:{$breakpoint['key']}";

				if ( ! empty( $settings[ $control_key ] ) ) {
					$slider['settings'][ $control_key ] = $settings[ $control_key ];
				}
			}
		}

		// STEP: Map old slider swiperJS keys to new slider-nested splideJS keys
		$swiper_to_splide = [
			'height'          => 'height',
			'gutter'          => 'gap',
			'initialSlide'    => 'start',
			'slidesToShow'    => 'perPage',
			'slidesToScroll'  => 'perMove',
			'adaptiveHeight'  => 'autoHeight',
			'autoplay'        => 'autoplay',
			'pauseOnHover'    => 'pauseOnHover',
			'autoplaySpeed'   => 'interval',
			'speed'           => 'speed',

			'arrows'          => 'arrows',
			'arrowHeight'     => 'arrowHeight',
			'arrowWidth'      => 'arrowWidth',
			'arrowBackground' => 'arrowBackground',
			'arrowBorder'     => 'arrowBorder',
			'arrowTypography' => 'arrowTypography',

			'prevArrow'       => 'prevArrow',
			'prevArrowTop'    => 'prevArrowTop',
			'prevArrowRight'  => 'prevArrowRight',
			'prevArrowBottom' => 'prevArrowBottom',
			'prevArrowLeft'   => 'prevArrowLeft',

			'nextArrow'       => 'nextArrow',
			'nextArrowTop'    => 'nextArrowTop',
			'nextArrowRight'  => 'nextArrowRight',
			'nextArrowBottom' => 'nextArrowBottom',
			'nextArrowLeft'   => 'nextArrowLeft',

			'dots'            => 'pagination',
			'dotsSpacing'     => 'paginationSpacing',
			'dotsHeight'      => 'paginationHeight',
			'dotsWidth'       => 'paginationWidth',
			'dotsColor'       => 'paginationColor',
			'dotsBorder'      => 'paginationBorder',
			'dotsActiveColor' => 'paginationColorActive',
			'dotsTop'         => 'paginationTop',
			'dotsRight'       => 'paginationRight',
			'dotsBottom'      => 'paginationBottom',
			'dotsLeft'        => 'paginationLeft',
		];

		foreach ( $swiper_to_splide as $swiper_key => $splide_key ) {
			if ( ! empty( $settings[ $swiper_key ] ) ) {
				$slider['settings'][ $splide_key ] = $settings[ $swiper_key ];
			}

			foreach ( Setup::$breakpoints as $breakpoint ) {
				$control_key = "{$swiper_key}:{$breakpoint['key']}";

				if ( ! empty( $settings[ $control_key ] ) ) {
					$slider['settings'][ "{$splide_key}:{$breakpoint['key']}" ] = $settings[ $control_key ];
				}
			}
		}

		if ( isset( $settings['infinite'] ) ) {
			$slider['settings']['type']    = 'loop';
			$slider['settings']['perPage'] = 1;
			$slider['settings']['perMove'] = 1;
		}

		array_unshift( $nestable_elements, $slider );

		return $nestable_elements;
	}

	/**
	 * Convert: elementClasses, nestableElements
	 *
	 * @param string $data Source string to apply search & replace for.
	 * @param string $source themeStyles, globalSettings, globalClasses, globalElements, pageSettings, $post_id.
	 * @param array  $convert elementClasses, nestableElements, contaner.
	 *
	 * @return string
	 *
	 * @since 1.4
	 */
	private function convert( $data, $source, $convert ) {
		if ( ! $data ) {
			return $data;
		}

		$count = 0;

		/**
		 * STEP: Convert element IDs & class name 'bricks-element-' to 'brxe-'
		 *
		 * @since 1.4
		 */

		if ( in_array( 'elementClasses', $convert ) ) {
			// Check if data is array: JSON encode to string > convert > decode back to array
			$is_array = is_array( $data );

			if ( $is_array ) {
				$data = json_encode( $data );
			}

			// Search for (key) & replace with (value)
			$search_replace = [
				'#bricks-element-'  => '#brxe-',
				'.bricks-element-'  => '.brxe-',
				'#bricks-header'    => '#brx-header',
				'#bricks-content'   => '#brx-content',
				'#bricks-footer'    => '#brx-footer',

				// All elements use brxe- class prefix (@since 1.5)
				'.bricks-container' => '.brxe-container',
				'.brx-container'    => '.brxe-container',
			];

			foreach ( $search_replace as $search => $replace ) {
				$data = str_replace( $search, $replace, $data, $number_of_replacements_made );

				$count += $number_of_replacements_made;
			}

			if ( $is_array ) {
				$data = json_decode( $data, true );
			}
		}

		/**
		 * STEP: Convert elements to nestable elements
		 *
		 * - Plain 'slider', 'testimonials', 'carousel' element to 'slider-nested' element
		 *
		 * @since 1.?
		 */
		// if ( in_array( 'nestableElements', $convert ) && is_array( $data ) && is_numeric( $source ) ) {
		// $converter_response = self::convert_to_nestable_elements( $data, $count );
		// $data               = $converter_response['elements'];
		// $count              = $converter_response['count'];
		// }

		/**
		 * STEP: Add 'position' = 'relative' if top/right/bottom/left/_zIndex value is set
		 *
		 * As default now is relative: static
		 *
		 * @since 1.?
		 *
		 * NOTE: Not yet in use!
		 */
		// if ( is_array( $data ) && ( $source === 'globalClasses' || $source === 'globalElements' || is_numeric( $source ) ) ) {
		// $elements = $data;

		// $directions = ['_top', '_right', '_bottom', '_left', '_zIndex'];

		// foreach ( $elements as $index => $element ) {
		// $element_settings = ! empty( $element['settings'] ) ? $element['settings'] : [];

		// foreach ( $element_settings as $setting_key => $value ) {
		// Has a direction ('_top', '_right', '_bottom', '_left'), but no '_position' setting: Set position to relative
		// foreach ( $directions as $direction ) {
		// Setting starts with check (to capture all breakpoint & pseudo-class settings, etc.)
		// if ( strpos( $setting_key, $direction ) === 0 ) {
		// $position_key = str_replace( $direction, '_position', $setting_key );

		// if ( empty( $element_settings[$position_key] ) ) {
		// $elements[$index]['settings'][$position_key] = 'relative';
		// $count += 1;
		// }
		// }
		// }
		// }
		// }

		// $data = $elements;
		// }

		/**
		 * STEP: Convert 'container' to 'section' & 'block' element & theme styles
		 *
		 * @since 1.5
		 */
		if ( in_array( 'container', $convert ) ) {
			/**
			 * - Stretched root 'container' to 'section' element
		   * - 'container' inside 'container' to 'block' element
			 */
			if ( is_array( $data ) && is_numeric( $source ) ) {
				$elements = $data;

				$response = self::convert_container_to_section_block_element( $elements );
				$data     = $response['elements'];
				$count    = $response['count'];
			}

			/**
			 * Theme styles
			 */
			elseif ( $source === 'themeStyles' ) {
				$theme_styles = $data;

				foreach ( $theme_styles as $style_id => $style ) {
					$settings           = ! empty( $style['settings'] ) ? $style['settings'] : [];
					$section_settings   = ! empty( $settings['section'] ) ? $settings['section'] : [];
					$container_settings = ! empty( $settings['container'] ) ? $settings['container'] : [];

					foreach ( $settings as $group => $group_settings ) {
						switch ( $group ) {
							case 'general':
								foreach ( $group_settings as $key => $value ) {
									// Root container margin to section margin
									if ( $key === 'sectionMargin' ) {
										$section_settings['margin'] = $value;
										$count                     += 1;

										unset( $theme_styles[ $style_id ]['settings'][ $group ][ $key ] );
									}

									// Root container padding to section padding
									elseif ( $key === 'sectionPadding' ) {
										$section_settings['padding'] = $value;
										$count                      += 1;

										unset( $theme_styles[ $style_id ]['settings'][ $group ][ $key ] );
									}

									// Root container max-width to container width
									elseif ( $key === 'containerMaxWidth' ) {
										$container_settings['width'] = $value;
										$count                      += 1;

										unset( $theme_styles[ $style_id ]['settings'][ $group ][ $key ] );
									}
								}
								break;
						}
					}

					if ( count( $section_settings ) ) {
						$theme_styles[ $style_id ]['settings']['section'] = $section_settings;
					}

					if ( count( $container_settings ) ) {
						$theme_styles[ $style_id ]['settings']['container'] = $container_settings;
					}
				}

				$data = $theme_styles;
			}
		}

		return [
			'count' => $count,
			'data'  => $data,
		];
	}

	public static function convert_container_to_section_block_element( $elements = [] ) {
		$converted_container_ids = [];
		$count                   = 0;

		foreach ( $elements as $index => $element ) {
			// Skip non-container elements
			if ( $element['name'] !== 'container' ) {
				continue;
			}

			$parent_id = $element['parent'];

			// STEP: Stretched or 100%/vw width root container to section
			if ( $parent_id == '0' ) {
				if (
					( ! empty( $element['settings']['_alignSelf'] ) && $element['settings']['_alignSelf'] === 'stretch' ) ||
					( ! empty( $element['settings']['_width'] ) && in_array( $element['settings']['_width'], [ '100%', '100vw' ] ) ) ||
					( ! empty( $element['settings']['_widthMin'] ) && in_array( $element['settings']['_widthMin'], [ '100%', '100vw' ] ) ) ||
					( ! empty( $element['settings']['_widthMax'] ) && in_array( $element['settings']['_widthMax'], [ '100%', '100vw' ] ) )
					) {
					$elements[ $index ]['name'] = 'section';

					if ( ! isset( $element['label'] ) || $element['label'] === 'Container' ) {
						unset( $elements[ $index ]['label'] );
					}

					$count += 1;
				}
			}

			// Child 'container' inside 'container' to 'block' element
			elseif ( $parent_id != '0' ) {
				$parent_index   = array_search( $parent_id, array_column( $elements, 'id' ) );
				$parent_element = ! empty( $elements[ $parent_index ] ) ? $elements[ $parent_index ] : false;
				$parent_name    = ! empty( $parent_element['name'] ) ? $parent_element['name'] : false;

				if (
					( $parent_name === 'container' ) || // Convert if parent is 'container'
					( $parent_name === 'block' && in_array( $parent_element['id'], $converted_container_ids ) ) // Convert if parent 'id' was converted from 'container' to 'block'
				) {
					$elements[ $index ]['name'] = 'block';

					if ( ! isset( $element['label'] ) || $element['label'] === 'Container' ) {
						unset( $elements[ $index ]['label'] );
					}

					if ( ! empty( $elements[ $index ]['id'] ) ) {
						$converted_container_ids[] = $elements[ $index ]['id'];
					}

					$count += 1;
				}
			}
		}

		return [
			'elements' => $elements,
			'count'    => $count,
		];
	}
}
