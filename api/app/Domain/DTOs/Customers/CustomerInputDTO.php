<?php

namespace App\Domain\DTOs\Customers;

final class CustomerInputDTO
{

  public function __construct(
    public readonly string		$userId,
    public readonly string		$name,
    public readonly string		$email,
    public readonly string		$cpf,
    public readonly ?string		$rg = null,
    public readonly ?int			$age = null,
    public readonly ?string		$phone = null,
    public readonly ?string		$address = null,
    public readonly ?string		$zipcode = null,
    public readonly ?string		$number = null,
    public readonly ?string		$district = null,
    public readonly ?string		$city = null,
    public readonly ?string		$fu = null
  ) {}

  public static function fromRequest(array $data): self
  {
    return new self(
			$data['userId']				?? $data['user_id'] ?? '',
      $data['name']     		?? '',
      $data['email']    		?? '',
      $data['cpf']      		?? '',
      $data['rg']       		?? null,
      (int) ($data['age']		?? 0),
      $data['phone']    		?? null,
      $data['address']  		?? null,
      $data['zipcode']  		?? null,
      $data['number']   		?? null,
      $data['district'] 		?? null,
      $data['city']     		?? null,
      $data['fu']       		?? null
    );
  }

}