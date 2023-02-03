<?php
// This is the main plugin definition
/**
 * Zlick Payments Plugin
 *
 * @category  Zlick
 * @package   Zlick
 * @author    Arsalan Ahmad <arsalan@zlick.it>
 * @copyright Copyright (c) 2018 Zlick ltd (https://www.zlick.it)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      https://zlick.it
 *
 * Plugin Name: Zlick Payments
 * Plugin URI:  https://zlick.it
 * Description: Zlick Payments Plugin for wordpress allows you to integrate Zlick Payment System in your Wordpress shop.
 * Version:     1.3.5
 * Author:      Arsalan Ahmad
 * Author URI:  arsalan@zlick.it
 * Text Domain: zlick-payments
 */

namespace zlick_payments;

// define necessary variable for the site.
define( 'ZLICK_POST_TYPE', "articles" );
define( 'ZLICK_PREVIEWABLE_CONTENT_PARA_DEFAULT', 2 );
define( 'ZLICK_URL', plugins_url( '', __FILE__ ) );
define( 'ZLICK_LOCAL', dirname( __FILE__ ) );
define( 'ZLICK_AJAX_URL', admin_url( 'admin-ajax.php' ) );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once 'includes/post_type_article.php';
include_once 'includes/database_handler.php';

/**
 * Activates plugins.
 */
function zlick_payments_activate_plugin() {
	$db_hanlder = new \Zlick_Payments_Db_Handler();

	$db_hanlder->createDbTables();
}

/**
 * Deactivates Plugin.
 */
function zlick_payments_deactivate_plugin() {
	$db_hanlder = new \Zlick_Payments_Db_Handler();

	$db_hanlder->dropTable();
}

// run the install scripts upon plugin activation
register_activation_hook( __FILE__, __NAMESPACE__ . '\\zlick_payments_activate_plugin' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\zlick_payments_deactivate_plugin' );

/**
 * Adds data on admin menu page.
 *
 * @return void
 */
function zlick_payments_config() {
	add_menu_page(
		__( 'Zlick Payments Configuration', 'zlick-payments' ),
		__( 'Zlick Payments', 'zlick-payments' ),
		'manage_options',
		'zlick-payments-plugin',
		__NAMESPACE__ . '\\zlick_payments_init',
		'',
		100
	);
}

if ( is_admin() ) {
	add_action( 'admin_menu', __NAMESPACE__ . '\\zlick_payments_config' );
}

/**
 * Processes and validates form data.
 *
 * @return void
 */
function zlick_payments_init() {
	if ( ! current_user_can( 'manage_options' ) ) {
		die( esc_attr( __( 'You don\'t have the required permissions to edit this Plugin' ) ) );
	}

	wp_register_style( 'zlick-payments', plugins_url( 'css/configuration.css', __FILE__ ), $deps = array(), $ver = false, $media = 'all' );
	wp_enqueue_style( 'zlick-payments' );

	$data = array();
	$response['is_submited'] = false;
	$default_lang = zp_get_default_lang_code();

	load_plugin_textdomain( 'zlick-payments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	$lang_code = $default_lang;
	if ( isset( $_GET['lang'] ) ) {
		$lang_code = sanitize_key( $_GET['lang'] );
	}

	$lang_code = explode( '_', $lang_code )[0];

	zp_load_textdomain( 'zlick-payments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/', $lang_code );

	if ( isset( $_POST['zp_client_token'] ) && isset( $_POST['zp_client_secret'] ) ) {
		if ( ! check_admin_referer( 'zlick-payments-nonce', 'zp_nonce_field' ) ) {
			die( esc_attr( __( 'Validation Error!' ) ) );
		}

		$sanitized_data = zp_sanitize_configuration_data( $_POST );
		$response = zp_authenticate( $sanitized_data['client_token'], $sanitized_data['client_secret'] );
		$response['is_submited'] = true;
		if ( 'success' === $response['status'] ) {
			zp_update_options_data( $sanitized_data );
		}
	}

	$data = zp_get_options_data();
	$post_types = get_post_types();
	$data['post_types'] = $post_types;

	include 'templates/configuration.php';
}


/**
 * Sanitizes the configurations data.
 *
 * @param array $post_data Form post data.
 *
 * @return array
 */
function zp_sanitize_configuration_data( $post_data ) {
	$configurations = array();

	$configurations['active'] = isset( $post_data['zp_active'] ) ?
		intval( $post_data['zp_active'] ) : 0;

	// don't know why ']' is added instead
	$configurations['zp_environment'] = isset( $post_data['zp_environment'] ) ?
		sanitize_text_field( wp_unslash( $post_data['zp_environment'] ) ) : ']';

	$configurations['client_token'] = isset( $post_data['zp_client_token'] ) ?
		sanitize_text_field( wp_unslash( $post_data['zp_client_token'] ) ) : ']';

	$configurations['client_secret'] = isset( $post_data['zp_client_secret'] ) ?
		sanitize_text_field( wp_unslash( $post_data['zp_client_secret'] ) ) : ']';

	$configurations['client_name'] = isset( $post_data['zp_client_name'] ) ?
		sanitize_text_field( wp_unslash( $post_data['zp_client_name'] ) ) : ']';

	$configurations['post_type'] = isset( $post_data['zp_post_type'] ) ?
		sanitize_text_field( wp_unslash( $post_data['zp_post_type'] ) ) : ']';

	$configurations['previewable_para_length'] = isset( $post_data['zp_previewable_para_length'] ) ?
		intval( $post_data['zp_previewable_para_length'] ) : ZLICK_PREVIEWABLE_CONTENT_PARA_DEFAULT;

    $configurations['set_default_price'] = isset( $post_data['zp_set_default_price'] ) ?
        intval( $post_data['zp_set_default_price'] ) : 1;
    $configurations['default_price'] = isset( $post_data['zp_default_price'] ) ?
        sanitize_text_field( wp_unslash( $post_data['zp_default_price'] ) ) : ']';

    $configurations['enable_subscription'] = isset( $post_data['zp_enable_subscription'] ) ?
        intval( $post_data['zp_enable_subscription'] ) : 1;
    $configurations['subscription_id'] = isset( $post_data['zp_subscription_id'] ) ?
        sanitize_text_field( wp_unslash( $post_data['zp_subscription_id'] ) ) : ']';

	$configurations['widget_text'] = isset( $post_data['zp_widget_text'] ) ?
		sanitize_text_field( wp_unslash( $post_data['zp_widget_text'] ) ) : ']';

	return $configurations;
}

/**
 * Update the plugins options data.
 *
 * @param array $configurations Sanitized configuration data.
 * @param string $lang_code The selected language iso code.
 *
 * @return void
 */
function zp_update_options_data( $configurations ) {
	update_option( 'zp_active', $configurations['active'] );
	update_option( 'zp_environment', $configurations['zp_environment']);
	update_option( 'zp_client_token', $configurations['client_token'] );
	update_option( 'zp_client_secret', $configurations['client_secret'] );
	update_option( 'zp_client_name', $configurations['client_name'] );
	update_option( 'zp_post_type', $configurations['post_type'] );
	update_option( 'zp_previewable_para_length', $configurations['previewable_para_length'] );
	update_option( 'zp_widget_text', $configurations['widget_text'] );
	update_option( 'zp_set_default_price', $configurations['set_default_price'] );
	update_option( 'zp_default_price', $configurations['default_price'] );
	update_option( 'zp_enable_subscription', $configurations['enable_subscription'] );
	update_option( 'zp_subscription_id', $configurations['subscription_id'] );
}

/**
 * Gets default lang code.
 *
 * @return string
 */
function zp_get_default_lang_code() {
	$wpml_options = zp_get_wpml_settings();

	$default_lang = get_locale();
	if ( $wpml_options ) {
		$default_lang = $wpml_options['default_language'];
		if ( empty( $default_lang ) ) {
			$default_lang = get_locale();
		}
	}
	$default_lang = explode( '_', $default_lang )[0];

	return $default_lang;
}

/**
 * Verifies Zlick account.
 *
 * @param int $client_token The shop id.
 * @param string $shop_secret The shop password.
 *
 * @return array
 */
function zp_authenticate( $client_token, $shop_secret ) {
	return array(
		'status'  => 'success',
		'message' => __( 'Settings Updated', 'zlick-payments' ),
	);

	$query_string  = "auth={$client_token}|{$shop_secret}&version=cust-1.0.0&type=request&charset=iso";
	$url           = URL_GET_SETTINGS . '?' . $query_string;
	$response      = wp_remote_get( $url );
	$server_output = wp_remote_retrieve_body( $response );
	if ( 'Access denied' === $server_output ) {
		$response = array(
			'status'  => 'error',
			'message' => __( 'Shop ID & Password Required', 'zlick-payments' ),
		);
	} else {
		$response = array(
			'status'  => 'success',
			'message' => __( 'Settings Updated', 'zlick-payments' ),
		);
	}

	return $response;
}

/**
 * Gets the configurations.
 *
 * @return array
 */
function zp_get_options_data() {
	return array(
		'zp_active'        => get_option( 'zp_active', '0' ),
		'zp_client_token'  => get_option( 'zp_client_token', '' ),
		'zp_environment'   => get_option( 'zp_environment', ''),
		'zp_client_secret' => get_option( 'zp_client_secret', '' ),
		'zp_client_name'   => get_option( 'zp_client_name', '' ),
		'zp_post_type'     => get_option( 'zp_post_type', '' ),
		'zp_previewable_para_length'     => get_option( 'zp_previewable_para_length', ZLICK_PREVIEWABLE_CONTENT_PARA_DEFAULT ),
		'zp_widget_text'   => get_option( 'zp_widget_text', '' ),
        'zp_set_default_price' => get_option( 'zp_set_default_price', '1' ),
        'zp_default_price'  => get_option( 'zp_default_price', '' ),
        'zp_enable_subscription'        => get_option( 'zp_enable_subscription', '1' ),
        'zp_subscription_id'  => get_option( 'zp_subscription_id', '' ),
	);
}

/**
 * Gets the configurations.
 *
 * @return array
 */
function zp_get_post_type() {
	return get_option( 'zp_post_type', '' );
}

/**
 * Gets wpml settings.
 *
 * @return mixed|null|void
 */
function zp_get_wpml_settings() {
	$active_plugins = (array) get_option( 'active_plugins', array() );

	if ( ! empty( $active_plugins ) && in_array( 'sitepress-multilingual-cms/sitepress.php', $active_plugins, true ) ) {
		return get_option( 'icl_sitepress_settings' );
	}

	return null;
}

/**
 * Loads text domain.
 *
 * @param string $domain Text domain.
 * @param bool $deprecated Deprication.
 * @param bool $plugin_rel_path The plugin url path.
 * @param string $locale The locale id.
 *
 * @return bool
 */
function zp_load_textdomain( $domain, $deprecated = false, $plugin_rel_path = false, $locale ) {
	$mofile = $domain . '-' . $locale . '.mo';

	/**
	 * Try to load from the languages directory first.
	 */
	if ( load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mofile ) ) {
		return true;
	}

	if ( false !== $plugin_rel_path ) {
		$path = WP_PLUGIN_DIR . '/' . trim( $plugin_rel_path, '/' );
	} elseif ( false !== $deprecated ) {
		_deprecated_argument( __FUNCTION__, '2.7.0' );
		$path = ABSPATH . trim( $deprecated, '/' );
	} else {
		$path = WP_PLUGIN_DIR;
	}

	return load_textdomain( $domain, $path . '/' . $mofile );
}

/**
 * Do curl request to Zlick System.
 *
 * @param string $url The api endpoint.
 * @param string $method The api method.
 * @param string $post_fields Post fields data.
 * @param array $headers The api headers.
 *
 * @return string
 */
function zp_do_curl( $url, $method = 'GET', $post_fields = '', $headers = array() ) {
	$arguments = array(
		'method'    => $method,
		'sslverify' => true,
		'timeout'   => 15,
		'headers'   => $headers,
		'body'      => wp_json_encode( $post_fields ),
	);

	$response = wp_remote_request( $url, $arguments );
	$body     = wp_remote_retrieve_body( $response );

	return $body;
}

/**
 * Register ShortCodes.
 */
require_once 'zlick-widget.php';
