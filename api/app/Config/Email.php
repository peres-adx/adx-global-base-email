<?php

// namespace Config;

// use CodeIgniter\Config\BaseConfig;

// class Email extends BaseConfig
// {
// 	public string $fromEmail  = '';
// 	public string $fromName   = '';
// 	public string $recipients = '';
// 	public string $userAgent = 'CodeIgniter';
// 	public string $protocol = 'mail';
// 	public string $mailPath = '/usr/sbin/sendmail';
// 	public string $SMTPHost = '';
// 	public string $SMTPAuthMethod = 'login';
// 	public string $SMTPUser = '';
// 	public string $SMTPPass = '';
// 	public int $SMTPPort = 25;
// 	public int $SMTPTimeout = 5;
// 	public bool $SMTPKeepAlive = false;
// 	public string $SMTPCrypto = 'tls';
// 	public bool $wordWrap = true;
// 	public int $wrapChars = 76;
// 	public string $mailType = 'text';
// 	public string $charset = 'UTF-8';
// 	public bool $validate = false;
// 	public int $priority = 3;
// 	public string $CRLF = "\r\n";
// 	public string $newline = "\r\n";
// 	public bool $BCCBatchMode = false;
// 	public int $BCCBatchSize = 200;
// 	public bool $DSN = false;
// }

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
	public string $fromEmail			= '';
	public string $fromName				= '';
	public string $recipients			= '';
	public string $userAgent			= 'CodeIgniter';
	public string $protocol				= 'smtp';
	public string $mailPath				= '/usr/sbin/sendmail';
	public string $SMTPHost				= '';
	public bool 	$SMTPAuth				= true;
	public string $SMTPUser				= '';
	public string $SMTPPass				= '';
	public int    $SMTPPort				= 587;
	public int    $SMTPTimeout		= 20;
	public bool   $SMTPKeepAlive	= false;
	public string $SMTPCrypto			= 'tls';
	public bool   $wordWrap				= true;
	public int    $wrapChars			= 76;
	public string $mailType				= 'html';
	public string $charset				= 'UTF-8';
	public bool   $validate				= false;
	public int    $priority				= 3;
	public string $CRLF						= "\r\n";
	public string $newline				= "\r\n";
	public bool   $BCCBatchMode		= false;
	public int    $BCCBatchSize		= 200;
	public bool   $DSN						= false;

	public function __construct()
	{
		parent::__construct();
		$this->fromEmail  = env('email.fromEmail')				?? $this->fromEmail;
		$this->fromName   = env('email.fromName')					?? $this->fromName;
		$this->SMTPHost   = env('email.SMTPHost')					?? $this->SMTPHost;
		$this->SMTPAuth   = (bool)(env('email.SMTPAuth')	?? $this->SMTPAuth);
		$this->SMTPUser   = env('email.SMTPUser')					?? $this->SMTPUser;
		$this->SMTPPass   = env('email.SMTPPass')					?? $this->SMTPPass;
		$this->SMTPPort   = (int)(env('email.SMTPPort')		?? $this->SMTPPort);
		$this->SMTPCrypto = env('email.SMTPCrypto')				?? $this->SMTPCrypto;
	}

}