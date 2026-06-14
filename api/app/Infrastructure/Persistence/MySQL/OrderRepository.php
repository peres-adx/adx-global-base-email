<?php

namespace App\Infrastructure\Persistence\MySQL;

use App\Domain\Common\Result; 
use App\Domain\Entities\Order;
use App\Domain\Repositories\Orders\IOrderRepository;
use App\Domain\ValueObjects\Uuid;
use CodeIgniter\Database\BaseConnection;

class OrderRepository implements IOrderRepository
{

  private string $table				= 'orders';
  private string $baseSelect	= '
    HEX(o.id) as id, 
    HEX(o.customer_id) as customer_id, 
    o.description, 
    o.total_value, 
    o.created_at,
    c.name as customer_name,
    c.cpf as customer_cpf
  ';

	public function __construct(private readonly BaseConnection $db) {}

	public function listAll(): Result
  {

		$rows = $this->db->table("{$this->table} o")
											->select($this->baseSelect)
											->join('customers c', 'o.customer_id = c.id')
											->get()
											->getResultArray();

    return !empty($rows)
      ? Result::success(array_map(fn($row) => Order::restore($row)->toArray(), $rows), "Pedidos listados com sucesso.")
      : Result::success([], "Nenhum pedido encontrado.");

  }

	public function listById(Uuid $id): Result
  {

		$row = $this->db->table("{$this->table} o")
										->select($this->baseSelect)
										->join('customers c', 'o.customer_id = c.id')
										->where('o.id', "UNHEX('{$id}')", false)
										->get()
										->getRowArray();

    return $row 
      ? Result::success(Order::restore($row)->toArray(), "Pedido localizado.") 
      : Result::failure("Pedido não encontrado.");

  }

	public function listByCustomer(Uuid $customerId): Result
  {

		$rows = $this->db->table("{$this->table} o")
											->select($this->baseSelect)
											->join('customers c', 'o.customer_id = c.id')
											->where('o.customer_id', "UNHEX('{$customerId}')", false)
											->get()
											->getResultArray();

		return !empty($rows)
			? Result::success(array_map(fn($row) => Order::restore($row)->toArray(), $rows), "Pedidos localizados com sucesso.")
			: Result::success([], "Nenhum pedido encontrado para este cliente.");

  }

	public function register(Order $order): Result
  {

		$data	= $order->toArray();
		$id		= array_shift($data);
		$cId	= $data['customer_id'];

		unset($data['customer_id']);

		$saved = $this->db->table($this->table)
											->set('id', "UNHEX('{$id}')", false)
											->set('customer_id', "UNHEX('{$cId}')", false)
											->set($data)
											->insert();

    return $saved 
      ? Result::success($order->toArray(), "Pedido registrado com sucesso.") 
      : Result::failure("Erro ao registrar o pedido.");

  }

	public function update(Order $order): Result
  {

    $id		= (string) $order->getId();
    $data = [
      'description' => $order->getDescription(),
      'total_value' => $order->getTotalValue()
    ];

    $updated = $this->db->table($this->table)
												->where('id', "UNHEX('{$id}')", false)
												->update($data);

    return $updated 
      ? Result::success($order->toArray(), "Pedido atualizado com sucesso.") 
      : Result::failure("Erro ao atualizar o pedido.");

  }

	public function delete(Uuid $id): Result
  {

    $this->db->table($this->table)
							->where('id', "UNHEX('{$id}')", false)
							->delete();

    return ($this->db->affectedRows() > 0)
      ? Result::success(null, "Pedido excluído com sucesso.")
      : Result::failure("Pedido não encontrado.");

  }

	public function existsById(Uuid $id): bool
  {

    return $this->db->table($this->table)
										->where('id', "UNHEX('{$id}')", false)
										->countAllResults() > 0;

  }

}