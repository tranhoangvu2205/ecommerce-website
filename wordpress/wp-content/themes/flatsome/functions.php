<?php
/* thay doi qickview => xem nhanh */
		function my_custom_translations( $strings ) {
		$text = array(
		'Quick View' => 'Chi tiết'
		);
		$strings = str_ireplace( array_keys( $text ), $text, $strings );
		return $strings;
		}
		add_filter( 'gettext', 'my_custom_translations', 20 );
		/* thay doi qickview => xem nhanh */

require get_template_directory() . '/inc/init.php';

/**
 * Note: It's not recommended to add any custom code here. Please use a child theme so that your customizations aren't lost during updates.
 * Learn more here: http://codex.wordpress.org/Child_Themes
 */
