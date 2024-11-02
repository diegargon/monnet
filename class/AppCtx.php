<?php

class AppCtx
{
    /**
     * @var array<string|int> $cfg
     */
    private array $cfg = [];

    /**
     * @var array<string> $lang
     */
    private array $lang = [];
    private Database $db;
    private Hosts $hosts;
    private User $user;
    private Categories $categories;
    private Networks $networks;
    private Items $items;
    private Mailer $mailer;

    /**
     * @param array<string> $cfg
     * @param array<string> $lang
     * @param Database $db
     */
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

    /**
     * @return Hosts
     */
    public function getAppHosts(): Hosts
    {
        if (!isset($this->hosts)) {
            $this->hosts = new Hosts($this);
        }

        return $this->hosts;
    }

    /**
     * @return User
     */
    public function getAppUser(): User
    {
        if (!isset($this->user)) {
            $this->user = new User($this);
        }
        return $this->user;
    }

    /**
     * @return Categories
     */
    public function getAppCategories(): Categories
    {
        if (!isset($this->categories)) {
            $this->categories = new Categories($this);
        }

        return $this->categories;
    }

    /**
     * @return Networks
     */
    public function getAppNetworks(): Networks
    {
        if (!isset($this->networks)) {
            $this->networks = new Networks($this);
        }

        return $this->networks;
    }

    /**
     * @return Items
     */
    public function getAppItems()
    {
        if (!isset($this->items)) {
            $this->items = new Items($this);
        }

        return $this->items;
    }

    /**
     * @return Mailer
     */
    public function getAppMail()
    {
        if (!isset($this->mailer)) {
            $this->mailer = new Mailer($this);
        }

        return $this->mailer;
    }
}
