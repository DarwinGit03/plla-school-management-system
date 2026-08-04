class Parents extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->requireLogin();

        $this->requireRole([5]);
    }
}