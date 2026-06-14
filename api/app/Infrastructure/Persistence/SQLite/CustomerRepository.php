<?php

namespace App\Infrastructure\Persistence\SQLite;

use App\Domain\Common\Result;
use App\Domain\Entities\Customer;
use App\Domain\Repositories\Customers\ICustomerRepository;
use App\Domain\ValueObjects\{Cpf, Email, Uuid};
use CodeIgniter\Database\BaseConnection;

class CustomerRepository implements ICustomerRepository
{

  private string $table      = 'customers';
  private string $baseSelect = 'upper(hex(id)) as id, upper(hex(user_id)) as user_id, name, email, cpf, rg, age, phone, address, zipcode, number, district, city, fu';

  public function __construct(private readonly BaseConnection $db) {}

	public function listAll(): Result
	{

		$rows = $this->db->table($this->table)
											->select($this->baseSelect)
											->get()
											->getResultArray();

		return !empty($rows)
			? Result::success(array_map(fn($row) => Customer::restore($row)->toArray(), $rows), "Clientes listados com sucesso.")
			: Result::success([], "Nenhum cliente encontrado.");

	}

  public function listById(Uuid $id): Result
  {

    $row = $this->db->table($this->table)
										->select($this->baseSelect)
										->where('id', "x'{$id}'", false)
										->get()
										->getRowArray();

		return $row 
			? Result::success(Customer::restore($row)->toArray(), "Cliente localizado.")
			: Result::failure("Cliente não encontrado.");

  }

  public function listByUser(Uuid $userId): Result
  {

		$rows = $this->db->table($this->table)
											->select($this->baseSelect)
											->where('user_id', "x'{$userId}'", false)
											->get()
											->getResultArray();

		return !empty($rows)
			? Result::success(array_map(fn($row) => Customer::restore($row)->toArray(), $rows), "Clientes do usuário listados com sucesso.")
			: Result::success([], "Nenhum cliente encontrado.");

  }

	public function listByEmail(Email $email): Result
	{

		$row = $this->db->table($this->table)
										->select($this->baseSelect)
										->where('email', (string) $email)
										->get()
										->getRowArray();

		return $row 
			? Result::success(Customer::restore($row)->toArray(), "Cliente localizado por e-mail.")
			: Result::failure("Cliente não encontrado.");

	}
	
  public function listByCpf(Cpf $cpf): Result
  {

    $row = $this->db->table($this->table)
										->select($this->baseSelect)
										->where('cpf', (string) $cpf)
										->get()
										->getRowArray();

		return $row
			? Result::success(Customer::restore($row)->toArray(), "Cliente localizado por CPF.")
			: Result::failure("Cliente não encontrado.");

  }

  public function register(Customer $customer): Result
  {

    $data   = $customer->toArray();
    $id     = array_shift($data);
    $userId = array_shift($data);

    $saved = $this->db->table($this->table)
                      ->set('id', "x'{$id}'", false)
                      ->set('user_id', "x'{$userId}'", false)
                      ->set($data)
                      ->insert();

		return $saved 
			? Result::success($customer->toArray(), "Cliente cadastrado.") 
			: Result::failure("Erro ao cadastrar o cliente.");

  }

  public function update(Customer $customer): Result
  {

    $data = $customer->toArray();
    $id   = array_shift($data);

    array_shift($data);

    $updated = $this->db->table($this->table)
												->set($data)
												->where('id', "x'{$id}'", false)
												->update();

		return $updated 
			? Result::success($customer->toArray(), "Dados do cliente atualizado com sucesso.") 
			: Result::failure("Erro ao atualizar os dados do cliente.");

  }

  public function delete(Uuid $id): Result
  {

    $this->db->table($this->table)
							->where('id', "x'{$id}'", false)
							->delete();

		return ($this->db->affectedRows() > 0) 
			? Result::success(null, "Cliente excluído com sucesso.") 
			: Result::failure("Cliente não encontrado para exclusão.");

  }

  public function exists(Email $email, Cpf $cpf): bool
  {

		return $this->db->table($this->table)
										->groupStart()
                    	->where('email', (string) $email)
                    	->orWhere('cpf', (string) $cpf)
										->groupEnd()
										->countAllResults() > 0;

  }

  public function existsForOther(Email $email, Cpf $cpf, Uuid $customerId): bool
  {

    return $this->db->table($this->table)
										->where('id !=', "x'{$customerId}'", false)
										->groupStart()
											->where('email', (string)$email)
											->orWhere('cpf', (string)$cpf)
										->groupEnd()
										->countAllResults() > 0;

  }

}