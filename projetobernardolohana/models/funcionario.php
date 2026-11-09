<?php
require_once __DIR__ . '/../config/db.php';

class Funcionario
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Lista completa (para a tela de gestão de funcionarioes).
     * Protegido com try/catch: se a tabela ainda não existir no banco
     * (antes de rodar a migração), retorna lista vazia em vez de
     * quebrar a página.
     */
    public function listarTodos(): array
    {
        try {
            return $this->conn->query("
                SELECT f.*, c.nome AS cargo_nome
                FROM funcionario f
                LEFT JOIN cargo c ON c.id = f.cargo_id
                ORDER BY f.id DESC
            ")->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Lista só os ativos, usada para popular o <select> de funcionario
     * no formulário de produto (produto_form.php).
     */
    public function listarAtivos(): array
    {
        try {
            $sql = "SELECT id, nome FROM funcionario WHERE ativo = 1 ORDER BY nome";
            return $this->conn->query($sql)->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function buscarPorId(int $id): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM funcionario WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $r = $stmt->fetch();
            return $r ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function inserir(
        string $nome,
        ?string $cpf,
        ?string $lugar,
        ?int $cargoId,
        ?string $horaInicio,
        ?string $horaFim
    ): int {
        $stmt = $this->conn->prepare("
            INSERT INTO funcionario
                (nome, cpf, lugar, cargo_id, hora_inicio, hora_fim, ativo)
            VALUES
                (:nome, :cpf, :lugar, :cargo_id, :hora_inicio, :hora_fim, 1)
        ");
        $stmt->execute([
            ':nome'             => $nome,
            ':cpf'              => $cpf,
            ':lugar'            => $lugar,
            ':cargo_id'         => $cargoId,
            ':hora_inicio'      => $horaInicio,
            ':hora_fim'         => $horaFim
        ]);
        return (int)$this->conn->lastInsertId();
    }

    public function atualizar(
        int $id,
        string $nome,
        ?string $cpf,
        ?string $lugar,
        ?int $cargoId,
        ?string $horaInicio,
        ?string $horaFim
    ): void {
        $stmt = $this->conn->prepare("
            UPDATE funcionario
            SET nome = :nome,
                cpf = :cpf,
                lugar = :lugar,
                cargo_id = :cargo_id,
                hora_inicio = :hora_inicio,
                hora_fim = :hora_fim
            WHERE id = :id
        ");
        $stmt->execute([
            ':id'               => $id,
            ':nome'             => $nome,
            ':cpf'              => $cpf,
            ':lugar'            => $lugar,
            ':cargo_id'         => $cargoId,
            ':hora_inicio'      => $horaInicio,
            ':hora_fim'         => $horaFim
        ]);
    }

    public function setAtivo(int $id, bool $ativo): void
    {
        $stmt = $this->conn->prepare("UPDATE funcionario SET ativo = :ativo WHERE id = :id");
        $stmt->execute([
            ':id' => $id,
            ':ativo' => $ativo ? 1 : 0
        ]);
    }

    public function deletar(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM funcionario WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
