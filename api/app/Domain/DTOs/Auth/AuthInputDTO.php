<?php

namespace App\Domain\DTOs\Auth;

readonly class AuthInputDTO
{

	public function __construct(
		public string $email,
		public string $password
	) {}

	public static function fromArray(array $data): self
	{
		return new self(
			email:    $data['email']		?? '',
			password: $data['password']	?? ''
		);
	}

}