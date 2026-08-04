class Settings extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->requireLogin();

        $this->requireRole([1,2]);
    }

    public function index()
    {

    }
}