<?php
class WC_MomoPay_Helper {

	/**
	 * 
	 */
	private static $plugin_slug = 'momopay_';

	public static function pluginInit() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		global $wpdb;
		global $payments_db_version;
		
		$payments_db_version = '1.0';
		$table_name = self::getPaymentsTableName();
		$charset_collate = $wpdb->get_charset_collate();

		dbDelta("CREATE TABLE IF NOT EXISTS $table_name (
				id INT UNSIGNED NOT NULL AUTO_INCREMENT,
				order_id varchar(128) DEFAULT '' NULL,
				amount INT UNSIGNED DEFAULT 0 NULL,
				phone_number varchar(128) DEFAULT '' NULL,
				created datetime DEFAULT CURRENT_TIMESTAMP  NOT NULL,
				payment_id varchar(128) DEFAULT '' NULL,
				failed_reason varchar(128) DEFAULT '' NULL,
				salt varchar(128) DEFAULT '' NULL,
				status varchar(36) DEFAULT 'pending' NULL,
				PRIMARY KEY  (id)
				) $charset_collate;
		");
	
		add_option( self::getPaymentsTableVersionName(), $payments_db_version );
	}	
	

	public static function pluginRemove() {
		global $wpdb;
		$table_name = self::getPaymentsTableName();
		
		$wpdb->query('DROP TABLE ' . $table_name);
		
		delete_option('woocommerce_momopay_settings');		
		delete_option('wc_momopay_adv_data');		
	}

	public static function getPaymentsTableName() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::$plugin_slug . 'payments';
		return $table_name;
	}

	public static function getPaymentsTableVersionName() {
		global $wpdb;
		$version_name = $wpdb->prefix . self::$plugin_slug . 'payments_db_version';
		return $version_name;
	}
}
?>