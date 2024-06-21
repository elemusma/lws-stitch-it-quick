<?php
defined( 'ABSPATH' ) || exit();

/**
 * Styling class and functions
 */
class Af_Addon_Front_Style {

	public function af_addon_front_fields_styling( $rule_id ) {

		?>
		
		<style>
			
			.af_addon_front_field_title_div_<?php echo esc_attr( $rule_id ); ?> h1, h2, h3, h4, h5, h6{
				margin-bottom: unset !important;
			}

			.add_on_description_style_<?php echo esc_attr( $rule_id ); ?>{
				font-size: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_desc_font_size', true ) ); ?>px;
				margin: 0;
			}

			.addon_field_border_<?php echo esc_attr( $rule_id ); ?>{
				<?php
				if ( '1' == get_post_meta( $rule_id, 'af_addon_field_border', true ) ) {
					?>
					border: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_field_border_pixels', true ) ); ?>px solid <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_field_border_color', true ) ); ?>;
					<?php
					if ( ! empty( get_post_meta( $rule_id, 'af_addon_field_border_top_left_radius', true ) ) ) {
						?>
						border-top-left-radius: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_field_border_top_left_radius', true ) ); ?>px;
						<?php
					}
					if ( ! empty( get_post_meta( $rule_id, 'af_addon_field_border_top_right_radius', true ) ) ) {
						?>
						border-top-right-radius: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_field_border_top_right_radius', true ) ); ?>px;
						<?php
					}
					if ( ! empty( get_post_meta( $rule_id, 'af_addon_field_border_bottom_left_radius', true ) ) ) {
						?>
						border-bottom-left-radius: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_field_border_bottom_left_radius', true ) ); ?>px;
						<?php
					}
					if ( ! empty( get_post_meta( $rule_id, 'af_addon_field_border_bottom_right_radius', true ) ) ) {
						?>
						border-bottom-right-radius: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_field_border_bottom_right_radius', true ) ); ?>px;
						<?php
					}
					if ( ! empty( get_post_meta( $rule_id, 'af_addon_field_border_top_padding', true ) ) ) {
						?>
						padding-top: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_field_border_top_padding', true ) ); ?>px;
						<?php
					}
					if ( ! empty( get_post_meta( $rule_id, 'af_addon_field_border_bottom_padding', true ) ) ) {
						?>
						padding-bottom: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_field_border_bottom_padding', true ) ); ?>px;
						<?php
					}
					if ( ! empty( get_post_meta( $rule_id, 'af_addon_field_border_left_padding', true ) ) ) {
						?>
						padding-left: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_field_border_left_padding', true ) ); ?>px;
						<?php
					}
					if ( ! empty( get_post_meta( $rule_id, 'af_addon_field_border_right_padding', true ) ) ) {
						?>
						padding-right: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_field_border_right_padding', true ) ); ?>px;
						<?php
					}
				}
				?>
				width: 100%;
			}

			.af_addon_option_font_<?php echo esc_attr( $rule_id ); ?>{
				font-size: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_option_title_font_size', true ) ); ?>px;
				color: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_option_title_font_color', true ) ); ?>;
			}

			.addon_heading_styling_<?php echo esc_attr( $rule_id ); ?>{
				<?php
				if ( '1' == get_post_meta( $rule_id, 'af_addon_title_bg', true ) ) {
					?>
					background-color: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_bg_color', true ) ); ?>;
					<?php
				}

				?>
				color: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_title_color', true ) ); ?>;
			}

			.af_addon_front_field_title_div_<?php echo esc_attr( $rule_id ); ?>{
				<?php
				if ( '1' == get_post_meta( $rule_id, 'af_addon_title_bg', true ) ) {
					?>
					background-color: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_bg_color', true ) ); ?>;
					<?php
				}
				if ( ! empty( get_post_meta( $rule_id, 'af_addon_title_top_left_radius', true ) ) ) {
					?>
					border-top-left-radius: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_title_top_left_radius', true ) ); ?>px;
					<?php
				}
				if ( ! empty( get_post_meta( $rule_id, 'af_addon_title_top_right_radius', true ) ) ) {
					?>
					border-top-right-radius: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_title_top_right_radius', true ) ); ?>px;
					<?php
				}
				if ( ! empty( get_post_meta( $rule_id, 'af_addon_title_bottom_left_radius', true ) ) ) {
					?>
					border-bottom-left-radius: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_title_bottom_left_radius', true ) ); ?>px;
					<?php
				}
				if ( ! empty( get_post_meta( $rule_id, 'af_addon_title_bottom_right_radius', true ) ) ) {
					?>
					border-bottom-right-radius: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_title_bottom_right_radius', true ) ); ?>px;
					<?php
				}
				if ( '1' == get_post_meta( $rule_id, 'af_addon_title_bg', true ) ) {

					if ( ! empty( get_post_meta( $rule_id, 'af_addon_title_top_padding', true ) ) ) {
						?>
						padding-top: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_title_top_padding', true ) ); ?>px;
						<?php
					}
					if ( ! empty( get_post_meta( $rule_id, 'af_addon_title_bottom_padding', true ) ) ) {
						?>
						padding-bottom: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_title_bottom_padding', true ) ); ?>px;
						<?php
					}
					if ( ! empty( get_post_meta( $rule_id, 'af_addon_title_left_padding', true ) ) ) {
						?>
						padding-left: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_title_left_padding', true ) ); ?>px;
						<?php
					}
					if ( ! empty( get_post_meta( $rule_id, 'af_addon_title_right_padding', true ) ) ) {
						?>
						padding-right: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_title_right_padding', true ) ); ?>px;
						<?php
					}
				}
				?>
				color: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_title_color', true ) ); ?>;
				width: 100%;
				<?php
				if ( 'af_addon_title_display_text' == get_post_meta( $rule_id, 'af_addon_title_display_as_selector', true ) ) {
					?>
					font-size: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_title_font_size', true ) ); ?>px;
					<?php
				}
				if ( 'right' == get_post_meta( $rule_id, 'af_addon_field_title_position', true ) ) {
					?>
					text-align: right;
					<?php
				} elseif ( 'center' == get_post_meta( $rule_id, 'af_addon_field_title_position', true ) ) {
					?>
					text-align: center;
					<?php
				}
				?>
				 
			}

			.tooltip_<?php echo esc_attr( $rule_id ); ?> {
				position: relative;
				display: inline-block;
				border-bottom: none !important;
				color: black !important;
				font-size: 15px;
				vertical-align: middle;
			}

			.tooltip_<?php echo esc_attr( $rule_id ); ?> .tooltiptext_<?php echo esc_attr( $rule_id ); ?> {
				visibility: hidden;
				width: 110px;
				background-color: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_tooltip_background_color', true ) ); ?>;
				color: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_tooltip_text_color', true ) ); ?>;
				font-size: <?php echo esc_attr( get_post_meta( $rule_id, 'af_addon_tooltip_font_size', true ) ); ?>px;
				text-align: center;
				border-radius: 4px;
				padding: 5px 0;

				/* Position the tooltip */
				position: absolute;
				z-index: 1;
				left: -104px;
				top: -30px;
				height: auto;
			}

			.tooltip_<?php echo esc_attr( $rule_id ); ?>:hover .tooltiptext_<?php echo esc_attr( $rule_id ); ?> {
				visibility: visible;
			}

		</style>
		
		<?php
	}
}
