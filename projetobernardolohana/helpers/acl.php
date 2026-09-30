<?php
/**
 * Controle de acesso por perfil de usuário.
 * Perfis existentes: cliente, garcom, gerente.
 * (O perfil de usuário "garcom" não deve ser confundido com a tabela/model
 * `fornecedor`, que representa os fornecedores de mercadoria do restaurante
 * — são conceitos diferentes que só coincidem de nome por acaso.)
 *
 * Regra combinada nas três funções abaixo:
 *   - cliente -> só o Cardápio (dashboard)
 *   - garcom  -> só Estoque e Pedidos
 *   - gerente -> todas as páginas (Cardápio, Fornecedores, Estoque, Pedidos, Categorias)
 */

function acl_exigirLogin(): void
{
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: index.php?controller=auth&action=form");
        exit;
    }
}

/**
 * Exige login E que o perfil da sessão esteja entre os $permitidos.
 * 'gerente' sempre passa, mesmo sem estar na lista (acesso a tudo).
 */
function acl_exigirPerfil(array $permitidos): void
{
    acl_exigirLogin();
    $perfil = $_SESSION['perfil'] ?? '';
    if ($perfil === 'gerente') {
        return;
    }
    if (!in_array($perfil, $permitidos, true)) {
        http_response_code(403);
        require_once __DIR__ . '/../views/acesso_negado.php';
        exit;
    }
}

/** Usado pelas views para decidir quais links de navegação mostrar. */
function acl_podeVer(string $pagina): bool
{
    $perfil = $_SESSION['perfil'] ?? '';
    if ($perfil === 'gerente') return true;
    $mapa = [
        'dashboard'   => ['cliente'],
        'fornecedor'  => [],              // gestão de fornecedores: só gerente (ver nota na entrega)
        'produto'     => ['garcom'],
        'venda'       => ['garcom'],
    ];
    return in_array($perfil, $mapa[$pagina] ?? [], true);
}
