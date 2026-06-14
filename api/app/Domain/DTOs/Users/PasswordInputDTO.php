<?php

namespace App\Domain\DTOs\Users;

class PasswordInputDTO
{

	public function __construct(
		public string $currentPassword,
		public string $newPassword,
		public string	$confirmPassword
	) {}

	public static function fromArray(array $data): self
	{
		return new self(
			$data['currentPassword']	?? '',
			$data['newPassword']			?? '',
			$data['confirmPassword']	?? ''
		);
	}

}