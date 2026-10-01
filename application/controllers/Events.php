<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Events extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->load->model('system/School_event_model', 'school_event_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $this->render_page('dashboard/events/index', [
            'title' => 'School events',
            'page_title' => 'School events',
            'page_subtitle' => 'View upcoming activities and completed events.',
            'breadcrumb' => ['Events'],
            'upcoming_events' => $this->school_event_model->get_upcoming(),
            'completed_events' => $this->school_event_model->get_completed(),
            'editing_event' => null,
            'error_message' => $this->session->flashdata('error'),
            'success_message' => $this->session->flashdata('success')
        ]);
    }

    public function edit($id)
    {
        $this->requirePermission('dashboard.events.manage');
        $event = $this->school_event_model->get_by_id((int) $id);
        if (!$event || $event->status !== 'active') { show_404(); return; }
        $this->render_page('dashboard/events/index', [
            'title' => 'Edit school event',
            'page_title' => 'School events',
            'page_subtitle' => 'Update the event details.',
            'breadcrumb' => ['Events', 'Edit'],
            'upcoming_events' => $this->school_event_model->get_upcoming(),
            'completed_events' => $this->school_event_model->get_completed(),
            'editing_event' => $event,
            'error_message' => $this->session->flashdata('error'),
            'success_message' => $this->session->flashdata('success')
        ]);
    }

    public function save()
    {
        $this->requirePermission('dashboard.events.manage');
        if ($this->input->method() !== 'post') { show_404(); return; }

        $id = (int) $this->input->post('id', true);
        if ($id && (!$this->school_event_model->get_by_id($id) || $this->school_event_model->get_by_id($id)->status !== 'active')) {
            show_404(); return;
        }
        $this->form_validation->set_rules('title', 'Event title', 'trim|required|max_length[160]');
        $this->form_validation->set_rules('description', 'Description', 'trim|max_length[2000]');
        $this->form_validation->set_rules('location', 'Location', 'trim|max_length[180]');
        $this->form_validation->set_rules('starts_at', 'Start date and time', 'trim|required');
        $this->form_validation->set_rules('ends_at', 'End date and time', 'trim|required');

        $start = $this->parse_datetime($this->input->post('starts_at', true));
        $end = $this->parse_datetime($this->input->post('ends_at', true));
        if (!$this->form_validation->run() || !$start || !$end || $end < $start) {
            $this->session->set_flashdata('error', validation_errors(' ', ' ') ?: 'Enter valid dates and ensure the end is after the start.');
            return redirect($id ? 'events/edit/' . $id : 'events');
        }

        $now = date('Y-m-d H:i:s');
        $data = [
            'title' => trim((string) $this->input->post('title', true)),
            'description' => trim((string) $this->input->post('description', true)) ?: null,
            'location' => trim((string) $this->input->post('location', true)) ?: null,
            'starts_at' => $start,
            'ends_at' => $end,
            'updated_by' => (string) $this->session->userdata('employee_no'),
            'updated_at' => $now
        ];
        if (!$id) {
            $data['status'] = 'active';
            $data['created_by'] = (string) $this->session->userdata('employee_no');
            $data['created_at'] = $now;
        }

        if (!$this->school_event_model->save($data, $id ?: null)) {
            $this->session->set_flashdata('error', 'The event could not be saved. Please try again.');
            return redirect($id ? 'events/edit/' . $id : 'events');
        }
        $this->audit_log_service->log('events', $id ? 'EVENT_UPDATED' : 'EVENT_CREATED', $id ?: $this->db->insert_id(), 'School event saved.');
        $this->session->set_flashdata('success', $id ? 'Event updated.' : 'Event created.');
        return redirect('events');
    }

    public function cancel($id)
    {
        $this->requirePermission('dashboard.events.manage');
        if ($this->input->method() !== 'post') { show_404(); return; }
        $event = $this->school_event_model->get_by_id((int) $id);
        if (!$event) { show_404(); return; }
        if ($this->school_event_model->cancel($id, (string) $this->session->userdata('employee_no'))) {
            $this->audit_log_service->log('events', 'EVENT_CANCELLED', (int) $id, 'School event cancelled.');
            $this->session->set_flashdata('success', 'Event cancelled.');
        }
        return redirect('events');
    }

    private function parse_datetime($value)
    {
        $value = trim((string) $value);
        $parsed = DateTime::createFromFormat('Y-m-d\TH:i', $value);
        return $parsed && $parsed->format('Y-m-d\TH:i') === $value ? $parsed->format('Y-m-d H:i:s') : false;
    }

    private function render_page($content, array $data)
    {
        $data['content'] = $content;
        $data['page_styles'] = ['assets/css/events.css'];
        $data['can_manage_events'] = $this->can('dashboard.events.manage');
        $data['error_message'] = $data['error_message'] ?? null;
        $data['success_message'] = $data['success_message'] ?? null;
        $this->load->view('dashboard/layouts/master', array_merge($this->data, $data));
    }
}
