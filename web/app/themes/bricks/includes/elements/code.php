<?php
namespace Bricks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Element_Code extends Element {
	public $block    = [ 'core/code', 'core/preformatted' ];
	public $category = 'general';
	public $name     = 'code';
	public $icon     = 'ion-ios-code';
	public $scripts  = [ 'bricksPrettify' ];

	public function enqueue_scripts() {
		if ( ! empty( $this->theme_styles['prettify'] ) || ! empty( $this->settings['prettify'] ) ) {
			wp_enqueue_script( 'bricks-prettify' );
			wp_enqueue_style( 'bricks-prettify' );
		}
	}

	public function get_label() {
		return esc_html__( 'Code', 'bricks' );
	}

	public function get_keywords() {
		return [ 'snippet' ];
	}

	public function set_controls() {
		$this->controls['code'] = [
			'tab'       => 'content',
			'type'      => 'code',
			'mode'      => 'php', // 'css', 'javascript', 'php',
			'clearable' => false, // Required to always have 'mode' set for CodeMirror
			'default'   => "<style>\nh1.my-heading {\n  color: crimson;\n}\n</style>\n\n<h1 class='my-heading'>Just some custom HTML</h1>",
			'required'  => [ 'useDynamicData', '=', '' ],
			'rerender'  => true,
		];

		// NOTE: Undocumented (enable code execution first under: Bricks > Settings > Builder Access)
		$execution_allowed = apply_filters( 'bricks/code/allow_execution', ! Database::get_setting( 'executeCodeDisabled', false ) );

		if ( $execution_allowed ) {
			$this->controls['executeCode'] = [
				'tab'      => 'content',
				'label'    => esc_html__( 'Execute code', 'bricks' ),
				'type'     => 'checkbox',
				'required' => [ 'useDynamicData', '=', '' ],
			];

			if ( ! current_user_can( Capabilities::EXECUTE_CODE ) ) {
				$this->controls['infoExecuteCodeOff'] = [
					'tab'     => 'content',
					'content' => esc_html__( 'You can manage code execution permissions under: Bricks > Settings > Builder Access > Code Execution', 'bricks' ),
					'type'    => 'info'
				];
			}

			$this->controls['infoExecuteCode'] = [
				'tab'      => 'content',
				'content'  => esc_html__( 'The code above will be executed on your site! Proceed with care and use only trusted code that you deem safe.', 'bricks' ),
				'type'     => 'info',
				'required' => [ 'executeCode', '!=', '' ]
			];
		}

		$this->controls['infoExecuteCode'] = [
			'tab'      => 'content',
			'content'  => esc_html__( 'Important: The code above will run on your site! Only add code that you consider safe. Especially when executing PHP & JavaSript code.', 'bricks' ),
			'type'     => 'info',
			'required' => [ 'executeCode', '!=', '' ]
		];

		$this->controls['useDynamicData'] = [
			'tab'                  => 'content',
			'label'                => '',
			'type'                 => 'text',
			'placeholder'          => esc_html__( 'Select dynamic data', 'bricks' ),
			'fetchContentOnCanvas' => true, // NOTE: Undocumented. When picking, fetch content for preview. (by default for link and video, needed for text field types)
			'required'             => [ 'executeCode', '=', '' ]
		];

		$this->controls['language'] = [
			'tab'            => 'content',
			'label'          => esc_html__( 'Language', 'bricks' ),
			'type'           => 'text',
			'hasDynamicData' => false,
			'placeholder'    => esc_html__( 'Auto detect', 'bricks' ),
			'description'    => esc_html__( 'Set language if auto detect fails (e.g. "css").', 'bricks' ),
			'required'       => [ 'executeCode', '=', '' ],
		];

		$this->controls['infoBeautify'] = [
			'tab'      => 'content',
			'content'  => esc_html__( 'Beautify your code under: Settings > Theme Styles > Element - Code', 'bricks' ),
			'type'     => 'info',
			'required' => [ 'executeCode', '=', '' ],
		];
	}

	public function render() {
		$settings = $this->settings;

		$code = ! empty( $settings['code'] ) ? $settings['code'] : false;

		if ( empty( $code ) ) {
			return $this->render_element_placeholder( [ 'title' => esc_html__( 'No code found.', 'bricks' ) ] );
		}

		// STEP: Execute code
		if ( ! empty( $settings['executeCode'] ) ) {
			$execution_allowed = apply_filters( 'bricks/code/allow_execution', ! Database::get_setting( 'executeCodeDisabled', false ) );

			// Return: Code execution not allowed
			if ( ! $execution_allowed ) {
				return $this->render_element_placeholder(
					[
						'title'       => esc_html__( 'Code execution not allowed.', 'bricks' ),
						'description' => esc_html__( 'You can manage code execution permissions under: Bricks > Settings > Builder Access > Code Execution', 'bricks' )
					]
				);
			}

			/**
			 * Filter $code content to prevent dangerous calls
			 *
			 * @since 1.3.7
			 */
			$disallow = apply_filters( 'bricks/code/disallow_keywords', [] );

			if ( ! empty( $disallow ) ) {
				foreach ( (array) $disallow as $keyword ) {
					if ( stripos( $code, $keyword ) !== false ) {
						return $this->render_element_placeholder(
							[
								'title'       => esc_html__( 'Code is not executed as it contains the following disallowed keyword', 'bricks' ) . ': ' . $keyword,
								'description' => Helpers::article_link( 'filter-bricks-code-disallow_keywords', esc_html__( 'Visit Bricks Academy', 'bricks' ) )
							]
						);
					}
				}
			}

			// Sets context on AJAX/REST API calls or when reloading the builder
			if ( bricks_is_builder() || bricks_is_builder_call() ) {
				global $post;

				$post = get_post( $this->post_id );

				setup_postdata( $post );
			}

			ob_start();

			// Prepare & set error reporting
			$error_reporting = error_reporting( E_ALL );
			$display_errors  = ini_get( 'display_errors' );
			ini_set( 'display_errors', 1 );

			try {
				$result = eval( ' ?>' . $code . '<?php ' );
			} catch ( \Exception $e ) {
				echo 'Exception: ' . $error->getMessage();

				return;
			} catch ( \ParseError $error ) {
				echo 'ParseError: ' . $error->getMessage();

				return;
			} catch ( \Error $error ) {
				echo 'Error: ' . $error->getMessage();

				return;
			}

			// Reset error reporting
			ini_set( 'display_errors', $display_errors );
			error_reporting( $error_reporting );

			// @see https://www.php.net/manual/en/function.eval.php
			if ( version_compare( PHP_VERSION, '7', '<' ) && $result === false || ! empty( $error ) ) {
				$output = $error;

				ob_end_clean();
			} else {
				$output = ob_get_clean();
			}

			if ( bricks_is_builder() || bricks_is_builder_call() ) {
				wp_reset_postdata();
			}

			echo "<div {$this->render_attributes( '_root' )}>{$output}</div>";

			return;
		}

		// Default: Print code snippet
		$theme = false;

		if ( ! empty( $this->theme_styles['prettify'] ) ) {
			$theme = $this->theme_styles['prettify'];
		} elseif ( ! empty( $settings['prettify'] ) ) {
			$theme = $settings['prettify'];
		}

		$language = ! empty( $settings['language'] ) ? ' lang-' . strtolower( $settings['language'] ) : '';

		// STEP: Get code
		if ( ! empty( $settings['useDynamicData'] ) ) {
			$code = $this->render_dynamic_data_tag( $settings['useDynamicData'] );

			if ( empty( $code ) ) {
				return $this->render_element_placeholder(
					[
						'title' => esc_html__( 'Dynamic data is empty.', 'bricks' )
					]
				);
			}
		}

		// Escaping
		$code = esc_html( $code );

		// If code comes already formatted, assure the language is set and leave
		if ( strpos( $code, '<pre' ) === 0 ) {
			$code = $theme && ! empty( $language ) ? str_replace( 'class="prettyprint', 'class="prettyprint' . $language . ' ', $code ) : $code;

			echo $code;

			return;
		}

		// Prettiprint theme set
		if ( $theme ) {
			echo "<div {$this->render_attributes( '_root' )}>";
			echo '<pre class="prettyprint ' . $theme . $language . '"><code>' . $code . '</code></pre>';
			echo '</div>';
		} else {
			// Default: Code snippet
			echo "<pre {$this->render_attributes( '_root' )}>{$code}</pre>";
		}
	}

	public function convert_element_settings_to_block( $settings ) {
		if ( $settings['executeCode'] ) {
			return;
		}

		if ( ! empty( $settings['useDynamicData'] ) ) {
			$code = $this->render_dynamic_data_tag( $settings['useDynamicData'] );

			// If code comes already formatted, extract the code only
			if ( strpos( $code, '<pre' ) === 0 ) {
				preg_match( '#<\s*?code\b[^>]*>(.*?)</code\b[^>]*>#s', $code, $matches );
				$code = isset( $matches[1] ) ? $matches[1] : $code;
			}
		} else {
			$code = isset( $settings['code'] ) ? trim( $settings['code'] ) : '';
		}

		$html = '<pre class="wp-block-code"><code>' . esc_html( $code ) . '</code></pre>';

		$block = [
			'blockName'    => 'core/code',
			'attrs'        => [],
			'innerContent' => [ $html ],
		];

		return $block;
	}

	public function convert_block_to_element_settings( $block, $attributes ) {
		$code = trim( $block['innerHTML'] );
		$code = substr( $code, strpos( $code, '>' ) + 1 ); // Remove starting <pre>
		$code = substr_replace( $code, '', -6 ); // Remove last </pre>

		// Remove <code> (core/code block)
		if ( substr( $code, 0, 6 ) === '<code>' ) {
			$code = substr( $code, strpos( $code, '>' ) + 1 ); // Remove starting <code>
			$code = substr_replace( $code, '', -7 ); // Remove last </code>
		}

		return [ 'code' => $code ];
	}
}