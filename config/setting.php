<?php
	return [
		'user' => [],
		'guest' => false,
		'oauth' => [
			'client_id' => env('OAUTH_CLIENT_ID'),
			'client_secret' => env('OAUTH_CLIENT_SECRET'),
		],
		'frontend' => [
			'url' => env('FRONT_URL'),
		],
		'shipping' => [
			'origin_id' => env('SHIPPING_ORIGIN_ID'),
		]
	];
