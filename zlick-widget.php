<?php

/**
 * Zlick Payments Plugin
 *
 * @category  Zlick
 * @package   Zlick
 * @author    Arsalan Ahmad <arsalan@zlick.it>
 * @copyright Copyright (c) 2018 Zlick ltd (https://www.zlick.it)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      https://www.zlick.it
 */

include_once 'includes/post_type_article.php';
include_once 'includes/database_handler.php';
include_once 'includes/validate_jwt.php';

function sanitizePrice($price) {
	$iprice = (float) $price;
	$iprice = round($iprice * 100);
	return $iprice;
}

function zlick_widget_placeholder() {
	return '';
}

/**
 * Returns the smart prc widget.
 *
 * @param string $post_id The post id.
 *
 * @return string
 */
function zlick_widget_link_shortcode( $data ) {
	if (!is_single()) {
		return '';
	}
	if ( get_option( 'zp_active', '0' )	) {
		$post_id = get_identifier($data);
		if (!empty($post_id)) {
			$zp_custom_fields = get_post_custom( $post_id );
			$article_price = '';
			if (isset($zp_custom_fields['zp_is_paid']) && $zp_custom_fields['zp_is_paid'][0] =='paid'){
				$specific_article_price = $zp_custom_fields['zp_article_price'][0];
				$article_price = $specific_article_price ? $specific_article_price : get_option( 'zp_default_price', '');
			} elseif (get_option( 'zp_set_default_price', '0') == '1') {
				$article_price = get_option( 'zp_default_price', '' );
			}

			$user_id = (isset($_COOKIE['zlick-user_id'])) ? $_COOKIE['zlick-user_id'] : '';
			$subscription_id = get_option( 'zp_subscription_id', '');
			if (zp_cookie_is_valid_subscription($subscription_id) OR zp_cookie_is_purchased_article(trim($post_id))) {
				return '';
			} else {
				$widget_text = get_option( 'zp_widget_text', '' );
				ob_start();
				$client_token = get_option( 'zp_client_token', '' );
				$zp_enable_subscription = get_option( 'zp_enable_subscription', '' );
				$zp_subscription_id = get_option( 'zp_subscription_id', '' );
				$zp_article_price = sanitizePrice($article_price);
				$zp_widget_text = str_replace('{price}',$article_price,$widget_text);
				$article_id = $post_id;
				$zp_environment = get_option('zp_environment', 'sandbox');

				include 'templates/zlick-widget.php';
				$widget = ob_get_contents();
				ob_end_clean();

				return $widget;
			}
		}
	}

	return '';
}

/**
 * @param $data
 *
 * @return mixed
 */
function get_identifier($data) {
	if(isset($data['post_id'])) {
		return $data['post_id'];
	}

	return null;
}

/**
 * Register short codes.
 */
add_shortcode( 'zlick_payment_widget', __NAMESPACE__ . '\\zlick_widget_link_shortcode' );

add_shortcode('zp_placeholder', __NAMESPACE__ . '\\zlick_widget_placeholder');

add_action('wp_ajax_zp_register_article','zp_register_article');
add_action('wp_ajax_nopriv_zp_register_article','zp_register_article');

add_action('wp_ajax_zp_authenticate_article','zp_authenticate_article');
add_action('wp_ajax_nopriv_zp_authenticate_article','zp_authenticate_article');

function cookie_register_article($article_id) {
	
	$zp_article_cookie_content = $_COOKIE["zp_articles"] || "";

	$authed_articles = explode(",", $zp_article_cookie_content);
	$authed_articles[] = $article_id;
	$authed_articles = array_unique($authed_articles);

	$payload = join(",", $authed_articles);

	$secret = get_option( 'zp_client_secret', '' );

	setcookie("zp_articles", zlick_jwt_sign($secret, $payload), time() + 60 *  60 * 24 * 365 * 10, "/");
}

function cookie_register_subscription($subscription_id) {
	$secret = get_option( 'zp_client_secret', '' );

	setcookie("zp_subscription", zlick_jwt_sign($secret, $subscription_id),  time() + 50 * 365 * 24 * 60 * 60, "/");
}

/**
 * @return false|int|null
 */
function zp_authenticate_article(){
	include_once 'includes/validate_jwt.php';
	$secret = get_option( 'zp_client_secret', '' );
	if (!zlick_validate_sign( $secret, $_POST['zp_signed'])) {
		exit("\nThe signature is NOT valid.");
	}

	$article_id = $_POST['zp_article_id'];

	$signedPayload = json_decode(zlick_get_jwt_payload($_POST['zp_signed']), true);

	if (isset($signedPayload["transaction"]["hasAccess"]) && $signedPayload["transaction"]["hasAccess"] === true) {
		$article_id = $signedPayload["transaction"]["productId"];
		error_log("\nRegistering article".$article_id);
		cookie_register_article($article_id);
	} elseif (isset($signedPayload["subscription"]["hasAccess"]) && $signedPayload["subscription"]["hasAccess"] === true) {
		$subscription_id = $signedPayload["subscription"]["subscriptionId"];
		error_log("\nRegistering subscription".$subscription_id);
		cookie_register_subscription($subscription_id);
	} else {
		exit("noop");
	}
	exit("reload");
	return 'Done. Reload the page now';
}

/**
 * @return false|int|null
 */
function zp_register_article(){
	if ( !wp_verify_nonce( $_POST['zp_nonce'], "zp_ajax_nonce")) {
		exit("No naughty business please");
	}
	include_once 'includes/validate_jwt.php';
	if (!zlick_validate_sign( get_option( 'zp_client_secret', '' ), trim($_POST['zp_signed']))) {
		exit("The signature is NOT valid.");
	}

	$db_handler = new Zlick_Payments_Db_Handler();
	if (isset($_POST['zp_data']['type']) && $_POST['zp_data']['type'] == 'purchase') {
        $response = $db_handler->register_article(trim($_POST['zp_data']['userId']), trim($_POST['zp_article_id']));
    } elseif (isset($_POST['zp_data']['type']) && $_POST['zp_data']['type'] == 'subscribe') {
        $response = $db_handler->register_subscription($_POST['zp_data']);
    }

	if (!is_null($response)) {
		return'Article is purchased!';
	} else {
		return "Something went wrong!";
	}
}

function zp_cookie_is_valid_subscription($subscription_id) {
	$cookie_val = isset($_COOKIE["zp_subscription"]) ? $_COOKIE["zp_subscription"] : "";
	$secret = get_option( 'zp_client_secret', '' );

	if ($cookie_val === "") {
		return false;
	}

	$payload = zlick_get_jwt_payload($cookie_val);

	if ($payload === $subscription_id) {
		return true;
	}
	return false;
}

function zp_cookie_is_purchased_article($article_id) {
	$cookie_val = isset($_COOKIE["zp_articles"]) ? $_COOKIE["zp_articles"] : "";
	$secret = get_option( 'zp_client_secret', '' );

	if ($cookie_val === "") {
		return false;
	}

	$payload = zlick_get_jwt_payload($cookie_val);

	$purchased_articles = explode(",", $payload);

	return in_array($article_id, $purchased_articles);
}

function findNumberP($content, $para_count) {
  $para_i = 0;
  $char_count = 0;

  do {
    $para_i++;
    $ti = strpos($content, '</p>', $char_count + 1);
    if ($ti === FALSE)  {
      break;
    }
    $char_count = $ti;
  } while($para_i < $para_count);

  return $char_count;
}

/**
 * @param $content
 *
 * @return false|string
 */
function zp_limit_content($content) {
	// die($content);

	$content = wpautop( $content );
	remove_filter( 'the_content', 'wpautop' );

	$post_id = get_the_ID();
	$zp_custom_fields = get_post_custom( $post_id );
	if (!isset($zp_custom_fields['zp_is_paid']) || $zp_custom_fields['zp_is_paid'][0] != 'paid') {
		return $content;
	}

	if (get_post_type() == zlick_payments\zp_get_post_type()) {
		$user_id = (isset($_COOKIE['zlick-user_id'])) ? $_COOKIE['zlick-user_id'] : '';
		$subscription_id = get_option( 'zp_subscription_id', '');
		$needle = <<<TMP
<!-- wp:shortcode -->
[zp_placeholder]
<!-- /wp:shortcode -->
TMP;
		if(!zp_cookie_is_valid_subscription($subscription_id) and !zp_cookie_is_purchased_article(get_the_ID())) {
			$content_length = strpos($content, $needle);
			if ($content_length === false) {
				$para_count = get_option( 'zp_previewable_para_length', ZLICK_PREVIEWABLE_CONTENT_PARA_DEFAULT);

				$content_length = findNumberP($content, $para_count);
			}
			$content = substr( $content, 0, $content_length );
			$content .= do_shortcode( "[zlick_payment_widget post_id=" . get_the_ID() . "]" );
		}
	}

	return $content;
}
add_filter( "the_content", "zp_limit_content", -1);