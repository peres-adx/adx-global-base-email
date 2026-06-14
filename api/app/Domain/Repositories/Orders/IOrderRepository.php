<?php

namespace App\Domain\Repositories\Orders;

use App\Domain\Common\Result;
use App\Domain\Entities\Order;
use App\Domain\ValueObjects\Uuid;

interface IOrderRepository
{
  public function listAll():																								Result;
  public function listById(Uuid $id):																				Result;
  public function listByCustomer(Uuid $customerId):													Result;
  public function register(Order $order):																		Result;
  public function update(Order $order):																			Result;
  public function delete(Uuid $id):																					Result;
  public function existsById(Uuid $id):																			bool;
}