<?php

namespace App\Infrastructure\Persistence\SQLite;

use App\Domain\Repositories\Users\IUserTokenRepository;

use CodeIgniter\Database\BaseConnection;

class UserTokenRepository implements IUserTokenRepository
{

	private string $table = 'user_setup_tokens';

	public function __construct(private readonly BaseConnection $db) {}

	public function saveInvite(string $userId, string $token, int $version = 1): bool
	{

		$tokenId			= bin2hex(random_bytes(16));
		$cleanUserId	= str_replace('-', '', $userId);

		$sql = "INSERT INTO {$this->table} 
							(id, user_id, token, version, expires_at) 
						VALUES 
							(x'{$tokenId}', x'{$cleanUserId}', ?, ?, ?)";

		return $this->db->query($sql, [
			$token, 
			$version, 
			date('Y-m-d H:i:s', strtotime('+24 hours')) 
		]);

	}

	public function findByToken(string $token): ?object
	{

		$row = $this->db->table($this->table)
										->select("upper(hex(user_id)) as userId, version, expires_at")
										->where('token', $token)
										->where('used_at', null)
										->get()
										->getRow();

		if (!$row) return null;

		return (object) [
			'userId'    => $row->userId,
			'version'   => (int) $row->version,
			'isExpired' => strtotime($row->expires_at) < time()
		];

	}

	public function markAsUsed(string $token): bool	{	return $this->db->table($this->table)->where('token', $token)->update(['used_at' => date('Y-m-d H:i:s')]); }

}