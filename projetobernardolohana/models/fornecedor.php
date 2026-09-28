<?php
require_once __DIR__ . '/../config/db.php';

class Fornecedor
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Lista completa (para a tela de gestão de fornecedores).
     * Protegido com try/catch: se a tabela ainda não existir no banco
     * (antes de rodar a migração), retorna lista vazia em vez de
     * quebrar a página.
     */
    public function listarTodos(): array
    {
        try {
            return $this->conn->query("SELECT * FROM fornecedor ORDER BY id DESC")->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Lista só os ativos, usada para popular o <select> de fornecedor
     * no formulário de produto (produto_form.php).
     */
    public function listarAtivos(): array
    {
        try {
            $sql = "SELECT id, nome FROM fornecedor WHERE ativo = 1 ORDER BY nome";
            return $this->conn->query($sql)->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function buscarPorId(int $id): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM fornecedor WHERE id = :id");
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
        ?string $produtos,
        ?string $dataRecebimento,
        ?string $horaRecebimento
    ): int {
        $stmt = $this->conn->prepare("
            INSERT INTO fornecedor
                (nome, cpf, lugar, produtos, data_recebimento, hora_recebimento, ativo)
            VALUES
                (:nome, :cpf, :lugar, :produtos, :data_recebimento, :hora_recebimento, 1)
        ");
        $stmt->execute([
            ':nome'             => $nome,
            ':cpf'              => $cpf,
            ':lugar'            => $lugar,
            ':produtos'         => $produtos,
            ':data_recebimento' => $dataRecebimento,
            ':hora_recebimento' => $horaRecebimento
        ]);
        return (int)$this->conn->lastInsertId();
    }

    public function atualizar(
        int $id,
        string $nome,
        ?string $cpf,
        ?string $lugar,
        ?string $produtos,
        ?string $dataRecebimento,
        ?string $horaRecebimento
    ): void {
        $stmt = $this->conn->prepare("
            UPDATE fornecedor
            SET nome = :nome,
                cpf = :cpf,
                lugar = :lugar,
                produtos = :produtos,
                data_recebimento = :data_recebimento,
                hora_recebimento = :hora_recebimento
            WHERE id = :id
        ");
        $stmt->execute([
            ':id'               => $id,
            ':nome'             => $nome,
            ':cpf'              => $cpf,
            ':lugar'            => $lugar,
            ':produtos'         => $produtos,
            ':data_recebimento' => $dataRecebimento,
            ':hora_recebimento' => $horaRecebimento
        ]);
    }

    public function setAtivo(int $id, bool $ativo): void
    {
        $stmt = $this->conn->prepare("UPDATE fornecedor SET ativo = :ativo WHERE id = :id");
        $stmt->execute([
            ':id' => $id,
            ':ativo' => $ativo ? 1 : 0
        ]);
    }

    public function deletar(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM fornecedor WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
