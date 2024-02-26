<?php

class AppCtx {

    private array $cfg;
    private array $lang;
    private Database $db;
    private Hosts $hosts;
    private User $user;
    private Categories $categories;

    public function __construct(array $cfg, array $lang, Database $db) {
        $this->cfg = $cfg;
        $this->db = $db;
        $this->lang = $lang;
    }

    /*
     * Setters/Getters
     */

    public function getAppCfg() {
        return $this->cfg;
    }

    public function getAppDb() {
        return $this->db;
    }

    public function getAppLang() {
        return $this->lang;
    }

    public function getAppHosts() {
        return $this->hosts;
    }

    public function setAppHosts(Hosts $hosts) {
        $this->hosts = $hosts;
    }

    public function getAppUser() {
        return $this->user;
    }

    public function setAppUser(User $user) {
        $this->user = $user;
    }

    public function getAppCategories() {
        if (!isset($this->categories)) {
            $this->categories = new Categories($this);
        }

        return $this->categories;
    }
}
