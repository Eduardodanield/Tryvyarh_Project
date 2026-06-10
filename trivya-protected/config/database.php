<?php

/**
 * Classe Database — Singleton PDO para Trivya RH
 *
 * Gerencia a conexão com o MySQL usando PDO com:
 *  - Padrão Singleton (uma conexão por ciclo de requisição)
 *  - Charset utf8mb4 (suporte completo a emojis e caracteres especiais)
 *  - Modo de erro por exceção (PDOException)
 *  - Prepared statements como padrão (proteção contra SQL Injection)
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

class Database
{
    /** @var Database|null Instância única da classe */
    private static ?Database $instancia = null;

    /** @var PDO Conexão PDO ativa */
    private PDO $pdo;

    /**
     * Construtor privado — impede instanciação direta.
     * Use Database::getInstance() para obter a conexão.
     */
    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        $opcoes = [
            // Lançar exceções em vez de retornar false/null em erros
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

            // Retornar arrays associativos por padrão
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            // Não emular prepared statements — usar os nativos do MySQL
            // Isso garante tipagem correta dos valores retornados
            PDO::ATTR_EMULATE_PREPARES   => false,

            // Reutilizar conexão persistente (melhora performance em servidores compartilhados)
            PDO::ATTR_PERSISTENT         => true,

            // Definir charset na conexão (redundante com o DSN, mas garante)
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $opcoes);
        } catch (PDOException $e) {
            // Em produção, não expor detalhes da conexão
            if (APP_DEBUG) {
                throw new RuntimeException("Falha na conexão com o banco de dados: " . $e->getMessage());
            }
            throw new RuntimeException("Serviço temporariamente indisponível. Tente novamente em instantes.");
        }
    }

    /**
     * Retorna a instância única (Singleton).
     * Cria a conexão na primeira chamada; reutiliza nas seguintes.
     */
    public static function getInstance(): Database
    {
        if (self::$instancia === null) {
            self::$instancia = new Database();
        }
        return self::$instancia;
    }

    /**
     * Retorna o objeto PDO bruto para uso direto.
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Prepara e executa uma query com parâmetros, retornando o PDOStatement.
     *
     * Exemplo:
     *   $stmt = Database::getInstance()->query(
     *       "SELECT * FROM admins WHERE email = :email",
     *       [':email' => $email]
     *   );
     *   $admin = $stmt->fetch();
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // Registrar o erro completo internamente
            error_log("[Database] Erro na query: " . $e->getMessage() . " | SQL: " . $sql);

            if (APP_DEBUG) {
                throw new RuntimeException("Erro no banco de dados: " . $e->getMessage());
            }
            throw new RuntimeException("Erro ao processar a operação. Tente novamente.");
        }
    }

    /**
     * Busca um único registro.
     * Retorna array associativo ou null se não encontrado.
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $resultado = $this->query($sql, $params)->fetch();
        return $resultado !== false ? $resultado : null;
    }

    /**
     * Busca todos os registros de uma query.
     * Retorna array (vazio se nenhum resultado).
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Executa INSERT/UPDATE/DELETE e retorna o número de linhas afetadas.
     */
    public function execute(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Retorna o ID do último registro inserido.
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Inicia uma transação.
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Confirma a transação atual.
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Desfaz a transação atual (rollback).
     */
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Impedir clonagem do Singleton.
     */
    private function __clone(): void {}

    /**
     * Impedir deserialização do Singleton.
     */
    public function __wakeup(): void
    {
        throw new RuntimeException("Singleton não pode ser deserializado.");
    }
}
