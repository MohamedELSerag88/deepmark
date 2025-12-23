<?php

namespace App\Services\Domain;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

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
		$sandbox = (bool)($cfg['sandbox'] ?? true);
		$this->apiUser = (string)($cfg['api_user'] ?? '');
		$this->apiKey = (string)($cfg['api_key'] ?? '');
		$this->username = (string)($cfg['username'] ?? '');
		$this->clientIp = (string)($cfg['client_ip'] ?? request()->ip());
		$override = (string)($cfg['base_url'] ?? '');
		$this->baseUrl = $override ?: ($sandbox
			? 'https://api.sandbox.namecheap.com/xml.response'
			: 'https://api.namecheap.com/xml.response');
	}

	public function check(string $domain): array
	{
		$params = [
			'DomainList' => $domain,
		];
		return $this->send('namecheap.domains.check', $params);
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
			'FirstName' => (string)($r['first_name'] ?? ''),
			'LastName' => (string)($r['last_name'] ?? ''),
			'Address1' => (string)($r['address1'] ?? ''),
			'City' => (string)($r['city'] ?? ''),
			'StateProvince' => (string)($r['state_province'] ?? ''),
			'PostalCode' => (string)($r['postal_code'] ?? ''),
			'Country' => (string)($r['country'] ?? ''),
			'Phone' => (string)($r['phone'] ?? ''), // format: +NNN.NNNNNNNNN
			'EmailAddress' => (string)($r['email'] ?? ''),
		];
	}

	private function send(string $command, array $params): array
	{
		$query = array_merge([
			'ApiUser' => $this->apiUser,
			'ApiKey' => $this->apiKey,
			'UserName' => $this->username,
			'ClientIp' => $this->clientIp,
			'Command' => $command,
		], $params);

		$response = Http::timeout(30)->get($this->baseUrl, $query);
		$xml = @simplexml_load_string((string)$response->body());
		if ($xml === false) {
			return ['ok' => false, 'error' => 'Invalid XML from Namecheap', 'raw' => $response->body()];
		}
		$status = (string)($xml['Status'] ?? '');
		if (strtoupper($status) !== 'OK') {
			$errors = [];
			if (isset($xml->Errors)) {
				foreach ($xml->Errors->Error as $err) {
					$errors[] = (string)$err;
				}
			}
			return ['ok' => false, 'error' => implode('; ', $errors), 'xml' => $xml];
		}
		return ['ok' => true, 'xml' => $xml];
	}
}


