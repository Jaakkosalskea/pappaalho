<?php
get_header();

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();

		$post_id     = get_the_ID();
		$post_type   = get_post_type();
		$bricks_data = Bricks\Helpers::get_bricks_data( $post_id, 'content' );

		// Render Bricks data
		if ( $bricks_data ) {
			Bricks\Frontend::render_content( $bricks_data );
		}

		// Render default post layout
		elseif ( $post_type === 'post' ) {
			get_template_part( 'template-parts/post' );
		}

		// Previewing Bricks Template without content template assigned: Fallback to preview ID WordPress content
		elseif ( $post_type === BRICKS_DB_TEMPLATE_SLUG && $preview_id = Bricks\Helpers::get_template_setting( 'templatePreviewPostId', $post_id ) ) {
			echo '<main id="brx-content">' . apply_filters( 'the_content', get_post( $preview_id )->post_content ) . '</main>';
		}

		// Default content
		else {
			echo '<main id="brx-content" class="brxe-container layout-default">';

			the_content();

			wp_link_pages(
				[
					'before'      => '<div class="bricks-pagination"><ul><span class="title">' . esc_html__( 'Pages:', 'bricks' ) . '</span>',
					'after'       => '</ul></div>',
					'link_before' => '<span>',
					'link_after'  => '</span>',
				]
			);

			echo '</main>';
		}
	}
}

get_footer();
