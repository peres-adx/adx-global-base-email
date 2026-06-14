<?php

namespace App\Controllers;

use App\Domain\Common\Result;
use App\Domain\DTOs\Customers\CustomerInputDTO;
use App\Traits\ResponseHandler;

use Config\Services;

class CustomerController extends BaseController
{

  use ResponseHandler;

	public function listAll()
  {
    return $this->sendResult(Services::customerService()->listAll());
  }

	public function listById($id = null)
  {
    if (!$id) return $this->sendResult(Result::failure("O ID é obrigatório", 202));
    return $this->sendResult(Services::customerService()->listById((string) $id));
  }

  public function listByUser($userId = null)
  {
    if (!$userId) return $this->sendResult(Result::failure("O ID do usuário é obrigatório", 202));
    return $this->sendResult(Services::customerService()->listByUser((string) $userId));
  }

	public function listByCpf($cpf = null)
  {
    if (!$cpf) return $this->sendResult(Result::failure("O CPF é obrigatório", 202));
    return $this->sendResult(Services::customerService()->listByCpf((string) $cpf));
  }

  public function listByEmail($email = null)
  {
    if (!$email) return $this->sendResult(Result::failure("O e-mail é obrigatório", 202));
    return $this->sendResult(Services::customerService()->listByEmail((string) $email));
  }

  public function register()
	{
		return $this->sendResult(Services::customerService()->register(CustomerInputDTO::fromRequest($this->request->getJSON(true))), 201);
	}

	public function update($id = null)
	{
	  if (!$id) return $this->sendResult(Result::failure("O ID é obrigatório"), 202);
    return $this->sendResult(Services::customerService()->update(CustomerInputDTO::fromRequest($this->request->getJSON(true)), (string) $id));
  }

  public function delete($id = null)
	{
		return $this->sendResult(Services::customerService()->delete((string) $id));
	}

}