<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\{Cpf, Email, Password, Uuid};
use App\Domain\DTOs\Users\UserInputDTO;

class User
{

  public function __construct(
    private string    $name,
    private Email     $email,
    private ?Password $password,
    private ?string   $id             = null,
    private ?Cpf      $cpf            = null,
    private ?string   $rg             = null,
    private ?string   $phone          = null,
    private ?string   $address        = null,
    private ?string   $zipcode        = null,
    private ?string   $number         = null,
    private ?string   $district       = null,
    private ?string   $city           = null,
    private ?string   $fu             = null,
    private int       $token_version  = 1
  ) {}

  public static function create(UserInputDTO $dto, Email $email, ?Password $password, ?Cpf $cpf): self
  {
    return new self(...array_merge((array) $dto, [
      'id'              => Uuid::next(),
      'email'           => $email,
      'password'        => $password,
      'cpf'             => $cpf,
      'token_version'   => 1
    ]));
  }

	public static function restore(array $data): self
	{

		$hash = $data['password'] ?? null;
		unset($data['password']); 

		return new self(...array_merge($data, [
			'email'         => Email::create($data['email'])->data,
			'password'      => !empty($hash) ? Password::fromHash($hash)->data : null,
			'id'            => $data['id'] ?? $data['uuid'] ?? null,
			'cpf'           => isset($data['cpf']) ? Cpf::create($data['cpf'])->data : null,
			'token_version' => (int) ($data['token_version'] ?? 1)
		]));

	}

  public function updateFromDTO(UserInputDTO $dto, Email $email, ?Cpf $cpf): void
  {
    $this->name     = $dto->name;
    $this->email    = $email;
    $this->cpf      = $cpf;
    $this->rg       = $dto->rg;
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

		$data = [
      'id'            => (string) $this->id,
      'name'          => $this->name,
      'email'         => (string) $this->email,
      'cpf'           => (string) $this->cpf,
      'rg'            => $this->rg,
      'phone'         => $this->phone,
      'address'       => $this->address,
      'zipcode'       => $this->zipcode,
      'number'        => $this->number,
      'district'      => $this->district,
      'city'          => $this->city,
      'fu'            => $this->fu,
      'token_version' => $this->token_version
    ];

		if ($this->password) $data['password'] = $this->password->getHash();

    return $data;

  }

	public function getTokenVersion():										int				{ return $this->token_version; }
  public function incrementTokenVersion():							void			{ $this->token_version++; }
  public function setNewPassword(Password $password):		void			{ $this->password = $password; }
  public function getId():															?string		{ return $this->id; }
  public function getName():														?string		{ return $this->name; }
  public function getEmail():														Email			{ return $this->email; }
  public function getPassword():												?Password	{ return $this->password; }
  public function getCpf():															?Cpf			{ return $this->cpf; }
  public function getRg():															?string		{ return $this->rg; }
  public function getPhone():														?string		{ return $this->phone; }
  public function getAddress():													?string		{ return $this->address; }
  public function getZipcode():													?string		{ return $this->zipcode; }
  public function getNumber():													?string		{ return $this->number; }
  public function getDistrict():												?string		{ return $this->district; }
  public function getCity():														?string		{ return $this->city; }
  public function getFu():															?string		{ return $this->fu; }

}