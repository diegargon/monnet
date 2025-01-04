<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    /**
     *
     * @var PHPMailer $phpMailer
     */
    private PHPMailer $phpMailer;

    /**
     *
     * @var AppContext $ctx
     */
    private AppContext $ctx;

    /**
     * @var Config $ncfg
     */
    private Config $ncfg;

    public function __construct(AppContext $ctx)
    {
        $this->ncfg = $ctx->get('Config');
        $this->ctx = $ctx;
        $lang = $this->ctx->get('Lang');

        if (!$this->ncfg->get('mail')) :
            return;
        endif;

        // Verify  PHPMailer install
        if (\Composer\InstalledVersions::isInstalled('phpmailer/phpmailer')) {
            $this->phpMailer = new PHPMailer(true);
            $this->phpMailer->setLanguage($this->ncfg->get('lang'));
        } else {
            Log::err($lang->get('L_ERR_MAILER'));
            return;
        }
        if (!Filters::varIp($this->ncfg->get('mail_host'))) {
            if (!Filters::varHostname($this->ncfg->get('mail_host'))) :
                Log::err($lang->get('L_ERR_MAIL_HOST'));
                return;
            endif;
        }
        if (
            $this->ncfg->get('mail_auth') &&
            empty($this->ncfg->get('mail_username')) ||
            empty($this->ncfg->get('mail_password'))
        ) :
            Log::err($lang->get('L_ERR_USERPASS_INVALID'));
            return;
        endif;
        if (!empty($this->ncfg->get('mail_port')) && !is_numeric($this->ncfg->get('mail_port'))) :
            Log::err($lang->get('L_ERR_PORT_INVALID'));
            return;
        endif;
        $this->configure();
    }

    /**
     *
     * @param array<string> $emails
     * @param string $subject
     * @param string $body
     *
     * @return void
     */
    public function sendMailMultiple(array $emails, string $subject, string $body): void
    {
        foreach ($emails as $email) {
            if (!$this->sendMail($email, $subject, $body)) :
                Log::err('L_ERR_SENDING_EMAILS ' . $email);
                break;
            endif;
        }
    }

    /**
     *
     * @param string $to
     * @param string $subject
     * @param string $body
     * @return bool
     */
    public function sendMail(string $to, string $subject, ?string $body): bool
    {
        if ($this->phpMailer == null) :
            return false;
        endif;

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

    /**
     *
     * @return void
     */
    private function configure(): void
    {
        $ncfg = $this->ncfg;
        if ($this->phpMailer != null) {
            $this->phpMailer->isSMTP();
            //$this->phpMailer->SMTPDebug = 4;
            $this->phpMailer->Host = $ncfg->get('mail_host');
            $this->phpMailer->SMTPAuth = (bool) $ncfg->get('mail_auth');
            if ($ncfg->get('mail_auth')) :
                $this->phpMailer->Username = $ncfg->get('mail_username');
                $this->phpMailer->Password = $ncfg->get('mail_password');
            endif;
            if ($ncfg->get('mail_auth_type')) :
                $this->phpMailer->AuthType = $ncfg->get('mail_auth_type');
            endif;
            if ($ncfg->get('smtp_security') == 'STARTTLS') :
                $this->phpMailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            else :
                $this->phpMailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            endif;
            $this->phpMailer->Port = $ncfg->get('mail_port');
        }
    }
}
