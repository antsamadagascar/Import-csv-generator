<?php
class CsvModel extends CI_Model {

	public function __construct() {
		$this->load->database();
		$this->load->library('csvreader');
	}

	public function import_data($file_path) {
		$csv_data = $this->csvreader->parse_file($file_path);
		foreach ($csv_data as $row) {
			$type_maison_id = $this->get_or_create_type_maison($row['type_maison'], $row['description']);
			$code_travaux_id = $this->get_or_create_code_travaux($row['code_travaux'], $row['type_travaux']);
			$unite_id = $this->get_or_create_unite($row['unité']);
			$data = array(
				'id_travaux' => $code_travaux_id ,
				'duree' => $row['duree_travaux'],
				'quantite' => $row['quantite'],
				'id_maison' => $type_maison_id,
				'id_unite' => $unite_id,
			);
			$this->db->insert('Travaux', $data);
		}
	}

	private function get_or_create_type_maison($type_maison, $description, $surface) {
		$query = $this->db->get_where('type_maison', array(
			'type_maison' => $type_maison,
			'description' => $description,
			'surface' => $surface,
		));
		if ($query->num_rows() > 0) {
			return $query->row()->id;
		} else {
			$this->db->insert('type_maison', array(
			'type_maison' => $type_maison,
			'description' => $description,
			'surface' => $surface,
		));
			return $this->db->insert_id();
		}
	}

	private function get_or_create_code_travaux($code_travaux, $type_travaux, $prix_unitaire) {
		$query = $this->db->get_where('code_travaux', array(
			'code_travaux' => $code_travaux,
			'type_travaux' => $type_travaux,
			'prix_Unitaire' => $prix_unitaire,
		));
		if ($query->num_rows() > 0) {
			return $query->row()->id;
		} else {
			$this->db->insert('code_travaux', array(
			'code_travaux' => $code_travaux,
			'type_travaux' => $type_travaux,
			'prix_Unitaire' => $prix_unitaire,
		));
			return $this->db->insert_id();
		}
	}

	private function get_or_create_unite($unité) {
		$query = $this->db->get_where('unite', array(
			'unite' => $unité,
		));
		if ($query->num_rows() > 0) {
			return $query->row()->id;
		} else {
			$this->db->insert('unite', array(
			'unite' => $unité,
		));
			return $this->db->insert_id();
		}
	}

}
