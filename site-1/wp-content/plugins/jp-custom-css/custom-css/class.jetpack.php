<?php

class Jetpack {
	/**
	 * Get $content_width, but with a <s>twist</s> filter.
	 */
	public static function get_content_width() {
		$content_width = isset( $GLOBALS['content_width'] ) ? $GLOBALS['content_width'] : false;
		/**
		 * Filter the Content Width value.
		 *
		 * @since 2.2.3
		 *
		 * @param string $content_width Content Width value.
		 */
		return apply_filters( 'jetpack_content_width', $content_width );
	}
}