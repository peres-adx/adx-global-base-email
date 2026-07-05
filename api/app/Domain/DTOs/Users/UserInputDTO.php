<?php

namespace App\Domain\DTOs\Users;

final class UserInputDTO
{

	public function __construct(
		public readonly string		$name,
		public readonly string		$email,
		public readonly string		$password,
		public readonly ?string		$cpf,
		public readonly ?string 	$rg,
		public readonly ?string 	$phone,
		public readonly ?string 	$address,
		public readonly ?string 	$zipcode,
		public readonly ?string 	$number,
		public readonly ?string 	$district,
		public readonly ?string 	$city,
		public readonly ?string 	$fu
	) {}

	public static function fromRequest(array $data): self
	{
		return new self(
			$data['name']				?? '',
			$data['email']			?? '',
			$data['password']		?? '',
			$data['cpf']				?? null,
			$data['rg']					?? null,
			$data['phone']			?? null,
			$data['address']		?? null,
			$data['zipcode']		?? null,
			$data['number']			?? null,
			$data['district']		?? null,
			$data['city']				?? null,
			$data['fu']					?? null
		);
	}

}