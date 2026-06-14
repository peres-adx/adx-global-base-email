<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{

	/**
	 * O caminho para os arquivos de migração.
	 */
	public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

	/**
	 * O grupo de conexão padrão.
	 */
	public string $defaultGroup = 'default';

	/**
	 * Configuração padrão (Template).
	 * Nota: Estes valores são apenas placeholders, pois seu Services.php
	 * injeta a configuração real do .env dinamicamente.
	 */
	public array $default = [
		'DSN'						=> '',
		'hostname'			=> 'localhost',
		'username'			=> '',
		'password'			=> '',
		'database'			=> '',
		'DBDriver'			=> 'MySQLi',
		'DBPrefix'			=> '',
		'pConnect'			=> false,
		'DBDebug'				=> true,
		'charset'				=> 'utf8mb4',
		'DBCollat'			=> 'utf8mb4_unicode_ci',
		'swapPre'				=> '',
		'encrypt'				=> false,
		'compress'			=> false,
		'strictOn'			=> false,
		'failover'			=> [],
		'port'					=> 3306,
		'numberNative'	=> false,
		'foundRows'			=> false,
	];

	/**
	 * Grupo de testes (opcional, mantido por compatibilidade do CI4)
	 */
	public array $tests = [
		'DSN'         	=> '',
		'hostname'			=> '127.0.0.1',
		'username'			=> '',
		'password'			=> '',
		'database'			=> ':memory:',
		'DBDriver'			=> 'SQLite3',
		'DBPrefix'			=> 'db_',
		'DBDebug'				=> true,
		'pConnect'			=> false,
		'charset'				=> 'utf8',
		'DBCollat'			=> '',
		'swapPre'				=> '',
		'encrypt'				=> false,
		'compress'			=> false,
		'strictOn'			=> false,
		'failover'			=> [],
		'port'					=> 3306,
		'foreignKeys'		=> true,
		'busyTimeout'		=> 1000,
	];

	public function __construct()
	{
		parent::__construct();
		if (ENVIRONMENT === 'testing') $this->defaultGroup = 'tests';
	}

}