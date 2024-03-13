<?php
namespace Bricks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Element_Icon extends Element {
	public $category = 'basic';
	public $name     = 'icon';
	public $icon     = 'ti-star';

	public function get_label() {
		return esc_html__( 'Icon', 'bricks' );
	}

	public function set_controls() {
		$this->controls['icon'] = [
			'tab'     => 'content',
			'label'   => esc_html__( 'Icon', 'bricks' ),
			'type'    => 'icon',
			'default' => [
				'library' => 'themify',
				'icon'    => 'ti-star',
			],
			'root'    => true, // To target 'svg' root
		];

		$this->controls['iconColor'] = [
			'tab'      => 'content',
			'label'    => esc_html__( 'Color', 'bricks' ),
			'type'     => 'color',
			'css'      => [
				[
					'property' => 'color',
				],
			],
			'required' => [ 'icon.icon', '!=', '' ],
		];

		$this->controls['iconSize'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Size', 'bricks' ),
			'type'        => 'number',
			'units'       => true,
			'css'         => [
				[
					'property' => 'font-size',
				],
			],
			'placeholder' => '60px',
			'required'    => [ 'icon.icon', '!=', '' ],
		];

		$this->controls['link'] = [
			'tab'   => 'content',
			'label' => esc_html__( 'Link', 'bricks' ),
			'type'  => 'link',
		];

		$this->controls['_typography']['placeholder']['font-size']   = 60;
		$this->controls['_typography']['placeholder']['line-height'] = 1;
	}

	public function render() {
		$settings = $this->settings;
		$is_svg   = ! empty( $settings['icon']['svg'] );

		if ( empty( $settings['icon'] ) ) {
			return $this->render_element_placeholder(
				[
					'title' => esc_html__( 'No icon selected.', 'bricks' ),
				]
			);
		}

		if ( ! empty( $settings['link'] ) ) {
			$this->set_attribute( '_root', 'class', $is_svg ? 'svg' : 'i' );

			$this->set_link_attributes( '_root', $settings['link'] );

			$icon = self::render_icon( $settings['icon'], [] );

			echo "<a {$this->render_attributes( '_root' )}>{$icon}</a>";
		} else {
			echo self::render_icon( $settings['icon'], $this->attributes['_root'] );
		}
	}
}
