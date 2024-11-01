<?php

class AppCtx
{
    private array $cfg = [];
    private array $lang = [];
    private Database $db;
    private Hosts $hosts;
    private User $user;
    private Categories $categories;
    private Networks $networks;
    private Items $items;
    private Mailer $mailer;

    public function __construct(array $cfg, array $lang, Database $db)
    {
        $this->cfg = $cfg;
        $this->db = $db;
        $this->lang = $lang;
        spl_autoload_register(array($this, 'autoload'));
    }

    /* Autoload class files */

    public function autoload(string $class_name): void
    {
        $file_path = 'class/' . $class_name . '.php';

        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    /* Getters */

    public function getAppCfg(): array
    {
        return $this->cfg;
    }

    public function getAppDb(): Database
    {
        return $this->db;
    }

    public function getAppLang(): array
    {
        return $this->lang;
    }

    public function getAppHosts(): Hosts
    {
        if (!isset($this->hosts)) {
            $this->hosts = new Hosts($this);
        }

        return $this->hosts;
    }

    public function getAppUser(): User
    {
        if (!isset($this->user)) {
            $this->user = new User($this);
        }
        return $this->user;
    }

    public function getAppCategories(): Categories
    {
        if (!isset($this->categories)) {
            $this->categories = new Categories($this);
        }

        return $this->categories;
    }

    public function getAppNetworks(): Networks
    {
        if (!isset($this->networks)) {
            $this->networks = new Networks($this);
        }

        return $this->networks;
    }

    public function getAppItems()
    {
        if (!isset($this->items)) {
            $this->items = new Items($this);
        }

        return $this->items;
    }

    public function getAppMail()
    {
        if (!isset($this->mailer)) {
            $this->mailer = new Mailer($this);
        }

        return $this->mailer;
    }
}
