<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Referralpayment extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->config->load('payroll');
        $this->config->load('image_valid');
        $this->load->model("referral_payment_model");
        $this->load->model("referral_person_model");
        $this->load->library("form_validation");
        $this->load->library('system_notification');
        $this->load->library('SaasValidation');
    }

    public function add()
    {
        if (!$this->rbac->hasPrivilege('referral_payment', 'can_add')) {
            access_denied();
        }

        $data = array();
        $this->form_validation->set_rules("patient_id", $this->lang->line('patient'), 'required|trim|xss_clean');
        $this->form_validation->set_rules("payee", $this->lang->line('payee'), 'trim|required|xss_clean');
        $this->form_validation->set_rules("percentage", $this->lang->line('commission_percentage'), 'required|trim|xss_clean');
        $this->form_validation->set_rules("commission_amount", $this->lang->line('commission_amount'), 'trim|required|xss_clean');
        $this->form_validation->set_rules("patient_type", $this->lang->line('patient_type'), 'trim|required|xss_clean');
        $this->form_validation->set_rules("bill_amount", $this->lang->line('patient_bill_amount'), 'trim|required|xss_clean');
        $this->form_validation->set_rules("bill_no", $this->lang->line('bill_no_case_id'), 'trim|required|xss_clean|callback_check_billid');

        $this->form_validation->set_rules('payment_mode', $this->lang->line('payment_mode'), 'required|callback_valid_payment_mode');
        if ($this->input->post('payment_mode', TRUE) === 'Cheque') {
            $this->form_validation->set_rules('cheque_no', $this->lang->line('cheque_no'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('cheque_date', $this->lang->line('cheque_date'), 'trim|required|callback_valid_cheque_date');
            $this->form_validation->set_rules('document', $this->lang->line('document'), 'callback_handle_doc_upload[document]|callback_validateCanUploadFile[document]');
        }

        if ($this->form_validation->run() == false) {
            $msg = array(
                'payment_mode' => form_error('payment_mode'),
                'cheque_no' => form_error('cheque_no'),
                'cheque_date' => form_error('cheque_date'),
                'document' => form_error('document'),
                'patient_id'        => form_error('patient_id'),
                'payee'             => form_error('payee'),
                'percentage'        => form_error('percentage'),
                'commission_amount' => form_error('commission_amount'),
                'percentage'        => form_error('percentage'),
                'patient_type'      => form_error('patient_type'),
                'bill_amount'       => form_error('bill_amount'),
                'bill_no'           => form_error('bill_no'),

            );
            $data = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $payment = array(
                "referral_person_id" => $this->input->post("payee", TRUE),
                "patient_id"         => $this->input->post("patient_id", TRUE),
                "referral_type"      => $this->input->post("patient_type", TRUE),
                "billing_id"         => $this->input->post("bill_no", TRUE),
                "bill_amount"        => $this->input->post("bill_amount", TRUE),
                "percentage"         => $this->input->post("percentage", TRUE),
                "amount"             => $this->input->post("commission_amount", TRUE),
                "date"               => date("Y-m-d H:i:s"),
                "payment_mode"       => $this->input->post('payment_mode', TRUE),
            );

            if ($payment['payment_mode'] === 'Cheque') {
                $payment['cheque_no'] = $this->input->post('cheque_no', TRUE);
                $payment['cheque_date'] = $this->customlib->dateFormatToYYYYMMDD($this->input->post('cheque_date', TRUE));
                if (!empty($_FILES['document']['name'])) {
                    $size = $this->media_storage->getTmpFileSize('document');
                    $filename = $this->media_storage->fileupload('document', './uploads/payment_document/');
                    if (!$filename) {
                        echo json_encode(array('status' => 'fail', 'error' => array('document' => 'Document upload failed.'), 'message' => ''));
                        return;
                    }
                    $payment['attachment'] = $filename;
                    $payment['attachment_name'] = $_FILES['document']['name'];
                }
            }
            $saved = $this->referral_payment_model->add($payment);
            if ($saved === false) {
                if (!empty($payment['attachment'])) {
                    $this->media_storage->filedelete($payment['attachment'], 'uploads/payment_document');
                }
                echo json_encode(array('status' => 'fail', 'error' => array('payment_mode' => 'Could not save payment.'), 'message' => ''));
                return;
            }
            if (!empty($payment['attachment'])) {
                try {
                    $this->saasvalidation->updateResouceQuota('storage', $size);
                } catch (Exception $e) {
                    log_message('error', 'Referral document quota update failed: ' . $e->getMessage());
                }
            }

            $data = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));

            $referral_type   = $this->notificationsetting_model->getreferraltypeDetails($this->input->post("patient_type", TRUE));
            $referral_person = $this->notificationsetting_model->getreferralpersonDetails($this->input->post("payee", TRUE));

            $event_data = array(
                'patient_id'            => $this->input->post("patient_id", TRUE),
                'patient_type'          => $this->lang->line($referral_type['name']),
                'bill_no'               => $this->customlib->getSessionPrefixByType($referral_type['prefixes_type']) . $this->input->post('bill_no', TRUE),
                'patient_bill_amount'   => number_format((float) $this->input->post("bill_amount", TRUE), 2, '.', ''),
                'payee'                 => $referral_person['name'],
                'commission_percentage' => $this->input->post("percentage", TRUE),
                'commission_amount'     => $this->input->post("commission_amount", TRUE),
            );

            $this->system_notification->send_system_notification('add_referral_payment', $event_data);
        }
        echo json_encode($data);
    }

    public function handle_doc_upload($str, $var)
    {
        $image_validate = $this->config->item('file_validate');

        if (isset($_FILES[$var]) && !empty($_FILES[$var]['name'])) {

            if ($_FILES[$var]['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($_FILES[$var]['tmp_name'])) {
                $this->form_validation->set_message('handle_doc_upload', $this->lang->line('error_while_uploading_document'));
                return false;
            }
            $file_type = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES[$var]['tmp_name']);
            $file_size = $_FILES[$var]["size"];
            $file_name = $_FILES[$var]["name"];

            $allowed_extension = $image_validate["allowed_extension"];
            $allowed_mime_type = $image_validate["allowed_mime_type"];
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if ($files = filesize($_FILES[$var]['tmp_name'])) {
                if (!in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_doc_upload', $this->lang->line('file_type_extension_error_uploading_document'));
                    return false;
                }
                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_doc_upload', $this->lang->line('extension_error_while_uploading_document'));
                    return false;
                }
                if ($file_size > 2097152) {
                    $this->form_validation->set_message('handle_doc_upload', $this->lang->line('file_size_shoud_be_less_than') . "2MB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_doc_upload', $this->lang->line('error_while_uploading_document'));
                return false;
            }

            return true;
        }
        return true;
    }

    public function valid_payment_mode($mode)
    {
        if (!array_key_exists($mode, $this->config->item('payment_mode'))) {
            $this->form_validation->set_message('valid_payment_mode', 'Please select a valid payment mode.');
            return false;
        }
        return true;
    }

    public function valid_cheque_date($date)
    {
        $value = $this->customlib->dateFormatToYYYYMMDD($date);
        $parts = explode('-', (string)$value);
        if (count($parts) !== 3 || !checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) {
            $this->form_validation->set_message('valid_cheque_date', 'Please enter a valid cheque date.');
            return false;
        }
        return true;
    }

    public function validateCanUploadFile($str, $params_string)
    {
        return $this->saasvalidation->validateCanUploadFile($str, array_map('trim', explode(',', $params_string)));
    }

    public function check_billid()
    {
        $billing_id = $this->input->post('bill_no', TRUE);
        $check      = $this->referral_payment_model->check_billid($billing_id);
        if ($check > 0) {
            $this->form_validation->set_message('check_billid', $this->lang->line('referral_payment_already_generated_for_this_bill_no'));
            return false;
        } else {
            return true;
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('referral_payment', 'can_delete')) {
            access_denied();
        }
        if (!empty($id)) {
            $payment = $this->referral_payment_model->get($id);
            $deleted = $this->referral_payment_model->delete($id);
            if ($deleted === false) {
                echo json_encode(array('status' => 0, 'msg' => 'Could not delete payment.'));
                return;
            }
            if (!empty($payment['attachment'])) {
                $size = $this->media_storage->getUploadedFileSize($payment['attachment'], 'uploads/payment_document');
                $this->media_storage->filedelete($payment['attachment'], 'uploads/payment_document');
                try {
                    $this->saasvalidation->deleteResouceQuota('storage', $size);
                } catch (Exception $e) {
                    log_message('error', 'Referral document quota cleanup failed: ' . $e->getMessage());
                }
            }
            echo json_encode(array("status" => 1, "msg" => $this->lang->line("delete_message")));
        }
    }

    public function get($id)
    {
        $data = $this->referral_payment_model->get($id);
        echo json_encode($data);
    }

    public function update()
    {
        $data = array();
        $this->form_validation->set_rules("commission_percentage", $this->lang->line('commission_percentage'), 'trim|required|xss_clean');
        $this->form_validation->set_rules("commission_amount", $this->lang->line('commission_amount'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $msg = array(
                "commission_percentage" => form_error('commission_percentage'),
                "commission_amount"     => form_error('commission_amount'),
            );
            $data = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $payment = array(
                "id"         => $this->input->post('paymentid', TRUE),
                "percentage" => $this->input->post('commission_percentage', TRUE),
                "amount"     => $this->input->post('commission_amount', TRUE),
            );

            $this->referral_payment_model->update($payment);
            $data = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($data);
    }

    public function getCommission()
    {
        $type       = $this->input->post("type", TRUE);
        $payee      = $this->input->post("payee", TRUE);
        $percentage = $this->referral_payment_model->get_commission($payee, $type);
		 
        echo $percentage;
    }

    public function getBillNo()
    {
        $referral_type = $this->input->post('type', TRUE);
        $patient_id    = $this->input->post('patient_id', TRUE);
        if ($referral_type == 1) {
            //opd
            $result = $this->referral_payment_model->get_opdBillNo($patient_id);
        } elseif ($referral_type == 2) {
            //ipd
            $result = $this->referral_payment_model->get_ipdBillNo($patient_id);
        } elseif ($referral_type == 3) {
            //pharmacy
            $result = $this->referral_payment_model->get_pharmacyBillNo($patient_id);
        } elseif ($referral_type == 4) {
            //pathology
            $result = $this->referral_payment_model->get_pathologyBillNo($patient_id);
        } elseif ($referral_type == 5) {
            //radiology
            $result = $this->referral_payment_model->get_radiologyBillNo($patient_id);
        } elseif ($referral_type == 6) {
            //blood_bank
            $result = $this->referral_payment_model->get_bloodbankBillNo($patient_id);
        } elseif ($referral_type == 7) {
            //ambulance
            $result = $this->referral_payment_model->get_ambulanceBillNo($patient_id);
        }
        echo json_encode($result);
    }

    public function getBillAmount()
    {
        $referral_type = $this->input->post('type', TRUE);
        $bill_no       = $this->input->post('bill_no', TRUE);
        if ($referral_type == 1) {
            //opd
            $result = $this->referral_payment_model->get_opdBillAmount($bill_no);

        } elseif ($referral_type == 2) {
            //ipd
            $result = $this->referral_payment_model->get_ipdBillAmount($bill_no);
        } elseif ($referral_type == 3) {
            //pharmacy
            $result = $this->referral_payment_model->get_pharmacyBillAmount($bill_no);
        } elseif ($referral_type == 4) {
            //pathology
            $result = $this->referral_payment_model->get_pathologyBillAmount($bill_no);
        } elseif ($referral_type == 5) {
            //radiology
            $result = $this->referral_payment_model->get_radiologyBillAmount($bill_no);
        } elseif ($referral_type == 6) {
            //blood_bank
            $result = $this->referral_payment_model->get_bloodbankBillAmount($bill_no);
        } elseif ($referral_type == 7) {
            //ambulance
            $result = $this->referral_payment_model->get_ambulanceBillAmount($bill_no);
        }

        echo json_encode($result);
    }

}
