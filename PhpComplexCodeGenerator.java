package generator;

import java.io.BufferedWriter;
import java.io.FileWriter;
import java.io.IOException;
import java.util.List;
import java.util.Map;

public class PhpComplexCodeGenerator {

    static class ComplexTableMapping {
        private String tableName;
        private String tableColumn;
        private String csvColumn;
    
        public ComplexTableMapping(String tableName, String tableColumn, String csvColumn) {
            this.tableName = tableName;
            this.tableColumn = tableColumn;
            this.csvColumn = csvColumn;
        }
    
        public String getTableName() {
            return tableName;
        }
    
        public String getTableColumn() {
            return tableColumn;
        }
    
        public String getCsvColumn() {
            return csvColumn;
        }
    
        public String getVariableName() {
            // Génère un nom de variable basé sur le nom de la table (en minuscule)
            return tableName.toLowerCase();
        }
    }
    

    public static void generatePhpImportCode(String modelName, String mainTable, Map<String, String> mainTableMappings, List<ComplexTableMapping> relatedTables) {
        try (BufferedWriter writer = new BufferedWriter(new FileWriter(modelName + ".php"))) {
            // Commence à écrire le code PHP
            writer.write("<?php\n");
            writer.write("class " + modelName + " extends CI_Model {\n\n");
            writer.write("\tpublic function __construct() {\n");
            writer.write("\t\t$this->load->database();\n");
            writer.write("\t\t$this->load->library('csvreader');\n");
            writer.write("\t}\n\n");
    
            writer.write("\tpublic function import_data($file_path) {\n");
            writer.write("\t\t$csv_data = $this->csvreader->parse_file($file_path);\n");
            writer.write("\t\tforeach ($csv_data as $row) {\n");
    
            // Génère le code pour les tables liées
            for (ComplexTableMapping relatedTable : relatedTables) {
                String[] csvColumns = relatedTable.getCsvColumn().split(",");
                if (csvColumns.length > 1) {
                    writer.write("\t\t\t$" + relatedTable.getVariableName() + "_id = $this->get_or_create_" + relatedTable.getVariableName() + "($row['" + csvColumns[0] + "'], $row['" + csvColumns[1] + "']);\n");
                } else {
                    writer.write("\t\t\t$" + relatedTable.getVariableName() + "_id = $this->get_or_create_" + relatedTable.getVariableName() + "($row['" + csvColumns[0] + "']);\n");
                }
            }
    
            // Génère le mappage des colonnes pour la table principale
            writer.write("\t\t\t$data = array(\n");
            for (Map.Entry<String, String> entry : mainTableMappings.entrySet()) {
                String csvColumn = entry.getKey();
                String tableColumn = entry.getValue();
                if (tableColumn.endsWith("_id")) {
                    String relatedVar = tableColumn.replace("_id", "");
                    writer.write("\t\t\t\t'" + tableColumn + "' => $" + relatedVar + "_id,\n");
                } else {
                    writer.write("\t\t\t\t'" + tableColumn + "' => $row['" + csvColumn + "'],\n");
                }
            }
            writer.write("\t\t\t);\n");
    
            writer.write("\t\t\t$this->db->insert('" + mainTable + "', $data);\n");
            writer.write("\t\t}\n");
            writer.write("\t}\n\n");
    
            // Génère les méthodes pour gérer les tables liées
            for (ComplexTableMapping relatedTable : relatedTables) {
                writer.write(generateHelperMethod(relatedTable));
            }
    
            writer.write("}\n");
        } catch (IOException e) {
            e.printStackTrace();
        }
    }
    
    private static String generateHelperMethod(ComplexTableMapping relatedTable) {
        StringBuilder method = new StringBuilder();
        String[] tableColumns = relatedTable.getTableColumn().split(",");
        String[] csvColumns = relatedTable.getCsvColumn().split(",");
    
        method.append("\tprivate function get_or_create_").append(relatedTable.getVariableName()).append("(");
        for (int i = 0; i < csvColumns.length; i++) {
            method.append("$").append(csvColumns[i].replace(" ", "_"));
            if (i < csvColumns.length - 1) {
                method.append(", ");
            }
        }
        method.append(") {\n");
    
        method.append("\t\t$query = $this->db->get_where('").append(relatedTable.getTableName()).append("', array(\n");
        for (int i = 0; i < tableColumns.length; i++) {
            method.append("\t\t\t'").append(tableColumns[i]).append("' => $").append(csvColumns[i].replace(" ", "_")).append(",\n");
        }
        method.append("\t\t));\n");
    
        method.append("\t\tif ($query->num_rows() > 0) {\n");
        method.append("\t\t\treturn $query->row()->id;\n");
        method.append("\t\t} else {\n");
        method.append("\t\t\t$this->db->insert('").append(relatedTable.getTableName()).append("', array(\n");
        for (int i = 0; i < tableColumns.length; i++) {
            method.append("\t\t\t'").append(tableColumns[i]).append("' => $").append(csvColumns[i].replace(" ", "_")).append(",\n");
        }
        method.append("\t\t));\n");
        method.append("\t\t\treturn $this->db->insert_id();\n");
        method.append("\t\t}\n");
        method.append("\t}\n\n");
    
        return method.toString();
    }
    
    public static void main(String[] args) {
        // Exemple d'utilisation
        Map<String, String> mainTableMappings = Map.of(
                "type_maison", "id_maison",
                "code_Travaux", "id_travaux",
                "unité", "id_unite",
                "quantite", "quantite",
                "duree_travaux", "duree"
        );

        List<ComplexTableMapping> relatedTables = List.of(
                new ComplexTableMapping("type_maison", "type_maison,description,surface", "type_maison,description,surface"),
                new ComplexTableMapping("code_travaux", "code_travaux,type_travaux,prix_Unitaire", "code_travaux,type_travaux,prix_unitaire"),
                new ComplexTableMapping("unite", "unite", "unité")
        );

        generatePhpImportCode("Import_Travaux_Model", "Travaux", mainTableMappings, relatedTables);
    }
}
