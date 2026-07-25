<?php

return [
	/*
	| Domain TLD priority for ranking / availability checks.
	| Names with an available higher-priority TLD rank above others.
	*/
	'priority_tlds' => array_values(array_filter(array_map(
		static fn ($tld) => strtolower(ltrim(trim((string) $tld), '.')),
		explode(',', (string) env('DOMAIN_PRIORITY_TLDS', 'com,net,io,co,ai'))
	))),

	/*
	| Extra TLDs checked beyond the request list / priority list.
	*/
	'extra_tlds' => array_values(array_filter(array_map(
		static fn ($tld) => strtolower(ltrim(trim((string) $tld), '.')),
		explode(',', (string) env('DOMAIN_EXTRA_TLDS', 'org,app,dev,me,store,online'))
	))),

	/*
	| When true, names with zero available domains are excluded from results.
	| If that filters everything out, BrandNameService falls back to unfiltered ranked results.
	*/
	'require_available_domain' => (bool) env('DOMAIN_REQUIRE_AVAILABLE', true),

	/*
	| Prefer Namecheap API for availability when credentials are configured.
	| Domains not returned by Namecheap fall back to DNS heuristics.
	*/
	'prefer_namecheap' => (bool) env('DOMAIN_PREFER_NAMECHEAP', true),

	/*
	| Namecheap domain search URL template. Use {domain} or {name}.
	*/
	'namecheap_search_url' => env(
		'NAMECHEAP_SEARCH_URL',
		'https://www.namecheap.com/domains/registration/results/?domain={domain}'
	),

	'calendly_branding_url' => env(
		'CALENDLY_BRANDING_URL',
		'https://calendly.com/deepmarks-support/30min'
	),
];
