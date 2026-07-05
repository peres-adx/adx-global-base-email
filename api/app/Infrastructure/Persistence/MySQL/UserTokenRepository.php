<?php

namespace App\Infrastructure\Persistence\MySQL;

use App\Domain\Repositories\Users\IUserTokenRepository;

use CodeIgniter\Database\BaseConnection;

class UserTokenRepository implements IUserTokenRepository
{

	private string $table = 'user_setup_tokens';

	public function __construct(private readonly BaseConnection $db) {}

	//TODO: tirar os códigos com SQL e usar o padrão do CodeIgniter.
	// Verificar toda a API para verificar se está tudo no mesmo padrão. 

	public function saveInvite(string $userId, string $token, int $version = 1): bool
	{

		$tokenId = bin2hex(random_bytes(16));

		return $this->db->table($this->table)
											->set('id', "HEX('{$tokenId}')", false)
											->set('user_id', "UNHEX(REPLACE('{$userId}', '-', ''))", false)
											->set([
												'token'				=> $token,
												'version'			=> $version,
												'expires_at'	=> date('Y-m-d H:i:s', strtotime('+24 hours'))
											])
											->insert();

		return $this->db->table($this->table)
											->set('id', "UNHEX('{$id}')", false)
											->set($data)
											->insert();

	}

	public function findByToken(string $token): ?object
	{

		$row = $this->db->table($this->table)
										->select("HEX(user_id) as userId, version, expires_at")
										->where('token', $token)
										->where('used_at', null)
										->get()
										->getRow();

		if (!$row) return null;

		return (object) [
			'userId'		=> $row->userId,
			'version'		=> (int) $row->version,
			'isExpired'	=> strtotime($row->expires_at) < time()
		];

	}

	public function markAsUsed(string $token): bool { return $this->db->table($this->table)->where('token', $token)->update(['used_at' => date('Y-m-d H:i:s')]); }

}