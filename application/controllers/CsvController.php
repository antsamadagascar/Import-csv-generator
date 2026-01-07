<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CsvController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('CsvModel');  // Chargez votre modèle CSV
        $this->load->library('upload');  // Chargez la bibliothèque d'upload
    }

    public function import() {
        $config['upload_path']   = './uploads/';
        $config['allowed_types'] = 'csv';
        $config['max_size']      = 100000; // en Ko

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('csv_file')) {
            $error = array('error' => $this->upload->display_errors());
            $this->load->view('upload_csv', $error);
        } else {
            $file_data = $this->upload->data();
            $file_path = $file_data['full_path'];

          // $this->CsvModel->import_services($file_path);
           $this->CsvModel->import_data($file_path);

            // Redirige ou affiche un message de succès
            $this->load->view('upload_success');
        }
    }
}
