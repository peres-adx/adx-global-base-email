<?php

namespace App\Infrastructure\Persistence\MySQL;

use App\Domain\Common\Result;
use App\Domain\Entities\User;
use App\Domain\Repositories\Users\IUserRepository;
use App\Domain\ValueObjects\{Cpf, Email, Uuid};

use CodeIgniter\Database\BaseConnection;

class UserRepository implements IUserRepository
{

	private string $table				= 'users';
	private string $baseSelect	= 'HEX(id) as id, name, email, cpf, rg, phone, address, zipcode, number, district, city, fu';

	public function __construct(private readonly BaseConnection $db) {}

	public function listAll(): Result
  {

		$rows = $this->db->table($this->table)
											->select($this->baseSelect)
											->get()
											->getResultArray();

    return !empty($rows)
      ? Result::success(array_map(fn($row) => User::restore($row)->toArray(), $rows), "Usuários listados com sucesso.")
      : Result::success([], "Nenhum usuário encontrado.");

  }

	public function listById(Uuid $id): Result
  {

		$row = $this->db->table($this->table)
										->select($this->baseSelect)
										->where('id', "UNHEX('{$id}')", false)
										->get()
										->getRowArray();

    return $row
			? Result::success(User::restore($row)->toArray(), "Usuário localizado.")
			: Result::failure("Usuário não encontrado.");

  }

	public function listByEmail(Email $email): Result
	{

		$row = $this->db->table($this->table)
										->select($this->baseSelect)
										->where('email', (string) $email)
										->get()
										->getRowArray();

		return $row
			? Result::success(User::restore($row)->toArray(), "Usuário localizado.")
			: Result::failure("Usuário não encontrado.");

	}

	public function listByEmailAuth(Email $email): Result
	{

		$row = $this->db->table($this->table)
										->select($this->baseSelect . ', password')
										->where('email', (string) $email)
										->get()
										->getRowArray();

		return $row
			? Result::success($row, "Usuário localizado.")
			: Result::failure("Usuário não encontrado.");

	}

	public function register(User $user): Result
	{

		$data	 = $user->toArray();
		$id		 = array_shift($data);

		$saved = $this->db->table($this->table)
											->set('id', "UNHEX('{$id}')", false)
											->set($data)
											->insert();

		return $saved
			? Result::success($user->toArray(), "Usuário cadastrado com sucesso.")
			: Result::failure("Erro ao cadastrar o usuário.");

	}

	public function update(User $user): Result
	{

		$data = $user->toArray();
		$id   = array_shift($data);

		$updated = $this->db->table($this->table)
												->set($data)
                        ->where('id', "UNHEX('{$id}')", false)
												->update();

		return $updated
			? Result::success($user->toArray(), "Usuário atualizado com sucesso.")
			: Result::failure("Erro ao atualizar o usuário.");

	}

	public function delete(Uuid $id): Result
	{

  	$this->db->table($this->table)
							->where('id', "UNHEX('{$id}')", false)
							->delete();

		return ($this->db->affectedRows() > 0) 
      ? Result::success(null, "Usuário excluído com sucesso.") 
			: Result::failure("Erro ao excluir o usuário.");

	}

	public function exists(Email $email, ?Cpf $cpf, ?Uuid $excludeId = null): bool
	{

		$builder = $this->db->table($this->table);

		$excludeId && $builder->where('id !=', $this->formatUuidFilter($excludeId), false);

		$identifiers = array_filter([
			'email' => (string) $email,
			'cpf'   => $cpf ? (string) $cpf : null
		]);

		return $builder->groupStart()
						   ->orWhere($identifiers)
					   ->groupEnd()
					   ->countAllResults() > 0;

	}

	public function existsById(Uuid $id): bool 
  {

    return $this->db->table($this->table)
										->where('id', "UNHEX('{$id}')", false)
										->countAllResults() > 0;

  }

}