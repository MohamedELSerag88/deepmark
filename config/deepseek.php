<?php

return [
	'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'),
	'api_key' => env('DEEPSEEK_API_KEY','sk-2b73db8a66244475bc8ae096ef9209d4'),
	'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
	'timeout' => env('DEEPSEEK_TIMEOUT', 30),
];


