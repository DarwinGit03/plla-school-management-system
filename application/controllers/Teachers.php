<?php 

class Teachers extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->requireLogin();

        $this->requireRole([4]);
    }
}