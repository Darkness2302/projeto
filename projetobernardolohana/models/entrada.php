<?php
require_once __DIR__ . '/../config/db.php';

/**
 * Histórico de mercadoria (tabela entrada_mercadoria).
 * Cada linha é uma movimentação do estoque:
 *   tipo 'entrada' -> fornecedor entregou o produto (soma no estoque)
 *   tipo 'venda'   -> pedido de cliente ou garçom (baixa no estoque)
 */
class Entrada
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Grava uma linha de histórico. Deve ser chamada DENTRO da mesma
     * transação da movimentação, para o histórico nunca ficar sem o estoque
     * (nem o contrário).
     */
    public static function registrar(PDO $pdo, array $m): void
    {
        $qtd = (int)$m['quantidade'];
        $vu  = (float)$m['valor_unitario'];

        $stmt = $pdo->prepare("
            INSERT INTO entrada_mercadoria
                (data, status, valor_total, tipo, produto_id, produto_nome, fornecedor_id,
                 pedido_id, usuario_id, quantidade, valor_unitario, estoque_antes, estoque_depois)
            VALUES
                (:data, 'confirmado', :valor_total, :tipo, :produto_id, :produto_nome, :fornecedor_id,
                 :pedido_id, :usuario_id, :quantidade, :valor_unitario, :antes, :depois)
        ");
        $stmt->execute([
            ':data'           => $m['data'],
            ':valor_total'    => round($qtd * $vu, 2),
            ':tipo'           => $m['tipo'],
            ':produto_id'     => $m['produto_id'] ?? null,
            ':produto_nome'   => $m['produto_nome'],
            ':fornecedor_id'  => $m['fornecedor_id'] ?? null,
            ':pedido_id'      => $m['pedido_id'] ?? null,
            ':usuario_id'     => $m['usuario_id'] ?? null,
            ':quantidade'     => $qtd,
            ':valor_unitario' => $vu,
            ':antes'          => $m['estoque_antes'],
            ':depois'         => $m['estoque_depois'],
        ]);
    }

    /** Histórico do mais recente para o mais antigo. */
    public function listar(int $limite = 100): array
    {
        $stmt = $this->conn->prepare("
            SELECT e.id, e.tipo, e.data, e.criado_em, e.produto_nome, e.quantidade,
                   e.valor_unitario, e.valor_total, e.estoque_antes, e.estoque_depois,
                   e.pedido_id, f.nome AS fornecedor_nome, u.nome AS usuario_nome
            FROM entrada_mercadoria e
            LEFT JOIN fornecedor f ON f.id = e.fornecedor_id
            LEFT JOIN usuario u ON u.id = e.usuario_id
            ORDER BY e.criado_em DESC, e.id DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Movimentos de mercadoria, do mais recente para o mais antigo.
     * $tipo = 'entrada' | 'venda' | null (todos).
     * Em entradas, "quem enviou" é o fornecedor. Em vendas, "quem comprou" é o
     * cliente do pedido; se o pedido foi feito pelo garçom, é o garçom que registrou.
     */
    public function listarMovimentos(?string $tipo = null, int $limite = 300): array
    {
        $filtro = $tipo ? "WHERE e.tipo = :tipo" : "";
        $stmt = $this->conn->prepare("
            SELECT e.id, e.tipo, e.data, e.criado_em, e.produto_id, e.produto_nome,
                   e.quantidade, e.valor_unitario, e.valor_total,
                   e.estoque_antes, e.estoque_depois, e.pedido_id, pe.nota_fiscal,
                   f.nome AS fornecedor_nome,
                   COALESCE(uc.nome, ur.nome) AS pessoa,
                   CASE WHEN e.tipo = 'entrada' THEN 'Fornecedor' ELSE 'Cliente / garçom' END AS papel
            FROM entrada_mercadoria e
            LEFT JOIN fornecedor f ON f.id = e.fornecedor_id
            LEFT JOIN usuario ur ON ur.id = e.usuario_id
            LEFT JOIN pedido pe ON pe.id = e.pedido_id
            LEFT JOIN usuario uc ON uc.id = pe.usuario_id
            {$filtro}
            ORDER BY e.criado_em DESC, e.id DESC
            LIMIT :lim
        ");
        if ($tipo) $stmt->bindValue(':tipo', $tipo);
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Resumo por produto: quanto entrou, quanto saiu e o estoque atual. */
    public function resumoPorProduto(): array
    {
        return $this->conn->query("
            SELECT p.id, p.nome, p.estoque_qtd,
                   COALESCE(SUM(CASE WHEN e.tipo = 'entrada' THEN e.quantidade END), 0) AS total_entrada,
                   COALESCE(SUM(CASE WHEN e.tipo = 'venda'   THEN e.quantidade END), 0) AS total_saida,
                   MAX(CASE WHEN e.tipo = 'entrada' THEN e.criado_em END) AS ultima_entrada
            FROM produto p
            LEFT JOIN entrada_mercadoria e ON e.produto_id = p.id
            GROUP BY p.id, p.nome, p.estoque_qtd
            ORDER BY p.nome
        ")->fetchAll();
    }

    /** Entrada do fornecedor: soma no estoque e grava no histórico, numa transação. */
    public function registrarEntrada(
        int $produtoId,
        ?int $fornecedorId,
        int $quantidade,
        string $data,
        int $usuarioId
    ): void {
        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare("SELECT nome, preco, estoque_qtd FROM produto WHERE id = :id FOR UPDATE");
            $stmt->execute([':id' => $produtoId]);
            $prod = $stmt->fetch();
            if (!$prod) {
                throw new RuntimeException('Produto não encontrado.');
            }

            $antes  = (int)$prod['estoque_qtd'];
            $depois = $antes + $quantidade;
            $this->conn->prepare("UPDATE produto SET estoque_qtd = :d WHERE id = :id")
                       ->execute([':d' => $depois, ':id' => $produtoId]);

            self::registrar($this->conn, [
                'data'            => $data,
                'tipo'            => 'entrada',
                'produto_id'      => $produtoId,
                'produto_nome'    => $prod['nome'],
                'fornecedor_id'   => $fornecedorId,
                'usuario_id'      => $usuarioId,
                'quantidade'      => $quantidade,
                'valor_unitario'  => (float)$prod['preco'],
                'estoque_antes'   => $antes,
                'estoque_depois'  => $depois,
            ]);

            $this->conn->commit();
        } catch (Throwable $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
