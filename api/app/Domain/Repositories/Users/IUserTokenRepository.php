<?php

namespace App\Domain\Repositories\Users;

interface IUserTokenRepository
{
	public function saveInvite(string $userId, string $token):	bool;
	public function findByToken(string $token):									?object;
	public function markAsUsed(string $token):									bool;
}