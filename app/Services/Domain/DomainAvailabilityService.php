<?php

namespace App\Services\Domain;

class DomainAvailabilityService
{
	public function __construct(
		private readonly NamecheapService $namecheap,
	) {}

	/**
	 * Check availability for a brand name across TLDs (primary label + useful variants).
	 *
	 * @param  array<int, string>  $tlds
	 * @return array<int, array{domain: string, available: bool, buy_url: string, source?: string}>
	 */
	public function check(string $brandName, array $tlds = []): array
	{
		$normalized = $this->normalizeLabel($brandName);
		if ($normalized === '') {
			return [];
		}

		$tlds = $this->normalizeTlds($tlds);
		if ($tlds === []) {
			$tlds = $this->defaultTlds();
		}

		$labels = array_values(array_unique(array_filter([
			$normalized,
			$this->hyphenate($normalized),
			'get' . $normalized,
			'try' . $normalized,
			$normalized . 'app',
			$normalized . 'hq',
		])));

		$candidates = [];
		$seen = [];
		foreach ($labels as $label) {
			foreach ($tlds as $tld) {
				$domain = $label . '.' . $tld;
				if (isset($seen[$domain])) {
					continue;
				}
				$seen[$domain] = true;
				if (!\filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
					continue;
				}
				$candidates[] = $domain;
			}
		}

		$availability = $this->resolveAvailability($candidates);
		$results = [];
		foreach ($candidates as $domain) {
			$results[] = [
				'domain' => $domain,
				'available' => (bool) ($availability[$domain]['available'] ?? false),
				'buy_url' => $this->namecheapSearchUrl($domain, $normalized),
				'source' => $availability[$domain]['source'] ?? 'dns',
			];
		}

		return $this->sortByTldPriority($results);
	}

	/**
	 * Faster check: primary label × TLDs only (used for generate ranking).
	 *
	 * @param  array<int, string>  $tlds
	 * @return array<int, array{domain: string, available: bool, buy_url: string, tld: string, source?: string}>
	 */
	public function checkPrimary(string $brandName, array $tlds = []): array
	{
		$normalized = $this->normalizeLabel($brandName);
		if ($normalized === '') {
			return [];
		}

		$tlds = $this->normalizeTlds($tlds);
		if ($tlds === []) {
			$tlds = $this->defaultTlds();
		}

		$candidates = [];
		foreach ($tlds as $tld) {
			$domain = $normalized . '.' . $tld;
			if (!\filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
				continue;
			}
			$candidates[] = $domain;
		}

		$availability = $this->resolveAvailability($candidates);
		$results = [];
		foreach ($candidates as $domain) {
			$tld = strtolower((string) substr(strrchr($domain, '.') ?: '', 1));
			$results[] = [
				'domain' => $domain,
				'available' => (bool) ($availability[$domain]['available'] ?? false),
				'tld' => '.' . $tld,
				'buy_url' => $this->namecheapSearchUrl($domain, $normalized),
				'source' => $availability[$domain]['source'] ?? 'dns',
			];
		}

		return $this->sortByTldPriority($results);
	}

	/**
	 * Batch check many brand names' primary domains (Namecheap when configured).
	 *
	 * @param  array<int, string>  $brandNames
	 * @param  array<int, string>  $tlds
	 * @return array<string, array<int, array{domain: string, available: bool, buy_url: string, tld: string, source?: string}>>
	 */
	public function checkPrimaryMany(array $brandNames, array $tlds = []): array
	{
		$tlds = $this->normalizeTlds($tlds);
		if ($tlds === []) {
			$tlds = $this->defaultTlds();
		}

		$byName = [];
		$allDomains = [];
		foreach ($brandNames as $brandName) {
			$normalized = $this->normalizeLabel((string) $brandName);
			if ($normalized === '') {
				$byName[(string) $brandName] = [];
				continue;
			}
			$domains = [];
			foreach ($tlds as $tld) {
				$domain = $normalized . '.' . $tld;
				if (!\filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
					continue;
				}
				$domains[] = $domain;
				$allDomains[] = $domain;
			}
			$byName[(string) $brandName] = [
				'normalized' => $normalized,
				'domains' => $domains,
			];
		}

		$availability = $this->resolveAvailability($allDomains);
		$out = [];
		foreach ($byName as $name => $meta) {
			if ($meta === []) {
				$out[$name] = [];
				continue;
			}
			$rows = [];
			foreach ($meta['domains'] as $domain) {
				$tld = strtolower((string) substr(strrchr($domain, '.') ?: '', 1));
				$rows[] = [
					'domain' => $domain,
					'available' => (bool) ($availability[$domain]['available'] ?? false),
					'tld' => '.' . $tld,
					'buy_url' => $this->namecheapSearchUrl($domain, $meta['normalized']),
					'source' => $availability[$domain]['source'] ?? 'dns',
				];
			}
			$out[$name] = $this->sortByTldPriority($rows);
		}

		return $out;
	}

	/**
	 * @param  array<int, string>  $requestTlds
	 * @return array<int, string>
	 */
	public function resolveTlds(array $requestTlds = []): array
	{
		$priority = $this->normalizeTlds(config('domains.priority_tlds', ['com', 'net', 'io', 'co', 'ai']));
		$extra = $this->normalizeTlds(config('domains.extra_tlds', []));
		$request = $this->normalizeTlds($requestTlds);

		return array_values(array_unique(array_merge($priority, $request, $extra)));
	}

	/**
	 * @return array<int, string>
	 */
	public function priorityTlds(): array
	{
		$priority = $this->normalizeTlds(config('domains.priority_tlds', ['com', 'net', 'io', 'co', 'ai']));

		return $priority !== [] ? $priority : ['com', 'net', 'io', 'co', 'ai'];
	}

	public function namecheapSearchUrl(string $domainOrName, ?string $fallbackName = null): string
	{
		$template = (string) config(
			'domains.namecheap_search_url',
			'https://www.namecheap.com/domains/registration/results/?domain={domain}'
		);

		$name = $this->normalizeLabel($fallbackName ?: $domainOrName);
		$domain = str_contains($domainOrName, '.')
			? strtolower(preg_replace('/\s+/', '', $domainOrName) ?? $domainOrName)
			: ($name !== '' ? $name . '.com' : '');

		return str_replace(
			['{domain}', '{name}'],
			[rawurlencode($domain), rawurlencode($name)],
			$template
		);
	}

	/**
	 * @param  array<int, array{domain: string, available: bool}>  $domainResults
	 */
	public function availabilityScore(array $domainResults): int
	{
		$score = 0;
		$priority = $this->priorityTlds();
		$weights = [];
		$weight = count($priority) + 2;
		foreach ($priority as $tld) {
			$weights[$tld] = $weight;
			$weight--;
		}

		foreach ($domainResults as $row) {
			if (!($row['available'] ?? false)) {
				continue;
			}
			$domain = (string) ($row['domain'] ?? '');
			$tld = strtolower((string) substr(strrchr($domain, '.') ?: '', 1));
			$score += $weights[$tld] ?? 1;
			if ($tld === 'com') {
				$score += 100;
			}
		}

		return $score;
	}

	public function hasAnyAvailable(array $domainResults): bool
	{
		foreach ($domainResults as $row) {
			if (!empty($row['available'])) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Prefer Namecheap when configured; fall back to DNS for missing domains.
	 *
	 * @param  array<int, string>  $domains
	 * @return array<string, array{available: bool, source: string}>
	 */
	private function resolveAvailability(array $domains): array
	{
		$domains = array_values(array_unique(array_filter($domains)));
		$out = [];

		$namecheapMap = [];
		if ((bool) config('domains.prefer_namecheap', true) && $this->namecheap->isConfigured()) {
			$namecheapMap = $this->namecheap->checkAvailabilityMap($domains);
		}

		foreach ($domains as $domain) {
			if (array_key_exists($domain, $namecheapMap)) {
				$out[$domain] = [
					'available' => (bool) $namecheapMap[$domain],
					'source' => 'namecheap',
				];
				continue;
			}

			$out[$domain] = [
				'available' => $this->isLikelyAvailable($domain),
				'source' => 'dns',
			];
		}

		return $out;
	}

	/**
	 * @param  array<int, string|mixed>  $tlds
	 * @return array<int, string>
	 */
	private function normalizeTlds(array $tlds): array
	{
		$out = [];
		foreach ($tlds as $tld) {
			$t = strtolower(ltrim(trim((string) $tld), '.'));
			if ($t !== '') {
				$out[] = $t;
			}
		}

		return array_values(array_unique($out));
	}

	/**
	 * @return array<int, string>
	 */
	private function defaultTlds(): array
	{
		return $this->resolveTlds([]);
	}

	private function normalizeLabel(string $name): string
	{
		$lower = mb_strtolower($name, 'UTF-8');
		$alnum = preg_replace('/[^a-z0-9]+/u', '', $lower);

		return (string) $alnum;
	}

	private function hyphenate(string $label): string
	{
		$parts = preg_split('/(?=[A-Z])|\s+/u', $label, -1, PREG_SPLIT_NO_EMPTY);
		if (!$parts || count($parts) < 2) {
			return $label;
		}

		return implode('-', array_map('strtolower', $parts));
	}

	private function isLikelyAvailable(string $domain): bool
	{
		$records = @dns_get_record($domain, DNS_A + DNS_AAAA + DNS_CNAME + DNS_NS + DNS_MX);

		return empty($records);
	}

	/**
	 * @param  array<int, array<string, mixed>>  $results
	 * @return array<int, array<string, mixed>>
	 */
	private function sortByTldPriority(array $results): array
	{
		$priority = $this->priorityTlds();
		$order = array_flip($priority);

		usort($results, function (array $a, array $b) use ($order) {
			$aAvail = !empty($a['available']) ? 0 : 1;
			$bAvail = !empty($b['available']) ? 0 : 1;
			if ($aAvail !== $bAvail) {
				return $aAvail <=> $bAvail;
			}

			$aTld = strtolower((string) substr(strrchr((string) ($a['domain'] ?? ''), '.') ?: '', 1));
			$bTld = strtolower((string) substr(strrchr((string) ($b['domain'] ?? ''), '.') ?: '', 1));
			$aRank = $order[$aTld] ?? 999;
			$bRank = $order[$bTld] ?? 999;

			return $aRank <=> $bRank;
		});

		return $results;
	}
}
