<?php

namespace App\Infrastructure\Persistence\SQLServer;

use App\Domain\Repositories\Users\IUserTokenRepository; 
use CodeIgniter\Database\BaseConnection;

class UserTokenRepository implements IUserTokenRepository
{

	private string $table = 'user_setup_tokens';

	public function __construct(private readonly BaseConnection $db) {}

	public function saveInvite(string $userId, string $token): bool
	{

		$sql = "INSERT INTO {$this->table} (id, user_id, token, expires_at) 
						VALUES (
							CONVERT(BINARY(16), '0x' + REPLACE(?, '-', ''), 1), 
							CONVERT(BINARY(16), '0x' + REPLACE(?, '-', ''), 1), 
							?, 
							?
						)";

		return $this->db->query($sql, [ $userId, $userId, $token, date('Y-m-d H:i:s', strtotime('+24 hours')) ]);

	}

	public function findByToken(string $token): ?object
	{

		$row = $this->db->table($this->table)
										->select("CONVERT(VARCHAR(32), user_id, 2) as userId, expires_at")
										->where('token', $token)
										->where('used_at', null)
										->get()
										->getRow();

		if (!$row) return null;

		return (object) [
			'userId'    => $row->userId,
			'isExpired' => strtotime($row->expires_at) < time()
		];

	}

	public function markAsUsed(string $token): bool 
	{ 
		return $this->db->table($this->table)
										->where('token', $token)
										->update(['used_at' => date('Y-m-d H:i:s')]); 
	}

}