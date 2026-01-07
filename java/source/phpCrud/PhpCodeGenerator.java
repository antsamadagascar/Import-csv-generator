package phpCrud;

import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.io.PrintWriter;

public class PhpCodeGenerator {

    public static void main(String[] args) {
        // Exemple de paramètres pour la génération de code
        String[] tableNames = {"Clients", "Travaux","Services"};
        
        // Définir les répertoires de sortie
        String modelOutputDir = "../../application/models";
        String controllerOutputDir = "../../application/controllers";
        
        // Vérifier la présence des répertoires et les créer si nécessaire
        createDirectoryIfNotExists(modelOutputDir);
        createDirectoryIfNotExists(controllerOutputDir);

        // Générer le code PHP pour chaque modèle et contrôleur
        for (String tableName : tableNames) {
            String className = tableName + "Model";
            String controllerName = tableName + "Controller";
            
            generatePhpModel(className, tableName, modelOutputDir + "/" + className + ".php");
            generatePhpController(controllerName, className, tableName, controllerOutputDir + "/" + controllerName + ".php");
        }
    }

    public static void generatePhpModel(String className, String tableName, String outputFile) {
        try (PrintWriter writer = new PrintWriter(new FileWriter(outputFile))) {
            writer.println("<?php");
            writer.println("defined('BASEPATH') OR exit('No direct script access allowed');");
            writer.println();
            writer.printf("class %s extends CI_Model {%n", className);
            writer.println();
            writer.println("    public function __construct() {");
            writer.println("        $this->load->database();");
            writer.println("    }");
            writer.println();
            
            // Générer la méthode d'insertion
            writer.printf("    public function insert_%s($data) {%n", tableName.toLowerCase());
            writer.println("        $this->db->insert('" + tableName + "', $data);");
            writer.println("    }");
            writer.println();
            
            // Générer la méthode de mise à jour
            writer.printf("    public function update_%s($id, $data) {%n", tableName.toLowerCase());
            writer.println("        $this->db->where('id', $id);");
            writer.println("        $this->db->update('" + tableName + "', $data);");
            writer.println("    }");
            writer.println();
            
            // Générer la méthode de suppression
            writer.printf("    public function delete_%s($id) {%n", tableName.toLowerCase());
            writer.println("        $this->db->where('id', $id);");
            writer.println("        $this->db->delete('" + tableName + "');");
            writer.println("    }");
            writer.println();
            
            // Générer la méthode de lecture
            writer.printf("    public function get_%s($id) {%n", tableName.toLowerCase());
            writer.println("        $query = $this->db->get_where('" + tableName + "', array('id' => $id));");
            writer.println("        return $query->row();");
            writer.println("    }");
            writer.println();

            // Générer la méthode de lecture de tous les enregistrements
            writer.printf("    public function get_all_%s() {%n", tableName.toLowerCase());
            writer.println("        $query = $this->db->get('" + tableName + "');");
            writer.println("        return $query->result();");
            writer.println("    }");
            writer.println();
        
            
            writer.println("}");
            writer.println("?>");
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    public static void generatePhpController(String controllerName, String modelName, String tableName, String outputFile) {
        try (PrintWriter writer = new PrintWriter(new FileWriter(outputFile))) {
            writer.println("<?php");
            writer.println("defined('BASEPATH') OR exit('No direct script access allowed');");
            writer.println();
            writer.printf("class %s extends CI_Controller {%n", controllerName);
            writer.println();
            writer.printf("    public function __construct() {%n");
            writer.println("        parent::__construct();");
            writer.printf("        $this->load->model('%s');%n", modelName);
            writer.println("    }");
            writer.println();
            
            // Méthode pour afficher la liste
            writer.printf("    public function index() {%n");
            writer.println("        $data['" + tableName.toLowerCase() + "'] = $this->" + modelName + "->get_all_" + tableName.toLowerCase() + "();");
            writer.println("        $this->load->view('" + tableName.toLowerCase() + "/list', $data);");
            writer.println("    }");
            writer.println();
            
            // Méthode pour afficher un formulaire d'ajout
            writer.printf("    public function create() {%n");
            writer.println("        if ($this->input->post()) {");
            writer.println("            $data = $this->input->post();");
            writer.println("            $this->" + modelName + "->insert_" + tableName.toLowerCase() + "($data);");
            writer.println("            redirect('" + controllerName + "');");
            writer.println("        }");
            writer.println("        $this->load->view('" + tableName.toLowerCase() + "/create');");
            writer.println("    }");
            writer.println();
            
            // Méthode pour afficher un formulaire de mise à jour
            writer.printf("    public function edit($id) {%n");
            writer.println("        if ($this->input->post()) {");
            writer.println("            $data = $this->input->post();");
            writer.println("            $this->" + modelName + "->update_" + tableName.toLowerCase() + "($id, $data);");
            writer.println("            redirect('" + controllerName + "');");
            writer.println("        }");
            writer.println("        $data['item'] = $this->" + modelName + "->get_" + tableName.toLowerCase() + "($id);");
            writer.println("        $this->load->view('" + tableName.toLowerCase() + "/edit', $data);");
            writer.println("    }");
            writer.println();
            
            // Méthode pour supprimer un enregistrement
            writer.printf("    public function delete($id) {%n");
            writer.println("        $this->" + modelName + "->delete_" + tableName.toLowerCase() + "($id);");
            writer.println("        redirect('" + controllerName + "');");
            writer.println("    }");
            writer.println();
            
            writer.println("}");
            writer.println("?>");
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    private static void createDirectoryIfNotExists(String dirPath) {
        File directory = new File(dirPath);
        if (!directory.exists()) {
            if (directory.mkdirs()) {
                System.out.println("Répertoire créé : " + dirPath);
            } else {
                System.out.println("Erreur lors de la création du répertoire : " + dirPath);
            }
        }
    }
}
