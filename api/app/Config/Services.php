<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use App\Domain\Services\{AuthService, CustomerService, OrderService, UserService};
use App\Domain\Repositories\Users\{IUserRepository, IUserTokenRepository};
use App\Domain\Repositories\Orders\IOrderRepository;
use App\Domain\Repositories\Customers\ICustomerRepository;
use Config\Database;

class Services extends BaseService
{

	private static function resolveRepository(string $className): object
	{

		$header = service('request')->header('X-Database-Engine')?->getValue();
		
		$rawEngine    = !empty($header) && $header !== 'SELECIONE O BANCO DE DADOS' ? trim($header) : env('DB_ENGINE', 'MySQL');
		$engine       = str_replace(' ', '', $rawEngine);
		$upper        = strtoupper($engine);

		$config = [
			'DBDriver' => env("{$upper}_DRIVER"),
			'hostname' => env("{$upper}_HOSTNAME"),
			'username' => env("{$upper}_USERNAME"),
			'password' => env("{$upper}_PASSWORD"),
			'database' => self::resolveDatabasePath(env("{$upper}_DATABASE")),
			'charset'  => env("{$upper}_CHARSET", 'utf8'),
			'DBDebug'  => (bool) env("{$upper}_DBDEBUG", true),
		];

		$extraKeys = ['ENCRYPT' => 'encrypt', 'TRUST_CERT' => 'trustServerCertificate'];
		foreach ($extraKeys as $envSuffix => $dbKey) {
			$val = env("{$upper}_{$envSuffix}");
			$config[$dbKey] = filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $val;
		}

		$namespace = "App\\Infrastructure\\Persistence\\{$engine}\\{$className}";

		if (!class_exists($namespace)) throw new \RuntimeException("Repositório [{$namespace}] não localizado.");

		$driverClass = "CodeIgniter\\Database\\{$config['DBDriver']}\\Connection";
		
		$db = new $driverClass($config);
		$db->initialize();

		return new $namespace($db);

	}
	
	private static function resolveDatabasePath(?string $db): string
	{
		if (!$db || !str_contains($db, 'writable/')) return (string) $db;
		return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ROOTPATH . $db);
	}

	// User Repositories
	public static function userRepository(bool $getShared = true): IUserRepository
	{
		$hasExternal = !empty(service('request')->header('X-Database-Engine')?->getValue());
		if ($getShared && !$hasExternal) return static::getSharedInstance('userRepository');
		return self::resolveRepository('UserRepository');
	}

	public static function userTokenRepository(bool $getShared = true): IUserTokenRepository
	{
		$hasExternal = !empty(service('request')->header('X-Database-Engine')?->getValue());
		if ($getShared && !$hasExternal) return static::getSharedInstance('userTokenRepository');
		return self::resolveRepository('UserTokenRepository');
	}

	// Application Services
	public static function userService(bool $getShared = true): UserService
	{
		if ($getShared) return static::getSharedInstance('userService');
		return new UserService(static::userRepository(), static::userTokenRepository());
	}

	public static function customerRepository(bool $getShared = true): ICustomerRepository
	{
		if ($getShared) return static::getSharedInstance('customerRepository');
		return self::resolveRepository('CustomerRepository');
	}

	public static function customerService(bool $getShared = true): CustomerService
	{
		if ($getShared) return static::getSharedInstance('customerService');
		return new CustomerService(static::customerRepository(), static::userRepository());
	}

	public static function orderRepository(bool $getShared = true): IOrderRepository
	{
		if ($getShared) return static::getSharedInstance('orderRepository');
		return self::resolveRepository('OrderRepository');
	}

	public static function orderService(bool $getShared = true): OrderService
	{
		if ($getShared) return static::getSharedInstance('orderService');
		return new OrderService(static::orderRepository());
	}

	public static function authService(bool $getShared = true): AuthService
	{
		if ($getShared) return static::getSharedInstance('authService');
		return new AuthService(static::userRepository(false));
	}

}