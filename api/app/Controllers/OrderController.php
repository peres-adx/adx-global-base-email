<?php

namespace App\Controllers;

use App\Domain\Common\Result;
use App\Domain\DTOs\Orders\OrderInputDTO;
use App\Traits\ResponseHandler;

use Config\Services;

class OrderController extends BaseController
{

  use ResponseHandler;

	public function listAll()
	{
		return $this->sendResult(Services::orderService()->listAll());
	}

	public function listById($id = null)
	{
    if (!$id) return $this->sendResult(Result::failure("O ID é obrigatório", 202));
    return $this->sendResult(Services::orderService()->listById((string) $id));
	}

  public function listByCustomer($customerId = null)
  {
    if (!$customerId) return $this->sendResult(Result::failure("O ID do cliente é obrigatório", 202));
    return $this->sendResult(Services::orderService()->listByCustomer((string) $customerId));
  }

	public function register()
	{
		return $this->sendResult(Services::orderService()->register(OrderInputDTO::fromRequest($this->request->getJSON(true))), 201);
	}

  public function update($id = null)
  {
    if (!$id) return $this->sendResult(Result::failure("O ID é obrigatório", 202));
    return $this->sendResult(Services::orderService()->update(OrderInputDTO::fromRequest($this->request->getJSON(true)), (string) $id));
  }

	public function delete($id = null)
	{
    if (!$id) return $this->sendResult(Result::failure("O ID é obrigatório", 202));
		return $this->sendResult(Services::orderService()->delete((string) $id));
	}

}