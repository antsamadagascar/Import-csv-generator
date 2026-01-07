package phpCrud;

import connection.MysqlConnection;

import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.io.PrintWriter;
import java.sql.Connection;
import java.sql.DatabaseMetaData;
import java.sql.ResultSet;
import java.sql.SQLException;

public class PhpGeneratePage {

    public static void main(String[] args) {
        // Définir les paramètres de test
        String[] tables = {"Clients", "Travaux","Services"};
        String outputDir = "../../application/views";

        // Vérifier la présence du répertoire et le créer si nécessaire
        File dir = new File(outputDir);
        if (!dir.exists()) {
            dir.mkdirs();
        }

        // Appeler la méthode de génération des vues
        generateViewsForTables(tables, outputDir);

        // Vérifier la création des fichiers
        verifyGeneratedFiles(outputDir, tables);
    }

    public static void generateViewsForTables(String[] tables, String outputDir) {
        Connection conn = null;
        try {
            conn = MysqlConnection.getConnection();
            for (String tableName : tables) {
                String tableDir = outputDir + "/" + tableName.toLowerCase();
                new File(tableDir).mkdirs();

                String[] columns = getColumns(conn, tableName);
                String controllerName = toCamelCase(tableName) + "Controller";
                
                generateViewFile(tableName, columns, "list.php", tableDir, controllerName);
                generateViewFile(tableName, columns, "create.php", tableDir, controllerName);
                generateViewFile(tableName, columns, "edit.php", tableDir, controllerName);
            }
        } catch (SQLException e) {
            e.printStackTrace();
        } finally {
            MysqlConnection.closeConnection(conn);
        }
    }

    private static void generateViewFile(String tableName, String[] columns, String fileName, String outputDir, String controllerName) {
        try (PrintWriter writer = new PrintWriter(new FileWriter(outputDir + "/" + fileName))) {
            writer.println("<!DOCTYPE html>");
            writer.println("<html lang='en'>");
            writer.println("<head>");
            writer.println("    <meta charset='UTF-8'>");
            writer.println("    <meta name='viewport' content='width=device-width, initial-scale=1.0'>");
            writer.println("    <title>" + capitalizeFirstLetter(fileName.replace(".php", "")) + " - " + capitalizeFirstLetter(tableName) + "</title>");
            writer.println("</head>");
            writer.println("<body>");
            
            if (fileName.equals("list.php")) {
                writer.printf("<h1>Liste des %ss</h1>%n", tableName.toLowerCase());
                writer.println("<table border= '1'>");
                writer.println("    <tr>");
                for (String column : columns) {
                    String columnName = column.split(":")[0];
                    writer.printf("        <th>%s</th>%n", capitalizeFirstLetter(columnName));
                }
                writer.println("        <th>Actions</th>");
                writer.println("    </tr>");
                writer.println("    <?php foreach ($" + tableName.toLowerCase() + " as $item): ?>");
                writer.println("    <tr>");
                for (String column : columns) {
                    String columnName = column.split(":")[0];
                    writer.printf("        <td><?php echo $item->%s; ?></td>%n", columnName);
                }
                writer.println("        <td>");
                writer.println("            <a href='<?php echo site_url(\"" + controllerName.toLowerCase() + "/edit/\" . $item->id); ?>'>Edit</a> |");
                writer.println("            <a href='<?php echo site_url(\"" + controllerName.toLowerCase() + "/delete/\" . $item->id); ?>'>Delete</a>");
                writer.println("        </td>");
                writer.println("    </tr>");
                writer.println("    <?php endforeach; ?>");
                
                writer.println("</table>");
                writer.printf("<p><a href='<?php echo site_url(\"" + controllerName.toLowerCase() + "/create/\"); ?>'>Ajouter un nouveau  %ss</p>%n",tableName.toLowerCase());
                
                writer.println("</body>");
                writer.println("</html>");

            } else if (fileName.equals("create.php")) {
                writer.printf("<h1>Ajouter un %s</h1>%n", tableName.toLowerCase());
                writer.println("<form action='<?php echo site_url(\"" + controllerName.toLowerCase() + "/create\"); ?>' method='post'>");
                // Generate inputs for columns except the first one
                for (int i = 1; i < columns.length; i++) {
                    String column = columns[i];
                    String columnName = column.split(":")[0];
                    String columnType = column.split(":")[1];
                    writer.printf("    <label for='%s'>%s:</label>%n", columnName, capitalizeFirstLetter(columnName));
                    writer.printf("    <input type='%s' id='%s' name='%s' required>%n",
                                   getHtmlInputType(columnType), columnName, columnName);
                }
                writer.println("    <button type='submit'>Submit</button>");
                writer.println("</form>");
                
            } else if (fileName.equals("edit.php")) {
                writer.printf("<h1>Modifier un %s</h1>%n", tableName.toLowerCase());
                writer.println("<form action='<?php echo site_url(\"" + controllerName.toLowerCase() + "/edit/\" . $item->id); ?>' method='post'>");
                writer.println("    <input type='hidden' name='id' value='<?php echo $item->id; ?>'>");
                // Generate inputs for columns except the first one
                for (int i = 1; i < columns.length; i++) {
                    String column = columns[i];
                    String columnName = column.split(":")[0];
                    String columnType = column.split(":")[1];
                    writer.printf("    <label for='%s'>%s:</label>%n", columnName, capitalizeFirstLetter(columnName));
                    writer.printf("    <input type='%s' id='%s' name='%s' value='<?php echo $item->%s; ?>' required>%n",
                                   getHtmlInputType(columnType), columnName, columnName, columnName);
                }
                writer.println("    <button type='submit'>Update</button>");
                writer.println("</form>");
            }
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    private static String getHtmlInputType(String columnType) {
        switch (columnType) {
            case "int":
                return "number";
            case "decimal":
                return "number";
            case "varchar":
                return "text";
            case "text":
                return "text";
            case "date":
                return "date";
            case "time":
                return "date-time-local";
            default:
                return "text";
        }
    }

    private static String capitalizeFirstLetter(String input) {
        return input.substring(0, 1).toUpperCase() + input.substring(1);
    }

    private static void verifyGeneratedFiles(String outputDir, String[] tables) {
        for (String tableName : tables) {
            File dir = new File(outputDir + "/" + tableName.toLowerCase());
            if (!dir.exists() || !dir.isDirectory()) {
                System.out.println("Le répertoire pour " + tableName + " n'a pas été créé correctement.");
                continue;
            }

            String[] expectedFiles = {"list.php", "create.php", "edit.php"};

            for (String fileName : expectedFiles) {
                File file = new File(dir, fileName);
                if (file.exists() && file.isFile()) {
                    System.out.println(fileName + " pour " + tableName + " a été créé avec succès.");
                } else {
                    System.out.println(fileName + " pour " + tableName + " n'a pas été trouvé.");
                }
            }
        }
    }

    private static String[] getColumns(Connection conn, String tableName) throws SQLException {
        DatabaseMetaData metaData = conn.getMetaData();
        ResultSet columns = metaData.getColumns(null, null, tableName, null);

        StringBuilder columnBuilder = new StringBuilder();
        while (columns.next()) {
            String columnName = columns.getString("COLUMN_NAME");
            String columnType = columns.getString("TYPE_NAME").toLowerCase();
            columnBuilder.append(columnName).append(":").append(columnType).append(",");
        }

        // Remove the trailing comma
        if (columnBuilder.length() > 0) {
            columnBuilder.setLength(columnBuilder.length() - 1);
        }

        return columnBuilder.toString().split(",");
    }

    private static String toCamelCase(String tableName) {
        String[] parts = tableName.toLowerCase().split("_");
        StringBuilder camelCaseString = new StringBuilder(parts[0]);
        
        for (int i = 1; i < parts.length; i++) {
            camelCaseString.append(parts[i].substring(0, 1).toUpperCase())
                           .append(parts[i].substring(1));
        }
        
        return camelCaseString.toString();
    }
}
