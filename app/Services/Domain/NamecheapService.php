<?php

namespace App\Services\Domain;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NamecheapService
{
	private string $baseUrl;
	private string $apiUser;
	private string $apiKey;
	private string $username;
	private string $clientIp;

	public function __construct()
	{
		$cfg = Config::get('namecheap');
		$sandbox = (bool) ($cfg['sandbox'] ?? true);
		$this->apiUser = (string) ($cfg['api_user'] ?? '');
		$this->apiKey = (string) ($cfg['api_key'] ?? '');
		$this->username = (string) ($cfg['username'] ?? '');
		$this->clientIp = (string) ($cfg['client_ip'] ?? request()->ip());
		$override = (string) ($cfg['base_url'] ?? '');
		$this->baseUrl = $override ?: ($sandbox
			? 'https://api.sandbox.namecheap.com/xml.response'
			: 'https://api.namecheap.com/xml.response');
	}

	public function isConfigured(): bool
	{
		return $this->apiUser !== '' && $this->apiKey !== '' && $this->username !== '';
	}

	public function check(string $domain): array
	{
		return $this->send('namecheap.domains.check', [
			'DomainList' => $domain,
		]);
	}

	/**
	 * Batch-check domains via Namecheap.
	 *
	 * @param  array<int, string>  $domains
	 * @return array<string, bool> map of lowercase domain => available
	 */
	public function checkAvailabilityMap(array $domains): array
	{
		$domains = array_values(array_unique(array_filter(array_map(
			static fn ($d) => strtolower(trim((string) $d)),
			$domains
		))));

		if ($domains === [] || !$this->isConfigured()) {
			return [];
		}

		$map = [];
		foreach (array_chunk($domains, 40) as $chunk) {
			$result = $this->send('namecheap.domains.check', [
				'DomainList' => implode(',', $chunk),
			]);
			if (!($result['ok'] ?? false)) {
				Log::warning('Namecheap domain check failed', [
					'error' => $result['error'] ?? 'unknown',
				]);
				continue;
			}

			$xml = $result['xml'] ?? null;
			if (!$xml) {
				continue;
			}

			$nodes = $xml->CommandResponse->DomainCheckResult ?? [];
			foreach ($nodes as $node) {
				$domain = strtolower((string) ($node['Domain'] ?? ''));
				if ($domain === '') {
					continue;
				}
				$available = strtolower((string) ($node['Available'] ?? 'false')) === 'true';
				$map[$domain] = $available;
			}
		}

		return $map;
	}

	public function register(string $domain, array $registrant, int $years = 1, bool $whoisGuard = false): array
	{
		$params = [
			'DomainName' => $domain,
			'Years' => $years,
			'AddFreeWhoisguard' => $whoisGuard ? 'yes' : 'no',
			'WGEnabled' => $whoisGuard ? 'yes' : 'no',
		];

		$contact = $this->mapRegistrant($registrant);
		foreach (['Registrant', 'Tech', 'Admin', 'AuxBilling'] as $role) {
			foreach ($contact as $key => $val) {
				$params[$role . $key] = $val;
			}
		}

		return $this->send('namecheap.domains.create', $params);
	}

	private function mapRegistrant(array $r): array
	{
		return [
			'FirstName' => (string) ($r['first_name'] ?? ''),
			'LastName' => (string) ($r['last_name'] ?? ''),
			'Address1' => (string) ($r['address1'] ?? ''),
			'City' => (string) ($r['city'] ?? ''),
			'StateProvince' => (string) ($r['state_province'] ?? ''),
			'PostalCode' => (string) ($r['postal_code'] ?? ''),
			'Country' => (string) ($r['country'] ?? ''),
			'Phone' => (string) ($r['phone'] ?? ''),
			'EmailAddress' => (string) ($r['email'] ?? ''),
		];
	}

	private function send(string $command, array $params): array
	{
		if (!$this->isConfigured()) {
			return [
				'ok' => false,
				'error' => 'Namecheap API is not configured. Set NAMECHEAP_API_USER, NAMECHEAP_API_KEY, and NAMECHEAP_USERNAME in .env',
			];
		}

		$query = array_merge([
			'ApiUser' => $this->apiUser,
			'ApiKey' => $this->apiKey,
			'UserName' => $this->username,
			'ClientIp' => $this->clientIp,
			'Command' => $command,
		], $params);

		$response = Http::timeout(30)->get($this->baseUrl, $query);
		$xml = @simplexml_load_string((string) $response->body());
		if ($xml === false) {
			return ['ok' => false, 'error' => 'Invalid XML from Namecheap', 'raw' => $response->body()];
		}
		$status = (string) ($xml['Status'] ?? '');
		if (strtoupper($status) !== 'OK') {
			$errors = [];
			if (isset($xml->Errors)) {
				foreach ($xml->Errors->Error as $err) {
					$errors[] = (string) $err;
				}
			}

			return ['ok' => false, 'error' => implode('; ', $errors), 'xml' => $xml];
		}

		return ['ok' => true, 'xml' => $xml];
	}
}
