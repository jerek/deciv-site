<?php

/**
 * Manages database access.
 */
class DB extends PDO {
    // ********************* //
    // ***** CONSTANTS ***** //
    // ********************* //

    /** @var string The name of the primary database. */
    private const DB_NAME = 'decivSite';

    // ********************** //
    // ***** PROPERTIES ***** //
    // ********************** //

    /** @var self The single instance of this class. */
    private static $pdo;

    // ********************* //
    // ***** FUNCTIONS ***** //
    // ********************* //

    // ====== //
    // PUBLIC //
    // ====== //

    /**
     * Returns the first field of the first row from a query.
     *
     * @param string  $sql
     * @param array   $params
     * @return mixed|null
     */
    public static function fetchField(string $sql, array $params = []) {
        $row = self::fetchRow($sql, $params);

        return $row ? current($row) : null;
    }

    /**
     * Returns the first row from a query.
     *
     * @param string  $sql
     * @param array   $params
     * @return array|null
     */
    public static function fetchRow(string $sql, array $params = []): ?array {
        return current(self::fetchRows($sql, $params)) ?: null;
    }

    /**
     * Returns all the rows from query.
     *
     * @param string  $sql
     * @param array   $params
     * @return array|null
     */
    public static function fetchRows(string $sql, array $params = []): ?array {
        return self::queryExec($sql, $params)->fetchAll();
    }

    /**
     * Executes the given query and returns its statement. If you're calling this externally, you can probably ignore
     * the returned statement.
     *
     * @param string  $sql
     * @param array   $params
     * @return PDOStatement
     * @throws Exception
     */
    public static function queryExec(string $sql, array $params = []): PDOStatement {
        $pdo = self::getConnection();

        $statement = $pdo->prepare($sql);
        if ($statement === false) {
            throw new Exception(
                "Could not prepare query: [{$sql}] Error info: " . json_encode($pdo->errorInfo())
            );
        }

        foreach ($params as $param => $value) {
            if (is_string($value)) {
                $type = self::PARAM_STR;
            } elseif (is_int($value)) {
                $type = self::PARAM_INT;
            } elseif (is_float($value)) {
                $type = self::PARAM_STR;
            } elseif (is_bool($value)) {
                $type = self::PARAM_INT;
                $value = (int)$value; // true => 1, false => 0
            } elseif (is_null($value)) {
                $type = self::PARAM_NULL;
            } else {
                $type = gettype($value);
                throw new Exception("Invalid query param type. [{$type}]");
            }

            $statement->bindValue($param, $value, $type);
        }

        $res = $statement->execute();
        if ($res === false) {
            throw new Exception("Failed to execute query. [{$sql}]");
        }

        return $statement;
    }

    // ======= //
    // PRIVATE //
    // ======= //

    /**
     * Fetch the PDO connection object.  Since we use the same connection for all DBs, we don't need a complex
     * maintenance infrastructure for this.
     *
     * @return self
     */
    private static function getConnection(): self {
        if (self::$pdo === null) {
            // Connect to the database.
            $dsn = 'mysql:host=' . getenv('DECIV_DB_HOST') . ';dbname=' . self::DB_NAME;
            $user = getenv('DECIV_DB_USERNAME');
            $pass = getenv('DECIV_DB_PASSWORD');
            self::$pdo = new self($dsn, $user, $pass);

            // Set the DB driver to use native mode, so fields are in appropriate PHP types.
            self::$pdo->setAttribute(self::ATTR_EMULATE_PREPARES, false);
            self::$pdo->setAttribute(self::ATTR_DEFAULT_FETCH_MODE, self::FETCH_ASSOC);

            // Always use UTF-8 with support for multi-byte characters.
            self::$pdo->query('SET NAMES utf8mb4');
        }

        return self::$pdo;
    }
}
