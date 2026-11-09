<?php
require_once __DIR__ . '/../config/db.php';

/**
 * Opções de variação de um produto (ex.: P, M, G), cada uma com preço próprio.
 * O campo produto.variacao continua sendo o nome do grupo (ex.: "Tamanho").
 */
class ProdutoVariacao
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function listarDoProduto(int $produtoId): array
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM produto_variacao WHERE produto_id = :p ORDER BY nome");
            $stmt->execute([':p' => $produtoId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return []; // tabela ainda não migrada
        }
    }

    /** Opções ativas de produtos ativos (para o formulário de pedidos). */
    public function listarAtivas(): array
    {
        try {
            return $this->conn->query("
                SELECT v.id, v.produto_id, v.nome, v.preco
                FROM produto_variacao v
                INNER JOIN produto p ON p.id = v.produto_id
                WHERE v.ativo = 1 AND p.ativo = 1
                ORDER BY v.nome
            ")->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /** Confere se a opção pertence ao produto e está ativa. */
    public function buscarDoProduto(int $id, int $produtoId): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM produto_variacao WHERE id = :id AND produto_id = :p AND ativo = 1");
        $stmt->execute([':id' => $id, ':p' => $produtoId]);
        $r = $stmt->fetch();
        return $r ?: null;
    }

    public function inserir(int $produtoId, string $nome, float $preco): bool
    {
        $stmt = $this->conn->prepare("INSERT INTO produto_variacao (produto_id, nome, preco, ativo) VALUES (:p, :nome, :preco, 1)");
        return $stmt->execute([':p' => $produtoId, ':nome' => $nome, ':preco' => $preco]);
    }

    public function deletar(int $id, int $produtoId): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM produto_variacao WHERE id = :id AND produto_id = :p");
        return $stmt->execute([':id' => $id, ':p' => $produtoId]);
    }
}
