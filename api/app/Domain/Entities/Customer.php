<?php

namespace App\Domain\Entities;

use App\Domain\DTOs\Customers\CustomerInputDTO;
use App\Domain\ValueObjects\{Cpf, Email, Uuid};

class Customer
{

  public function __construct(
    private string  	$name,
    private Email   	$email,
    private Cpf     	$cpf,
    private ?string		$userId			= null,
    private ?string 	$id         = null,
    private ?string 	$rg         = null,
    private ?int    	$age        = null,
    private ?string 	$phone      = null,
    private ?string 	$address    = null,
    private ?string 	$zipcode    = null,
    private ?string 	$number     = null,
    private ?string 	$district   = null,
    private ?string 	$city       = null,
    private ?string 	$fu         = null
  ) {}

  public static function create(CustomerInputDTO $dto, Email $email, Cpf $cpf, Uuid $userId): self
  {
    return new self(...array_merge((array) $dto, [
      'id'      => Uuid::next(),
      'userId' 	=> $userId,
      'email'   => $email,
      'cpf'     => $cpf
    ]));
  }

	public static function restore(array $data): self
	{

		$userId = $data['userId'] ?? $data['user_id'] ?? null;

		unset($data['user_id']); 

		return new self(...array_merge($data, [
			'id'     => $data['id'] ?? null,
			'userId' => $userId,
			'email'  => Email::create($data['email'])->data,
			'cpf'    => Cpf::create($data['cpf'])->data,
			'age'    => isset($data['age']) ? (int) $data['age'] : null
		]));
	}

  public function updateFromDTO(CustomerInputDTO $dto, Email $email, Cpf $cpf): void
  {
    $this->name     = $dto->name;
    $this->email    = $email;
    $this->cpf      = $cpf;
    $this->rg       = $dto->rg;
    $this->age      = $dto->age;
    $this->phone    = $dto->phone;
    $this->address  = $dto->address;
    $this->zipcode  = $dto->zipcode;
    $this->number   = $dto->number;
    $this->district = $dto->district;
    $this->city     = $dto->city;
    $this->fu       = $dto->fu;
  }

  public function toArray(): array
  {
    return [
      'id'       => (string) $this->id,
      'user_id'  => (string) $this->userId,
      'name'     => $this->name,
      'email'    => (string) $this->email,
      'cpf'      => (string) $this->cpf,
      'rg'       => $this->rg,
      'age'      => $this->age,
      'phone'    => $this->phone,
      'address'  => $this->address,
      'zipcode'  => $this->zipcode,
      'number'   => $this->number,
      'district' => $this->district,
      'city'     => $this->city,
      'fu'       => $this->fu
    ];
  }

  public function getId():      ?string { return $this->id; }
  public function getUserId():  ?string { return $this->userId; }
  public function getName():    string  { return $this->name; }
  public function getEmail():   Email   { return $this->email; }
  public function getCpf():     Cpf     { return $this->cpf; }

}