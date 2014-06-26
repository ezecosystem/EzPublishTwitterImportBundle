<?php

namespace xrow\EzPublishTwitterImportBundle\Service;

class UserService 
{
    protected $adminuser;

    public function __construct( $config)
    {
        $this->config = $config;
    }

    public function setUser()
    {
        $adminuser = $this->config['import_user'];
        return $adminuser;
    }
}