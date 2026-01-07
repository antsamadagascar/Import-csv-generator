package generator;

import java.util.HashMap;
import java.util.Map;

public class PhpComplexImportGenerator {

    public static String generateComplexImport(String className, String tableName, String[][] tableMappings, String[][] columnMappings) {
        StringBuilder sb = new StringBuilder();
        String primaryKeyColumn = getPrimaryKeyColumn(tableName, tableMappings);

        // Header and constructor
        sb.append("<?php\n");
        sb.append("class ").append(className).append(" extends CI_Model {\n\n");
        sb.append("    public function __construct() {\n");
        sb.append("        $this->load->database();\n");
        sb.append("        $this->load->library('csvreader');\n");
        sb.append("    }\n\n");

        // Import function
        sb.append("    public function import_$tableName($file_path) {\n");
        sb.append("        $csv_data = $this->csvreader->parse_file($file_path);\n\n");
        sb.append("        foreach ($csv_data as $row) {\n");

        // Mapping data
        Map<String, String> createdIds = new HashMap<>();
        for (String[] mapping : columnMappings) {
            if (mapping.length < 3) {
                System.err.println("Column mapping length is incorrect for: " + String.join(", ", mapping));
                continue;
            }

            String csvColumnName = mapping[0];
            String tableColumnName = mapping[1];
            String type = mapping[2];

            if (isForeignKey(type)) {
                String functionName = type.equals("foreign_key_create") ? "get_or_create_" : "get_";
                String tableNameForFunction = getTableNameFromColumn(tableColumnName, tableMappings);

                if (type.equals("foreign_key_create")) {
                    String idVar = "$" + tableColumnName;
                    sb.append("            ").append(idVar)
                      .append(" = $this->").append(functionName).append(tableNameForFunction)
                      .append("($row['").append(csvColumnName).append("']);\n");
                    createdIds.put(tableColumnName, idVar);
                } else if (type.equals("foreign_key_get")) {
                    String idVar = "$" + tableColumnName;
                    sb.append("            ").append(idVar)
                      .append(" = $this->").append(functionName).append(tableNameForFunction).append("_id($row['")
                      .append(csvColumnName).append("']);\n");
                    createdIds.put(tableColumnName, idVar);
                }
            }
        }

        // Data insertion
        sb.append("            $data = array(\n");
        for (String[] mapping : columnMappings) {
            if (mapping.length < 3) {
                System.err.println("Column mapping length is incorrect for: " + String.join(", ", mapping));
                continue;
            }

            String csvColumnName = mapping[0];
            String tableColumnName = mapping[1];
            String type = mapping[2];

            if (isForeignKey(type)) {
                sb.append("                '").append(tableColumnName).append("' => ")
                  .append(createdIds.get(tableColumnName)).append(",\n");
            } else if (type.equals("date")) {
                sb.append("                '").append(tableColumnName).append("' => date('Y-m-d', strtotime($row['")
                  .append(csvColumnName).append("'])),\n");
            } else if (type.equals("time")) {
                sb.append("                '").append(tableColumnName).append("' => date('H:i:s', strtotime($row['")
                  .append(csvColumnName).append("'])),\n");
            } else if (type.equals("date_optional")) {
                sb.append("                '").append(tableColumnName).append("' => !empty($row['")
                  .append(csvColumnName).append("']) ? date('Y-m-d', strtotime($row['")
                  .append(csvColumnName).append("'])) : NULL,\n");
            } else {
                sb.append("                '").append(tableColumnName).append("' => $row['")
                  .append(csvColumnName).append("'],\n");
            }
        }
        sb.append("            );\n\n");

        sb.append("            $this->db->insert('").append(tableName).append("', $data);\n");
        sb.append("        }\n");
        sb.append("    }\n\n");

        // Helper functions
        for (String[] mapping : columnMappings) {
            if (mapping.length < 3) {
                System.err.println("Column mapping length is incorrect for: " + String.join(", ", mapping));
                continue;
            }

            String tableColumnName = mapping[1];
            String type = mapping[2];

            if (isForeignKey(type)) {
                String friendlyName = getTableNameFromColumn(tableColumnName, tableMappings);

                if (type.equals("foreign_key_create")) {
                    sb.append("    private function get_or_create_").append(friendlyName)
                      .append("($value) {\n");
                    sb.append("        $query = $this->db->get_where('").append(friendlyName)
                      .append("', array('").append(tableColumnName).append("' => $value));\n");
                    sb.append("        if ($query->num_rows() > 0) {\n");
                    sb.append("            return $query->row()->id;\n");
                    sb.append("        } else {\n");
                    sb.append("            $this->db->insert('").append(friendlyName)
                      .append("', array('").append(tableColumnName).append("' => $value));\n");
                    sb.append("            return $this->db->insert_id();\n");
                    sb.append("        }\n");
                    sb.append("    }\n\n");
                } else if (type.equals("foreign_key_get")) {
                    sb.append("    private function get_").append(friendlyName).append("_id($value) {\n");
                    sb.append("        $query = $this->db->get_where('").append(friendlyName)
                      .append("', array('").append(tableColumnName).append("' => $value));\n");
                    sb.append("        return $query->row()->id;\n");
                    sb.append("    }\n\n");
                }
            }
        }

        sb.append("}\n?>");

        return sb.toString();
    }

    private static String getPrimaryKeyColumn(String tableName, String[][] tableMappings) {
        // Find the primary key column for the given table
        for (String[] mapping : tableMappings) {
            if (mapping.length > 1 && mapping[0].equals(tableName)) {
                return mapping[1];
            }
        }
        return "id"; // Default primary key column
    }

    private static String getTableNameFromColumn(String columnName, String[][] tableMappings) {
        // Map column name to table name
        for (String[] mapping : tableMappings) {
            if (mapping.length > 1 && mapping[1].equals(columnName)) {
                return mapping[0];
            }
        }
        return columnName; // Default to column name if not found
    }

    private static boolean isForeignKey(String type) {
        // Check if the type is a foreign key based on its mapping type
        return type.startsWith("foreign_key");
    }

    public static void main(String[] args) {
         String[][] tableMappings = {
            {"CarTypes", "type_name"},
            {"Clients", "id_client"},
            {"Services", "service_name"},
        };
    
        // Define column mappings: {csv_column_name, table_column_name, type}
         String[][] columnMappings = {
            {"type voiture", "id_client", "foreign_key_create"},
            {"date rdv", "date_rdv", "date"},
            {"heure rdv", "heure_rdv", "time"},
            {"type service", "id_service", "foreign_key_get"},
            {"montant", "montant", "simple"},
            {"date paiement", "date_paiement", "date_optional"}
        };


    
        String generatedCode = generateComplexImport("Import_Travaux_Model", "Travaux", tableMappings, columnMappings);
        System.out.println(generatedCode);
    }
}
