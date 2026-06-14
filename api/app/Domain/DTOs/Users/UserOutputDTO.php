<?php

namespace App\Domain\DTOs\Users;

use App\Domain\Entities\User;

final readonly class UserOutputDTO
{

	public function __construct(
		public string 		$id,
		public string 		$name,
		public string 		$email,
		public string			$cpf,
		public ?string		$phone		= null,
		public ?string		$rg				= null,
		public ?string		$address	= null,
		public ?string		$zipcode	= null,
		public ?string		$number 	= null,
		public ?string		$district	= null,
		public ?string		$city			= null,
		public ?string		$fu				= null
	) {}

	public static function fromEntity(User $user): self
	{

		return new self(
			id:       	(string) $user->getId(),
			name:     	$user->getName(),
			email:    	(string) $user->getEmail(),
			cpf:      	(string) $user->getCpf(),
			phone:    	$user->getPhone(),
			rg:       	$user->getRg(),
			address:  	$user->getAddress(),
			zipcode:  	$user->getZipcode(),
			number:   	$user->getNumber(),
			district: 	$user->getDistrict(),
			city:     	$user->getCity(),
			fu:       	$user->getFu()
		);

	}

}