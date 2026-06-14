<?php

namespace App\Infrastructure\Persistence\SQLServer;

use App\Domain\Common\Result;
use App\Domain\Entities\Customer;
use App\Domain\Repositories\Customers\ICustomerRepository;
use App\Domain\ValueObjects\{Cpf, Email, Uuid};
use CodeIgniter\Database\BaseConnection;

class CustomerRepository implements ICustomerRepository
{

	private string $table      = 'customers';
	private string $baseSelect = 'CONVERT(VARCHAR(32), id, 2) as id, CONVERT(VARCHAR(32), user_id, 2) as user_id, name, email, cpf, rg, age, phone, address, zipcode, number, district, city, fu';

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
										->where('id', "CONVERT(BINARY(16), '0x{$id}', 1)", false)
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
											->where('user_id', "CONVERT(BINARY(16), '0x{$userId}', 1)", false)
											->get()
											->getResultArray();

		return !empty($rows)
			? Result::success(array_map(fn($row) => Customer::restore($row)->toArray(), $rows), "Clientes listados com sucesso.")
			: Result::success([], "Nenhum cliente encontrado para o usuário especificado.");

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
		$userId = $data['user_id'];
		
		unset($data['user_id']);

		$saved  = $this->db->table($this->table)
												->set('id', "CONVERT(BINARY(16), '0x{$id}', 1)", false)
												->set('user_id', "CONVERT(BINARY(16), '0x{$userId}', 1)", false)
												->set($data)
												->insert();

		return $saved 
			? Result::success($customer->toArray(), "Cliente cadastrado com sucesso.") 
			: Result::failure("Erro ao cadastrar o cliente.");

	}

	public function update(Customer $customer): Result
	{

		$data = $customer->toArray();
		$id   = array_shift($data);

		unset($data['user_id']); 

		$updated = $this->db->table($this->table)
												->where('id', "CONVERT(BINARY(16), '0x{$id}', 1)", false)
												->update($data);

		return $updated 
			? Result::success($customer->toArray(), "Dados do cliente atualizado com sucesso.") 
			: Result::failure("Erro ao atualizar os dados do cliente.");

	}

	public function delete(Uuid $id): Result
	{

		$this->db->table($this->table)
							->where('id', "CONVERT(BINARY(16), '0x{$id}', 1)", false)
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

	public function existsForOther(Email $email, Cpf $cpf, Uuid $id): bool
	{

		return $this->db->table($this->table)
											->where('id !=', "CONVERT(BINARY(16), '0x{$id}', 1)", false)
											->groupStart()
												->where('email', (string) $email)
												->orWhere('cpf', (string) $cpf)
											->groupEnd()
										->countAllResults() > 0;

	}

}