<?php  
return [
	'cp_merchant' => env('CP_API_KEY', ''),
	'cp_ipn_secret' => env('CP_IPN_SECRET', ''),
	'cp_public_key' => env('CP_PUBLIC_KEY', ''),
	'cp_private_key' => env('CP_PRIVATE_KEY',''),
	'cp_success' => '',
	'cp_error' => '',
	'cp_ipn' => '',
	'auto_confirm' => 1
];

?> 