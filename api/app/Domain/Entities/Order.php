<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\{Uuid, Cpf};
use App\Domain\DTOs\Orders\OrderInputDTO;

class Order
{

	public function __construct(
		private Uuid			$customerId,
		private string		$description,
		private ?string		$id					= null,
		private ?float		$totalValue = 0.0,
		private ?string		$createdAt  = null
	) {}

	public static function create(OrderInputDTO $dto, Uuid $customerId): self
	{
		return new self(
			customerId:		$customerId,
			description:	$dto->description,
			id:						(string) Uuid::next(),
			totalValue:		$dto->totalValue ?? 0.0
		);
	}

	public static function restore(array $data): self
	{

		$customerRes = Uuid::create($data['customer_id']);
  
		return new self(
			id:           $data['id'] ?? $data['uuid'] ?? null,
			customerId:   $customerRes->data,
			description:  $data['description'],
			totalValue:   (float) ($data['total_value'] ?? 0.0),
			createdAt:    $data['created_at'] ?? null
		);

	}

	public function toArray(): array
	{
		return [
			'id'          	=> (string) $this->id,
			'customer_id' 	=> (string) $this->customerId,
			'description' 	=> $this->description,
			'total_value' 	=> $this->totalValue,
			'created_at'  	=> $this->createdAt ?? date('Y-m-d H:i:s'),
		];
	}

  public function getId():           ?string	{ return $this->id; }
  public function getCustomerId():   ?string	{ return $this->customerId; }
  public function getDescription():  string		{ return $this->description; }
  public function getTotalValue():   float		{ return $this->totalValue; }
  public function getCreatedAt():    ?string	{ return $this->createdAt; }

}