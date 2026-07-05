<?php

namespace App\Infrastructure\Persistence\SQLServer;

use App\Domain\Repositories\Users\IUserTokenRepository;

use CodeIgniter\Database\BaseConnection;

class UserTokenRepository implements IUserTokenRepository
{

	private string $table = 'user_setup_tokens';

	public function __construct(private readonly BaseConnection $db) {}

	public function saveInvite(string $userId, string $token, int $version = 1): bool
	{

		$tokenId = bin2hex(random_bytes(16));

		return $this->db->table($this->table)
										->set('id', "CONVERT(BINARY(16), '0x{$tokenId}', 1)", false)
										->set('user_id', "CONVERT(BINARY(16), '0x{$userId}', 1)", false)
										->set([
											'token'      => $token,
											'version'    => $version,
											'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
										])
										->insert();

	}

	public function findByToken(string $token): ?object
	{

		$row = $this->db->table($this->table)
										->select("CONVERT(VARCHAR(32), user_id, 2) as userId, version, expires_at")
										->where('token', $token)
										->where('used_at', null)
										->get()
										->getRowArray();

		return $row
			? (object) [
				'userId'    => $row['userId'],
				'version'   => (int) $row['version'],
				'isExpired' => strtotime($row['expires_at']) < time()
			]
			: null;

	}

	public function markAsUsed(string $token): bool { return $this->db->table($this->table)->where('token', $token)->update(['used_at' => date('Y-m-d H:i:s')]); }

}