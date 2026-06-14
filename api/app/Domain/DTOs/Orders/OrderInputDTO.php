<?php

namespace App\Domain\DTOs\Orders;

final class OrderInputDTO
{

  public function __construct(
    public readonly string	$customerId,
    public readonly string	$description,
    public readonly float		$totalValue = 0.0
  ) {}

  public static function fromRequest(array $data): self
  {
    return new self(
			$data['customerId']  ?? $data['customer_id']  ?? '',
			$data['description'] ?? $data['desc']         ?? '',
			$data['totalValue']  ?? $data['total_value']  ?? 0.0
    );
  }

}