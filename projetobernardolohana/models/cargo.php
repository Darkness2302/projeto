<?php
require_once __DIR__ . '/../config/db.php';

class Cargo
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /** Todos os cargos (para a lista e o select do funcionário). */
    public function listarTodos(): array
    {
        try {
            return $this->conn->query("SELECT * FROM cargo ORDER BY nome")->fetchAll();
        } catch (Exception $e) {
            return []; // tabela ainda não migrada
        }
    }

    /** Insere um cargo. Retorna false se o nome já existe (UNIQUE). */
    public function inserir(string $nome): bool
    {
        try {
            $stmt = $this->conn->prepare("INSERT INTO cargo (nome) VALUES (:nome)");
            return $stmt->execute([':nome' => $nome]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /** Remove o cargo. Funcionários que o tinham ficam sem cargo (ON DELETE SET NULL). */
    public function deletar(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM cargo WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
