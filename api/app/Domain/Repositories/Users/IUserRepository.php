<?php

namespace App\Domain\Repositories\Users;

use App\Domain\Common\Result; 
use App\Domain\Entities\User;
use App\Domain\ValueObjects\{Cpf, Email, Uuid};

interface IUserRepository
{
	public function listAll():																								Result;
  public function listById(Uuid $id):																				Result;
	public function listByEmail(Email $email):																Result;
	public function listByEmailAuth(Email $email):														Result;
	public function register(User $user):																			Result;
	public function update(User $user):																				Result;
	public function delete(Uuid $id):																					Result;
	public function exists(Email $email, Cpf $cpf, ?Uuid $excludeId = null):	bool;
	public function existsById(Uuid $id):																			bool;
}