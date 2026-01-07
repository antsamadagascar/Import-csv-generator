<?php
class Csv_model extends CI_Model {

    public function __construct() {
        $this->load->database();
        $this->load->library('csvreader');
    }

    public function import_services($file_path) {
        $csv_data = $this->csvreader->parse_file($file_path);

        foreach ($csv_data as $row) {
            $data = array(
                'service_name' => $row['service'],
                'duration' =>  $row['duree']
            );

            $this->db->insert('Services', $data);
        }
    }

    public function import_data($file_path) {
        $csv_data = $this->csvreader->parse_file($file_path);

        foreach ($csv_data as $row) {
            // Insérer ou mettre à jour les types de voiture
            $car_type_id = $this->get_or_create_car_type($row['type voiture']);

            // Insérer ou mettre à jour les services
            $service_id = $this->get_service_id($row['type service']);

            // Insérer ou mettre à jour les clients
            $client_id = $this->get_or_create_client($row['voiture'], $car_type_id);

            // Insérer les travaux
            $data = array(
                'id_client' => $client_id,
                'date_rdv' => date('Y-m-d', strtotime($row['date rdv'])),
                'heure_rdv' => date('H:i:s', strtotime($row['heure rdv'])),
                'id_service' => $service_id,
                'montant' => $row['montant'],
                'date_paiement' => !empty($row['date paiement']) ? date('Y-m-d', strtotime($row['date paiement'])) : NULL
            );

            $this->db->insert('Travaux', $data);
        }
    }

    private function get_or_create_car_type($type_name) {
        $query = $this->db->get_where('CarTypes', array('type_name' => $type_name));
        if ($query->num_rows() > 0) {
            return $query->row()->id;
        } else {
            $this->db->insert('CarTypes', array('type_name' => $type_name));
            return $this->db->insert_id();
        }
    }

    private function get_service_id($service_name) {
        $query = $this->db->get_where('Services', array('service_name' => $service_name));
        return $query->row()->id;
    }

    private function get_or_create_client($car_number, $car_type_id) {
        $query = $this->db->get_where('Clients', array('car_number' => $car_number));
        if ($query->num_rows() > 0) {
            return $query->row()->id;
        } else {
            $this->db->insert('Clients', array('car_number' => $car_number, 'car_type_id' => $car_type_id));
            return $this->db->insert_id();
        }
    }

}
