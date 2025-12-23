<?php

namespace App\Services\Domain;

class DomainAvailabilityService
{
	/**
	 * Generate candidate domains for a brand name, across a set of TLDs,
	 * and check DNS records to infer availability.
	 */
	public function check(string $brandName, array $tlds = []): array
	{
		$normalized = $this->normalizeLabel($brandName);
		if ($normalized === '') {
			return [];
		}

		$labels = array_values(array_unique(array_filter([
			$normalized,
			$this->hyphenate($normalized),
			'get' . $normalized,
			'try' . $normalized,
			$normalized . 'app',
			$normalized . 'hq',
		])));

		if (empty($tlds)) {
			$tlds = ['com','net','org','co','io','ai','app'];
		}

		$results = [];
		foreach ($labels as $label) {
			foreach ($tlds as $tld) {
				$domain = $label . '.' . ltrim(strtolower($tld), '.');
				if (!\filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
					continue;
				}
				$available = $this->isLikelyAvailable($domain);
				$results[] = [
					'domain' => $domain,
					'available' => $available,
				];
			}
		}
		return $results;
	}

	private function normalizeLabel(string $name): string
	{
		$lower = mb_strtolower($name, 'UTF-8');
		// Remove non alphanumeric characters
		$alnum = preg_replace('/[^a-z0-9]+/u', '', $lower);
		return (string)$alnum;
	}

	private function hyphenate(string $label): string
	{
		// Split original with spaces/camel-case to create hyphenated variant
		$parts = preg_split('/(?=[A-Z])|\s+/u', $label, -1, PREG_SPLIT_NO_EMPTY);
		if (!$parts || count($parts) < 2) {
			return $label;
		}
		return implode('-', array_map('strtolower', $parts));
	}

	private function isLikelyAvailable(string $domain): bool
	{
		// If any DNS record exists, assume registered
		$records = @dns_get_record($domain, DNS_A + DNS_AAAA + DNS_CNAME + DNS_NS + DNS_MX);
		return empty($records);
	}
}


