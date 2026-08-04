<?php 

class Finance extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->requireLogin();

        $this->requireRole([3]);
    }

    public function index()
    {

    }
}