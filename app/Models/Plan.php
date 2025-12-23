<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
	use HasFactory;

	protected $fillable = [
		'name',
		'description',
		'price_cents',
		'currency',
		'interval', // month, year
		'stripe_price_id',
	];

	public function features()
	{
		return $this->hasMany(PlanFeature::class);
	}
}


