<?php

namespace App\Service;

use PDO;
use PDOStatement;

class Database
{
    private string $database;

    private string $user;

    private string $password;

    private string $driver;

    private string $host;

    private int $port;

    private array $opts = [];

    // Leave the following properties empty !!
    private string $table = '';

    private string|array $cols = '';

    private PDO $connection;

    private string $query = '';

    private array $params = [];

    public function __construct(
        string $database,
        string $user = 'root',
        string $password = '',
        string $driver = 'mysql',
        string $host = '127.0.0.1',
        int $port = 3306,
        array $opts = []
    ) {
        $this->driver = $driver;
        $this->port = $port;
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->opts = $opts;

        $this->initializeConnection();

        return $this->connection;
    }

    protected function initializeConnection(): void
    {
        $dsn = http_build_query([
            'host' => $this->host,
            'port' => $this->port,
            'dbname' => $this->database,
        ], arg_separator: ';');

        $opts = array_merge([
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 60 * 30,  // 30 minutes to timeout
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ], $this->opts);

        $pdo = new PDO(
            "{$this->driver}:{$dsn};",
            $this->user,
            $this->password,
            $opts
        );

        $this->connection = $pdo;
    }

    /**
     * Perform SQL query
     */
    public function query(string $query, array $params = []): PDOStatement|false
    {
        $this->dd($query);
        $result = $this->connection->prepare($query);

        $result->execute($params);

        $this->logQuery($query, $params);

        return $result;
    }

    public function rawQuery(string $query): mixed
    {
        $result = $this->connection->query($query);

        $this->logQuery($query);

        return $result;
    }

    /**
     * Select table to query
     */
    public function table(string $tbl): static
    {
        $this->table = $tbl;

        return $this;
    }

    /**
     * Select columns to query
     */
    public function columns(string|array $cols): static
    {
        if (is_array($cols)) {
            $cols = $this->parseColumn($cols);
        }

        $this->cols = $cols;

        return $this;
    }

    /**
     * Perform select query to database
     */
    public function select(string $condition = '', array $bindings = []): ?PDOStatement
    {
        if (! $this->isTableSet() && $this->isColumnSet()) {
            return null;
        }

        $query = "SELECT {$this->cols} FROM {$this->table}";

        if ($condition) {
            $query .= " WHERE {$condition}";
        }

        return $this->query($query, $bindings);
    }

    /**
     * Perform insert query to database
     */
    public function insert(string|array $values, array $bindings = []): string|false|null
    {
        if (! $this->isTableSet() && $this->isColumnSet()) {
            return null;
        }

        if (is_array($values)) {
            $values = $this->parseColumn($values, true);
        }

        $query = "INSERT INTO {$this->table}({$this->cols}) VALUES ({$values})";

        $this->query($query, $bindings);

        return $this->connection->lastInsertId();
    }

    /**
     * Perform insert many query to database
     */
    public function insertMany(array $list): ?static
    {
        if (! $this->isTableSet() && $this->isColumnSet()) {
            return null;
        }

        $values = implode(', ', $this->parseInsertManyList($list));

        $query = "INSERT INTO {$this->table}({$this->cols}) VALUES {$values}";

        $this->connection->query($query);

        return $this;
    }

    /**
     * Perform update query to database
     */
    public function update(string $condition = '', array $bindings = []): ?static
    {
        if (! $this->isTableSet() && $this->isColumnSet()) {
            return null;
        }

        $cols = $this->cols;

        $query = "UPDATE {$this->table} SET {$cols}";

        if ($condition) {
            $query .= " WHERE {$condition}";
        }

        $this->query($query, $bindings);

        return $this;
    }

    /**
     * Perform delete query to database
     */
    public function delete(string $condition, array $bindings = []): ?static
    {
        if (! $this->isTableSet()) {
            return null;
        }

        $query = "DELETE FROM {$this->table} WHERE {$condition}";

        $this->query($query, $bindings);

        return $this;
    }

    /**
     * Dumps last query statement
     */
    public function toSql(bool $params = false): void
    {
        $this->dd('<strong>Query Statement: </strong><br>' . $this->query);

        if ($params) {
            $this->dd('<strong>Binding Parameters To Statement:</strong>');
            $this->dd($this->params);
        }
    }

    public function dd(mixed ...$data): void
    {
        echo '<pre>';
        \print_r(...$data);
        echo '</pre>';
    }

    /**
     * Format two-dimensional array to a single insert query for database
     */
    protected function parseInsertManyList(array $list): array
    {
        $temp = [];

        foreach ($list as $records) {
            $format = [];

            foreach ($records as $record) {
                $format[] = $this->parseColumn($record, true);
            }

            $temp[] = '(' . implode(', ', $format) . ')';
        }

        return $temp;
    }

    /**
     * A friendly UI to show if table is set
     */
    protected function isTableSet(): bool
    {
        if ($this->table === '') {
            echo '<strong>Table is not set</strong>';

            return false;
        }

        return true;
    }

    /**
     * A friendly UI to show if columns are set
     */
    protected function isColumnSet(): bool
    {
        if ($this->cols === '') {
            echo '<strong>Column is not set</strong>';

            return false;
        }

        return true;
    }

    /**
     * Prepare queries and params to be logged
     */
    protected function logQuery(string $query, array $params = []): void
    {
        $this->query = $query;
        $this->params = $params;
    }

    protected function parseColumn(mixed $target, bool $shouldSurroundWithQuote = false): string
    {
        if (is_int($target)) {
            return (string) $target;
        } elseif (is_bool($target)) {
            return (bool) $target;
        } elseif (is_string($target)) {
            if (! $shouldSurroundWithQuote || $target === '?') {
                return (string) $target;
            }

            return "'{$target}'";
        } elseif (is_array($target)) {
            $result = '';
            $len = count($target);
            $counter = 0;
            foreach ($target as $key => $val) {
                $counter++;
                // This method can be used when there is an array of columns,
                // or it could be column name as key and value as its value.
                // To identify which one we are dealing with, if the key is string
                // we can safely guess that we don't need to add (=) sign.
                if (! is_string($key)) {
                    $result .= $this->parseColumn($val, $shouldSurroundWithQuote);
                } else {
                    // If continue, parse each key/value that is compatible with sql query
                    $result .= $key . ' = ' . $this->parseColumn($val, $shouldSurroundWithQuote);
                }

                if ($len !== $counter) {
                    $result .= ', ';
                }
            }

            return $result;
        }

        $this->dd('This type of value isn\'t supported for parsing.');
    }
}
