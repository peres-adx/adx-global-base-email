<?php

namespace App\Domain\DTOs\Auth;

readonly class AuthOutputDTO
{

	public function __construct(
		public string $accessToken,
		public array	$user
	) {}

	public function toArray(): array
	{
		return [
			'status'      => $this->status,
			'detail'      => $this->detail,
			'accessToken' => $this->accessToken,
			'user'        => $this->user
		];
	}

}