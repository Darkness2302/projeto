<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/entrada.php';

/**
 * Histórico de pedidos (tabela `pedido`).
 * O nome do produto é gravado junto com o pedido, então o histórico
 * continua legível mesmo que o produto seja excluído depois.
 */
/** Lançada quando não há estoque suficiente para a venda. A mensagem já vai pronta para o usuário. */
class EstoqueInsuficienteException extends RuntimeException {}

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
            SELECT id, produto_id, produto_nome, variacao, quantidade, preco_unitario,
                   valor_total, desconto, data_pedido, hora_recebimento, nota_fiscal
            FROM pedido
            ORDER BY data_pedido DESC, id DESC
        ";
        return $this->conn->query($sql)->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT p.*, u.nome AS cliente_nome
            FROM pedido p
            LEFT JOIN usuario u ON u.id = p.usuario_id
            WHERE p.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch();
        return $r ?: null;
    }

    /**
     * Confere e baixa o estoque de cada produto da venda. Deve ser chamado
     * dentro de uma transação: a linha do produto fica travada (FOR UPDATE),
     * então duas vendas simultâneas não conseguem vender a mesma unidade.
     * Estoque 0 (ou menos que o pedido) -> EstoqueInsuficienteException, e
     * a transação inteira é desfeita pelo chamador.
     *
     * @param array $linhas cada uma com produto_id e quantidade
     */
    private function baixarEstoque(array $linhas): array
    {
        $antes = [];
        $porProduto = [];
        foreach ($linhas as $l) {
            if (empty($l['produto_id'])) continue;
            $pid = (int)$l['produto_id'];
            $porProduto[$pid] = ($porProduto[$pid] ?? 0) + (int)$l['quantidade'];
        }
        foreach ($porProduto as $pid => $qtd) {
            $stmt = $this->conn->prepare("SELECT nome, estoque_qtd FROM produto WHERE id = :id FOR UPDATE");
            $stmt->execute([':id' => $pid]);
            $prod = $stmt->fetch();
            if (!$prod) {
                throw new RuntimeException('Produto não encontrado.');
            }
            $disponivel = (int)$prod['estoque_qtd'];
            if ($disponivel < $qtd) {
                $msg = $disponivel <= 0
                    ? "{$prod['nome']} está esgotado no momento."
                    : "Estoque insuficiente para {$prod['nome']}: restam {$disponivel} unidade(s).";
                throw new EstoqueInsuficienteException($msg);
            }
            $antes[$pid] = $disponivel;
            $this->conn->prepare("UPDATE produto SET estoque_qtd = estoque_qtd - :q WHERE id = :id")
                       ->execute([':q' => $qtd, ':id' => $pid]);
        }
        return $antes;
    }

    /** Todos os itens de uma mesma nota (pedido feito pelo cliente pode ter vários). */
    public function buscarPorNota(string $nota): array
    {
        $stmt = $this->conn->prepare("
            SELECT p.*, u.nome AS cliente_nome
            FROM pedido p
            LEFT JOIN usuario u ON u.id = p.usuario_id
            WHERE p.nota_fiscal = :nf
            ORDER BY p.id
        ");
        $stmt->execute([':nf' => $nota]);
        return $stmt->fetchAll();
    }

    /** Pedidos do cliente agrupados por nota (um cartão por pedido feito). */
    public function listarDoCliente(int $usuarioId): array
    {
        $stmt = $this->conn->prepare("
            SELECT nota_fiscal,
                   MIN(id) AS primeiro_id,
                   MIN(data_pedido) AS data_pedido,
                   SUM(quantidade) AS itens,
                   SUM(valor_total - desconto) AS total
            FROM pedido
            WHERE usuario_id = :u AND nota_fiscal IS NOT NULL
            GROUP BY nota_fiscal
            ORDER BY MIN(data_pedido) DESC, MIN(id) DESC
        ");
        $stmt->execute([':u' => $usuarioId]);
        return $stmt->fetchAll();
    }

    /**
     * Registra um pedido do cliente com vários itens. Cada item vira uma linha
     * de pedido, e todas recebem a MESMA nota fiscal (NF-<ano>-<menor id>).
     * Tudo numa transação: ou entram todos os itens, ou nenhum.
     *
     * @param array $linhas itens: produto_id, produto_nome, variacao, quantidade, preco_unitario
     */
    public function inserirCarrinho(array $linhas, int $usuarioId, string $dataPedido, ?string $horaRecebimento = null): string
    {
        $this->conn->beginTransaction();
        try {
            $saldo = $this->baixarEstoque($linhas);
            $stmt = $this->conn->prepare("
                INSERT INTO pedido
                    (produto_id, produto_nome, variacao, quantidade, preco_unitario, valor_total,
                     desconto, data_pedido, hora_recebimento, usuario_id, nota_fiscal)
                VALUES
                    (:produto_id, :produto_nome, :variacao, :quantidade, :preco_unitario, :valor_total,
                     :desconto, :data_pedido, :hora_recebimento, :usuario_id, NULL)
            ");
            $ids = [];
            foreach ($linhas as $l) {
                $stmt->execute([
                    ':produto_id'     => $l['produto_id'],
                    ':produto_nome'   => $l['produto_nome'],
                    ':variacao'       => $l['variacao'],
                    ':quantidade'     => $l['quantidade'],
                    ':preco_unitario' => $l['preco_unitario'],
                    ':valor_total'    => round($l['preco_unitario'] * $l['quantidade'], 2),
                    ':desconto'       => (float)($l['desconto'] ?? 0),
                    ':data_pedido'    => $dataPedido,
                    ':hora_recebimento' => $horaRecebimento,
                    ':usuario_id'     => $usuarioId,
                ]);
                $idPedido = (int)$this->conn->lastInsertId();
                $ids[] = $idPedido;

                if (!empty($l['produto_id'])) {
                    $pid = (int)$l['produto_id'];
                    $ea  = $saldo[$pid];
                    $ed  = $ea - (int)$l['quantidade'];
                    $saldo[$pid] = $ed;
                    Entrada::registrar($this->conn, [
                        'data'           => substr($dataPedido, 0, 10),
                        'tipo'           => 'venda',
                        'produto_id'     => $pid,
                        'produto_nome'   => $l['produto_nome'],
                        'pedido_id'      => $idPedido,
                        'usuario_id'     => $usuarioId,
                        'quantidade'     => (int)$l['quantidade'],
                        'valor_unitario' => (float)$l['preco_unitario'],
                        'estoque_antes'  => $ea,
                        'estoque_depois' => $ed,
                    ]);
                }
            }
            $nota = sprintf('NF-%s-%06d', date('Y', strtotime($dataPedido)), min($ids));
            $in = implode(',', $ids);
            $this->conn->prepare("UPDATE pedido SET nota_fiscal = :nf WHERE id IN ({$in})")
                       ->execute([':nf' => $nota]);
            $this->conn->commit();
            return $nota;
        } catch (Throwable $e) {
            $this->conn->rollBack();
            throw $e;
        }
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
        ?string $horaRecebimento,
        ?string $variacao = null,
        ?int $usuarioId = null
    ): int {
        $valorTotal = round($precoUnitario * $quantidade, 2);

        $this->conn->beginTransaction();
        try {
            $saldo = $this->baixarEstoque([['produto_id' => $produtoId, 'quantidade' => $quantidade]]);

            $stmt = $this->conn->prepare("
                INSERT INTO pedido
                    (produto_id, produto_nome, variacao, quantidade, preco_unitario, valor_total,
                     data_pedido, hora_recebimento, nota_fiscal)
                VALUES
                    (:produto_id, :produto_nome, :variacao, :quantidade, :preco_unitario, :valor_total,
                     :data_pedido, :hora_recebimento, NULL)
            ");
            $stmt->execute([
                ':produto_id'       => $produtoId,
                ':produto_nome'     => $produtoNome,
                ':variacao'         => $variacao,
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

            if ($produtoId) {
                $ea = $saldo[$produtoId];
                Entrada::registrar($this->conn, [
                    'data'           => substr($dataPedido, 0, 10),
                    'tipo'           => 'venda',
                    'produto_id'     => $produtoId,
                    'produto_nome'   => $produtoNome,
                    'pedido_id'      => $id,
                    'usuario_id'     => $usuarioId,
                    'quantidade'     => $quantidade,
                    'valor_unitario' => $precoUnitario,
                    'estoque_antes'  => $ea,
                    'estoque_depois' => $ea - $quantidade,
                ]);
            }

            $this->conn->commit();
            return $id;
        } catch (Throwable $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function deletar(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM pedido WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
