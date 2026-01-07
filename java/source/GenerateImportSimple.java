package generator;

public class GenerateImportSimple {

    public static String generateSimpleImport(String className, String tableName, String[][] columnMappings) {
        // Start building the PHP class
        StringBuilder phpCode = new StringBuilder();
        
        phpCode.append("<?php\n");
        phpCode.append("class ").append(className).append(" extends CI_Model {\n\n");
        
        // Constructor
        phpCode.append("    public function __construct() {\n");
        phpCode.append("        $this->load->database();\n");
        phpCode.append("        $this->load->library('csvreader');\n");
        phpCode.append("    }\n\n");
        
        // Import function
        phpCode.append("    public function import_data($file_path) {\n");
        phpCode.append("        $csv_data = $this->csvreader->parse_file($file_path);\n\n");
        phpCode.append("        foreach ($csv_data as $row) {\n");
        phpCode.append("            $data = array(\n");
        
        // Add each column mapping
        for (int i = 0; i < columnMappings.length; i++) {
            phpCode.append("                '").append(columnMappings[i][1]).append("' => $row['")
                    .append(columnMappings[i][0]).append("']");
            if (i < columnMappings.length - 1) {
                phpCode.append(",");
            }
            phpCode.append("\n");
        }
        
        phpCode.append("            );\n\n");
        phpCode.append("            $this->db->insert('").append(tableName).append("', $data);\n");
        phpCode.append("        }\n");
        phpCode.append("    }\n");
        
        phpCode.append("}\n");
        phpCode.append("?>");
        
        return phpCode.toString();
    }

    public static void main(String[] args) {
        // Example usage
        String className = "Import_Service_Model";
        String tableName = "Services";
        String[][] columnMappings = {
            {"service", "service_name"},
            {"duree", "duration"}
        };

        String generatedCode = generateSimpleImport(className, tableName, columnMappings);
        System.out.println(generatedCode);
    }
}
