<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private PHPMailer $phpMailer;
    private AppCtx $ctx;

    /**
     * @var array<string> $cfg
     */
    private array $cfg;

    public function __construct(AppCtx $ctx)
    {
        $this->cfg = $ctx->getAppCfg();
        $this->ctx = $ctx;

        if (!$this->cfg['mailer_enabled']) {
            return;
        }
        // Verify  PHPMailer install
        if (\Composer\InstalledVersions::isInstalled('phpmailer/phpmailer')) {
            $this->phpMailer = new PHPMailer(true);
            $this->phpMailer->setLanguage($this->cfg['lang']);
        } else {
            Log::err($ctx->getAppLang()->get('L_ERR_MAILER'));
            return;
        }
        if (!Filters::varIp($this->cfg['mail_host'])) {
            if (!Filters::varHostname($this->cfg['mail_host'])) {
                Log::err($ctx->getAppLang()->get('L_ERR_MAIL_HOST'));
                return;
            }
        }
        if ($this->cfg['mail_auth'] && empty($this->cfg['mail_username']) || empty($this->cfg['mail_password'])) {
            Log::err($ctx->getAppLang()->get('L_ERR_USERPASS_INVALID'));
            return;
        }
        if (!empty($this->cfg['mail_port']) && !is_numeric($this->cfg['mail_port'])) {
            Log::err($ctx->getAppLang()->get('L_ERR_PORT_INVALID'));
            return;
        }
        $this->configure();
    }

    public function sendEmailMultiple(array $emails, string $subject, string $body)
    {
        foreach ($emails as $email) {
            if (!$this->sendEmail($email, $subject, $body)) {
                Log::err('L_ERR_SENDING_EMAILS ' . $email);
                break;
            }
        }
    }

    public function sendEmail(string $to, string $subject, string $body): bool
    {
        if (!$this->phpMailer) {
            return false;
        }

        try {
            $this->phpMailer->setFrom($this->phpMailer->Username);
            $this->phpMailer->addAddress($to);
            $this->phpMailer->Subject = $subject;
            $this->phpMailer->Body = $body;

            $this->phpMailer->send();
            return true;
        } catch (Exception $e) {
            Log::err('Mail: ' . $e->getMessage());
            return false;
        }
    }

    private function configure(): void
    {

        if ($this->phpMailer) {
            $this->phpMailer->isSMTP();
            //$this->phpMailer->SMTPDebug = 4;
            $this->phpMailer->Host = $this->cfg['mail_host'];
            $this->phpMailer->SMTPAuth = $this->cfg['mail_auth'];
            if ($this->cfg['mail_auth']) {
                $this->phpMailer->Username = $this->cfg['mail_username'];
                $this->phpMailer->Password = $this->cfg['mail_password'];
            }
            if ($this->cfg['mail_auth_type']) {
                $this->phpMailer->AuthType = $this->cfg['mail_auth_type'];
            }
            $this->phpMailer->SMTPSecure = $this->cfg['mail_security'];

            $this->phpMailer->Port = $this->cfg['mail_port'];
        }
    }
}
