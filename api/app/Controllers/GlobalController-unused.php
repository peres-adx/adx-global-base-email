<?php

namespace App\Controllers;

use App\Domain\Common\Result;
use App\Traits\ResponseHandler;

use Config\Services;

class GlobalController extends BaseController
{

	use ResponseHandler;

	public function runAudit()
	{

		$this->applyCorsHeaders();

		$projectRoot = ROOTPATH;
		$command     = "cd /d " . escapeshellarg($projectRoot) . " && composer test:report 2>&1";
		$output      = [];
		$resultCode  = 0;

		exec($command, $output, $resultCode);

		if ($resultCode !== 0) return $this->sendResult(Result::failure("Falha crítica ao processar o pipeline de auditoria no servidor.", 500));

		$currentScheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
		$currentHost   = $_SERVER['HTTP_HOST'];
		$scriptPath    = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
		$cleanPath     = str_replace('/public', '', $scriptPath);
		$dynamicBase   = $currentScheme . '://' . $currentHost . $cleanPath;

		return $this->sendResult(Result::success(['report_url' => $dynamicBase . '/tests/report'], "Documento de Auditoria criado com sucesso."));

	}

	public function switchDatabase()
	{

		$this->applyCorsHeaders();

		$engine  = (string) ($this->request->getPost('engine') ?? '');
		$allowed = ['MongoDB', 'MySQL', 'SQLite', 'SQLServer'];

		if (!in_array($engine, $allowed, true)) return $this->sendResult(Result::failure("Engine de banco de dados inválida ou não suportada pelo ecossistema.", 202));

		if (session_status() === PHP_SESSION_NONE) session()->start();

		session()->set('ACTIVE_DB_ENGINE', $engine);

		return $this->sendResult(Result::success(null, "Engine de persistência alterada com sucesso para: {$engine}."));

	}

	private function applyCorsHeaders(): void
	{

		$this->response->setHeader('Access-Control-Allow-Origin', '*')
										->setHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
										->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');

	}

}