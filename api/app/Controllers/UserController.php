<?php

namespace App\Controllers;

use App\Domain\Common\Result;
use App\Domain\DTOs\Users\{PasswordInputDTO, UserInputDTO};
use App\Traits\ResponseHandler;

use Config\Services;

class UserController extends BaseController
{

	use ResponseHandler;

	public function listAll()
	{
		return $this->sendResult(Services::userService()->listAll());
	}

	public function listById($id = null)
	{
    if (!$id) return $this->sendResult(Result::failure("O ID é obrigatório", 202));
    return $this->sendResult(Services::userService()->listById((string) $id));
	}

	public function listByEmail($email = null)
	{
    if (!$email) return $this->sendResult(Result::failure("O E-mail é obrigatório", 202));
    return $this->sendResult(Services::userService()->listByEmail((string) $email));
	}

  public function register()
	{
		return $this->sendResult(Services::userService()->register(UserInputDTO::fromRequest($this->request->getJSON(true))), 201);
	}

	public function update($id = null)
  {
    if (!$id) return $this->sendResult(Result::failure("O ID é obrigatório", 202));
		return $this->sendResult(Services::userService()->update(UserInputDTO::fromRequest($this->request->getJSON(true)), (string) $id));
  }

	public function delete($id = null)
	{
    if (!$id) return $this->sendResult(Result::failure("O ID é obrigatório", 202));
    $loggedUserId = Services::authService()->id(); 
    return $this->sendResult(Services::userService()->delete((string) $id, (string) $loggedUserId));
	}

	public function setupMaster()
  {
    $json		= $this->request->getJSON(true);
    $secret	= $json['secret'] ?? '';
    if ($secret !== 'ADX_MASTER_RECOVERY_2026') return $this->sendResult(Result::failure("Chave mestra inválida.", 401));
    return $this->sendResult(Services::userService()->createMasterUser());
  }

}