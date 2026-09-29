class Parents extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->requireLogin();

        $this->requireRole([5]);

        $this->load->model('parent/Parent_model');
    }

    public function index()
    {
        return redirect('parents/payment_history');
    }

    public function payment_history()
    {
        $email = trim((string) $this->session->userdata('email'));
        $payments = $email !== ''
            ? $this->Parent_model->get_payment_history_by_guardian_email($email)
            : [];

        $this->load->view('dashboard/layouts/master', [
            'title' => 'Payment History',
            'page_title' => 'Payment History',
            'page_subtitle' => 'Payments recorded for students linked to your parent account.',
            'breadcrumb' => ['Parent Portal', 'Payment History'],
            'content' => 'parents/payment_history',
            'payments' => $payments
        ]);
    }
}
