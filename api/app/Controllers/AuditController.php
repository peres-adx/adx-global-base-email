<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class AuditController extends Controller
{

	public function run(): ResponseInterface
	{

		$this->response->setHeader('Access-Control-Allow-Origin', '*')
		               ->setHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
		               ->setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');

		$projectRoot	= ROOTPATH;
		$command			= "cd /d " . escapeshellarg($projectRoot) . " && composer test:report 2>&1";
		$output				= [];
		$resultCode		= 0;

		exec($command, $output, $resultCode);

		if ($resultCode !== 0) {
			return $this->response->setJSON([
				'status' => 'error',
				'detail' => 'Falha crítica ao processar o pipeline de auditoria no servidor.',
				'log'    => $output
			])->setStatusCode(500);
		}

		$currentScheme	= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
		$currentHost		= $_SERVER['HTTP_HOST'];
		$scriptPath 		= str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
		$cleanBase  		= rtrim($scriptPath, '/');

		if (str_contains($cleanBase, '/api/api')) $cleanBase = str_replace('/api/api', '/api', $cleanBase);
		if (str_contains($cleanBase, '/public')) $cleanBase = str_replace('/public', '', $cleanBase);

		$cleanBase = rtrim($cleanBase, '/');

		$reportUrl = $currentScheme . '://' . $currentHost . $cleanBase . '/tests/report/';

		return $this->response->setJSON([
			'status'     => 'success',
			'detail'     => 'Documento de Auditoria criado com sucesso.',
			'report_url' => $reportUrl
		])->setStatusCode(200);

	}

}