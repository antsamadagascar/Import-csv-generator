package connection;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public class MysqlConnection {
    private static final String DB_URL = "jdbc:mysql://localhost:3306/maisonTravaux";
    private static final String DB_USER = "root";
    private static final String DB_PASSWORD = "";

    public static Connection getConnection() {
        Connection connection = null;
        try {
            Class.forName("com.mysql.cj.jdbc.Driver");
            connection = DriverManager.getConnection(DB_URL, DB_USER, DB_PASSWORD);
            System.out.println("Connexion à MySQL réussie !");
        } catch (ClassNotFoundException e) {
            System.err.println("Erreur lors du chargement du pilote JDBC : " + e.getMessage());
        } catch (SQLException e) {
            System.err.println("Erreur lors de la connexion à la base de données MySQL : " + e.getMessage());
        }
        return connection;
    }
 
    public static void closeConnection(Connection connection) {
        try {
            if (connection != null) {
                connection.close();
                System.out.println("Connection closed successfully.");
            }
        } catch (SQLException e) {
            System.out.println("Error closing connection: " + e.getMessage());
        }
    }
    public static void main(String[] args) {
        Connection connection = getConnection();
        if (connection != null) {
            try {
                connection.close();
                System.out.println("Connexion à MySQL fermée avec succès !");
            } catch (SQLException e) {
                System.err.println("Erreur lors de la fermeture de la connexion : " + e.getMessage());
            }
        } else {
            System.out.println("La connexion à MySQL a échoué.");
        }
    }
}
