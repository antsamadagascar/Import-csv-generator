// Classe TableMapping avec constructeurs et méthodes de getter/setter

import java.util.Map;

public class TableMapping {
    private String tableName;
    private Map<String, String> columns; // columnName -> columnType
    private String primaryKey;

    public TableMapping(String tableName, Map<String, String> columns, String primaryKey) {
        this.tableName = tableName;
        this.columns = columns;
        this.primaryKey = primaryKey;
    }

    public String getTableName() {
        return tableName;
    }

    public Map<String, String> getColumns() {
        return columns;
    }

    public String getPrimaryKey() {
        return primaryKey;
    }
}

// Classe ForeignKeyMapping avec constructeur et méthodes de getter/setter
class ForeignKeyMapping {
    private String columnName;
    private String referencedTable;
    private String referencedColumn;

    public ForeignKeyMapping(String columnName, String referencedTable, String referencedColumn) {
        this.columnName = columnName;
        this.referencedTable = referencedTable;
        this.referencedColumn = referencedColumn;
    }

    public String getColumnName() {
        return columnName;
    }

    public String getReferencedTable() {
        return referencedTable;
    }

    public String getReferencedColumn() {
        return referencedColumn;
    }
}