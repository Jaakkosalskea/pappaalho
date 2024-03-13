<?php
namespace Bricks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Element_Countdown extends Element {
	public $category     = 'general';
	public $name         = 'countdown';
	public $icon         = 'ti-timer';
	public $css_selector = '.field';
	public $scripts      = [ 'bricksCountdown' ];

	public function get_label() {
		return esc_html__( 'Countdown', 'bricks' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'bricks-countdown' );
	}

	public function set_controls() {
		$this->controls['date'] = [
			'tab'      => 'content',
			'label'    => esc_html__( 'Date', 'bricks' ),
			'type'     => 'datepicker',
			'default'  => '2023-01-01 12:00',
			'rerender' => true,
		];

		$this->controls['fields'] = [
			'tab'           => 'content',
			'label'         => esc_html__( 'Fields', 'bricks' ),
			'type'          => 'repeater',
			'titleProperty' => 'format',
			'placeholder'   => esc_html__( 'Field', 'bricks' ),
			'fields'        => [
				'prefix' => [
					'label'  => esc_html__( 'Prefix', 'bricks' ),
					'type'   => 'text',
					'inline' => true,
				],

				'format' => [
					'label'       => esc_html__( 'Format', 'bricks' ),
					'type'        => 'text',
					'placeholder' => '%D',
					'inline'      => true,
					'info'        => '%D, %H, %M, %S (' . esc_html__( 'Lowercase removes leading zeros', 'bricks' ) . ')',
				],

				'suffix' => [
					'label'  => esc_html__( 'Suffix', 'bricks' ),
					'type'   => 'text',
					'inline' => true,
				],
			],
			'default'       => [
				[ 'format' => '%D days' ],
				[ 'format' => '%H hours' ],
				[ 'format' => '%M minutes' ],
				[ 'format' => '%S seconds' ],
			],
			'rerender'      => true,
		];

		$this->controls['gutter'] = [
			'tab'     => 'content',
			'label'   => esc_html__( 'Spacing', 'bricks' ),
			'type'    => 'dimensions',
			'css'     => [
				[
					'property' => 'margin',
					'selector' => '.field',
				],
			],
			'default' => [
				'top'    => 0,
				'right'  => 5,
				'bottom' => 0,
				'left'   => 0,
			],
		];

		$this->controls['flexDirectionFields'] = [
			'tab'       => 'content',
			'label'     => esc_html__( 'Direction', 'bricks' ) . ' (' . esc_html__( 'Fields', 'bricks' ) . ')',
			'type'      => 'direction',
			'direction' => 'row',
			'css'       => [
				[
					'property' => 'flex-direction',
					'selector' => '',
				],
			],
			'inline'    => true,
		];

		$this->controls['flexDirection'] = [
			'tab'       => 'content',
			'label'     => esc_html__( 'Direction', 'bricks' ) . ' (' . esc_html__( 'Field', 'bricks' ) . ')',
			'type'      => 'direction',
			'direction' => 'column',
			'css'       => [
				[
					'property' => 'flex-direction',
					'selector' => '.field',
				],
			],
			'inline'    => true,
		];

		// COUNTDOWN DATE REACHED

		$this->controls['actionSeparator'] = [
			'tab'   => 'content',
			'label' => esc_html__( 'Date Reached', 'bricks' ),
			'type'  => 'separator',
		];

		$this->controls['action'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Show', 'bricks' ),
			'type'        => 'select',
			'options'     => [
				'countdown' => esc_html__( 'Countdown', 'bricks' ),
				'hide'      => esc_html__( 'Hide', 'bricks' ),
				'text'      => esc_html__( 'Custom text', 'bricks' ),
			],
			'placeholder' => esc_html__( 'Countdown', 'bricks' ),
			'inline'      => true,
			'rerender'    => true,
		];

		$this->controls['actionText'] = [
			'tab'            => 'content',
			'label'          => esc_html__( 'Custom text', 'bricks' ),
			'type'           => 'text',
			'hasDynamicData' => false,
			'placeholder'    => esc_html__( 'Countdown', 'bricks' ),
			// 'trigger'        => [ 'blur', 'enter' ],
			'required'       => [ 'action', '=', 'text' ],
			'rerender'       => true,
		];

		// STYLE: TYPOGRAPHY

		// Remove default '_typography'
		unset( $this->controls['_typography'] );

		$this->controls['typography'] = [
			'tab'   => 'style',
			'group' => '_typography',
			'label' => esc_html__( 'Typography', 'bricks' ),
			'type'  => 'typography',
			'css'   => [
				[
					'property' => 'font',
				],
			],
		];

		$this->controls['typographyPrefix'] = [
			'tab'   => 'style',
			'group' => '_typography',
			'label' => esc_html__( 'Typography', 'bricks' ) . ' (' . esc_html__( 'Prefix', 'bricks' ) . ')',
			'type'  => 'typography',
			'css'   => [
				[
					'property' => 'font',
					'selector' => '.prefix',
				],
			],
		];

		$this->controls['typographySuffix'] = [
			'tab'   => 'style',
			'group' => '_typography',
			'label' => esc_html__( 'Typography', 'bricks' ) . ' (' . esc_html__( 'Suffix', 'bricks' ) . ')',
			'type'  => 'typography',
			'css'   => [
				[
					'property' => 'font',
					'selector' => '.suffix',
				],
			],
		];
	}

	public function render() {
		$settings    = $this->settings;
		$date        = ! empty( $settings['date'] ) ? $settings['date'] : false;
		$fields      = ! empty( $settings['fields'] ) ? $settings['fields'] : false;
		$action      = ! empty( $settings['action'] ) ? $settings['action'] : 'countdown';
		$action_text = ! empty( $settings['actionText'] ) ? $settings['actionText'] : '';

		if ( ! $date || ! $fields ) {
			return $this->render_element_placeholder( [ 'title' => esc_html__( 'No date/fields set.', 'bricks' ) ] );
		}

		$this->set_attribute(
			'_root',
			'data-bricks-countdown-options',
			wp_json_encode(
				[
					'date'       => $date,
					'fields'     => $fields,
					'action'     => $action,
					'actionText' => $action_text,
				]
			)
		);

		echo "<div {$this->render_attributes( '_root' )}></div>";
	}

	public static function render_builder() { ?>
		<script type="text/x-template" id="tmpl-bricks-element-countdown">
			<component
				v-if="settings.fields && settings.date"
				:is="tag"
				:data-bricks-countdown-options="JSON.stringify({
					date: settings.date,
					fields: settings.fields,
					action: settings.action ? settings.action : 'countdown',
					actionText: settings.actionText ? settings.actionText : ''
				})">
			</component>

			<div v-else v-html="renderElementPlaceholder()"></div>
		</script>
		<?php
	}
}
