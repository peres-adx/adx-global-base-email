<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cors extends BaseConfig
{

	public array $default = [
		'allowedOrigins'					=> ['*'],
		'allowedOriginsPatterns'	=> [],
		'supportsCredentials'			=> false,
		'allowedHeaders'					=> ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'Origin'],
		'allowedMethods'					=> ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'],
		'exposedHeaders'					=> [],
		'maxAge'									=> 7200,
	];

}