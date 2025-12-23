<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomainReservation extends Model
{
	use HasFactory;

	protected $fillable = [
		'user_id',
		'domain',
		'years',
		'registrant',
		'provider',
		'provider_order_id',
		'status',
		'response',
		'error',
	];

	protected function casts(): array
	{
	 return [
		 'registrant' => 'array',
		 'response' => 'array',
	 ];
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}


