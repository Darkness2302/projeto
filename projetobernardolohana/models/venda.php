<?php
require_once __DIR__ . '/../config/db.php';

/**
 * Histórico de pedidos (tabela `pedido`).
 * O nome do produto é gravado junto com o pedido, então o histórico
 * continua legível mesmo que o produto seja excluído depois.
 */
class Venda
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /** Todos os pedidos, do mais recente para o mais antigo. */
    public function listar(): array
    {
        $sql = "
            SELECT id, produto_id, produto_nome, quantidade,
                   data_pedido, hora_recebimento, nota_fiscal
            FROM pedido
            ORDER BY data_pedido DESC, id DESC
        ";
        return $this->conn->query($sql)->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM pedido WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch();
        return $r ?: null;
    }

    public function inserir(
        ?int $produtoId,
        string $produtoNome,
        int $quantidade,
        string $dataPedido,
        ?string $horaRecebimento,
        ?string $notaFiscal
    ): int {
        $stmt = $this->conn->prepare("
            INSERT INTO pedido
                (produto_id, produto_nome, quantidade, data_pedido, hora_recebimento, nota_fiscal)
            VALUES
                (:produto_id, :produto_nome, :quantidade, :data_pedido, :hora_recebimento, :nota_fiscal)
        ");
        $stmt->execute([
            ':produto_id'       => $produtoId,
            ':produto_nome'     => $produtoNome,
            ':quantidade'       => $quantidade,
            ':data_pedido'      => $dataPedido,
            ':hora_recebimento' => $horaRecebimento,
            ':nota_fiscal'      => $notaFiscal
        ]);
        return (int)$this->conn->lastInsertId();
    }

    public function deletar(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM pedido WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
