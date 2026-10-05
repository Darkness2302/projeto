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
            SELECT id, produto_id, produto_nome, quantidade, preco_unitario,
                   valor_total, data_pedido, hora_recebimento, nota_fiscal
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

    /**
     * Registra o pedido e, em seguida, GERA o número da nota fiscal
     * sozinho — não existe mais campo pra digitar isso à mão.
     * O número usa o próprio ID do pedido (NF-<ano>-<id com 6 dígitos>),
     * então não precisa de contador separado nem corre risco de duas
     * notas saírem com o mesmo número.
     *
     * $precoUnitario é o preço do produto NO MOMENTO do pedido — fica
     * gravado aqui pra a nota fiscal nunca mudar de valor se o preço
     * do produto for alterado depois no Estoque.
     */
    public function inserir(
        ?int $produtoId,
        string $produtoNome,
        int $quantidade,
        float $precoUnitario,
        string $dataPedido,
        ?string $horaRecebimento
    ): int {
        $valorTotal = round($precoUnitario * $quantidade, 2);

        $stmt = $this->conn->prepare("
            INSERT INTO pedido
                (produto_id, produto_nome, quantidade, preco_unitario, valor_total,
                 data_pedido, hora_recebimento, nota_fiscal)
            VALUES
                (:produto_id, :produto_nome, :quantidade, :preco_unitario, :valor_total,
                 :data_pedido, :hora_recebimento, NULL)
        ");
        $stmt->execute([
            ':produto_id'       => $produtoId,
            ':produto_nome'     => $produtoNome,
            ':quantidade'       => $quantidade,
            ':preco_unitario'   => $precoUnitario,
            ':valor_total'      => $valorTotal,
            ':data_pedido'      => $dataPedido,
            ':hora_recebimento' => $horaRecebimento
        ]);
        $id = (int)$this->conn->lastInsertId();

        $numeroNota = sprintf('NF-%s-%06d', date('Y', strtotime($dataPedido)), $id);
        $this->conn->prepare("UPDATE pedido SET nota_fiscal = :nf WHERE id = :id")
                    ->execute([':nf' => $numeroNota, ':id' => $id]);

        return $id;
    }

    public function deletar(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM pedido WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
