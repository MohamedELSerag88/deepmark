<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "fname" => $this->fname ,
            "lname" => $this->lname ,
            "email" => $this->email ,
            "phone" => $this->phone,
            "token" => $this->token,
        ];
    }
}
