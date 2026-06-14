<?php

namespace App\Domain\Repositories\Customers;

use App\Domain\Common\Result;
use App\Domain\Entities\Customer;
use App\Domain\ValueObjects\{Cpf, Email, Uuid};

interface ICustomerRepository
{
  public function listAll():																								Result;
  public function listById(Uuid $id):																				Result;
  public function listByUser(Uuid $userId):																	Result;
  public function listByCpf(Cpf $cpf):																			Result;
  public function listByEmail(Email $email):																Result;
  public function register(Customer $customer):                  						Result;
  public function update(Customer $customer):                    						Result;
  public function delete(Uuid $id):                              						Result;
  public function exists(Email $email, Cpf $cpf):                						bool;
  public function existsForOther(Email $email, Cpf $cpf, Uuid $customerId): bool;
}