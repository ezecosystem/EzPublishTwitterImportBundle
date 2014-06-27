<?php

namespace xrow\EzPublishTwitterImportBundle\Service;

class UserService 
{
    protected $adminuser;

    public function __construct( $config)
    {
        $this->config = $config;
    }

    public function getUser()
    {
        $adminuser = $this->config['import_user'];
        return $adminuser;
    }
}