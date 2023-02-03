<?php

/**
 * @param $text
 *
 * @return string|string[]
 */
function base64UrlEncode($text)
{
	return str_replace(
		['+', '/', '='],
		['-', '_', ''],
		base64_encode($text)
	);
}

/**
 * @param $secret
 * @param $jwt
 *
 * @return bool
 */
function zlick_validate_sign($secret, $signed)
{
	$tokenParts        = explode( '.', $signed );
	$header            = base64_decode( $tokenParts[0] );
	$payload           = base64_decode( $tokenParts[1] );
	$signatureProvided = $tokenParts[2];

   // build a signature based on the header and payload using the secret
	$base64UrlHeader    = base64UrlEncode( $header );
	$base64UrlPayload   = base64UrlEncode( $payload );
	$signature          = hash_hmac( 'sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true );
	$base64UrlSignature = base64UrlEncode( $signature );

   // verify it matches the signature provided in the token
	$signatureValid = ( $base64UrlSignature === $signatureProvided );

	if ( $signatureValid ) {
		return true;
	} else {
		return false;
	}
}

/**
 * @param $signed
 * 
 * @return string
 */
function zlick_get_jwt_payload($signed) {
	$tokenParts = explode( '.', $signed );
	$payload = base64_decode( $tokenParts[1] );

	return $payload;
}

function zlick_jwt_sign($secret, $payload) {
	$header = [ "alg" => "HS256" ];
	$base64UrlHeader    = base64UrlEncode( $header );
	$base64UrlPayload   = base64UrlEncode( $payload );
	$signature          = hash_hmac( 'sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true );
	
	$token = join(".", [$base64UrlHeader, $base64UrlPayload, $signature]);

	return $token;
}