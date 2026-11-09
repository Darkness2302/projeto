<?php
require_once __DIR__ . '/../models/funcionario.php';
require_once __DIR__ . '/../models/cargo.php';
require_once __DIR__ . '/../helpers/acl.php';

class FuncionarioController
{
    // Tela de Funcionários (garçons): só o gerente acessa. Mesmo schema da tabela fornecedor.
    public function index(): void
    {
        acl_exigirPerfil([]);
        $funcionarioModel = new Funcionario();
        $funcionarios = $funcionarioModel->listarTodos();
        $cargos = (new Cargo())->listarTodos();

        $editar = null;
        if (isset($_GET['id'])) {
            $editar = $funcionarioModel->buscarPorId((int)$_GET['id']);
        }

        require_once __DIR__ . '/../views/funcionarios.php';
    }

    public function salvar(): void
    {
        acl_exigirPerfil([]);

        $id       = (int)($_POST['id'] ?? 0);
        $nome     = trim($_POST['nome'] ?? '');
        $cpf      = trim($_POST['cpf'] ?? '');
        $cpf      = $cpf === '' ? null : $cpf;
        $lugar    = trim($_POST['lugar'] ?? '');
        $lugar    = $lugar === '' ? null : $lugar;
        $cargoId  = (int)($_POST['cargo_id'] ?? 0);
        $cargoId  = $cargoId > 0 ? $cargoId : null;

        if ($nome === '') {
            die("Nome do funcionário é obrigatório.");
        }

        // Expediente: horário de início e de fim (vazio = não informado)
        $horaInicio = trim($_POST['hora_inicio'] ?? '');
        $horaInicio = $horaInicio === '' ? null : $horaInicio;
        $horaFim    = trim($_POST['hora_fim'] ?? '');
        $horaFim    = $horaFim === '' ? null : $horaFim;

        $funcionarioModel = new Funcionario();
        if ($id > 0) {
            $funcionarioModel->atualizar($id, $nome, $cpf, $lugar, $cargoId, $horaInicio, $horaFim);
        } else {
            $funcionarioModel->inserir($nome, $cpf, $lugar, $cargoId, $horaInicio, $horaFim);
        }

        header("Location: index.php?controller=funcionario&action=index");
        exit;
    }

    public function toggle(): void
    {
        acl_exigirPerfil([]);
        $id = (int)($_GET['id'] ?? 0);
        $ativo = (int)($_GET['ativo'] ?? 1);
        if ($id <= 0) die("ID inválido.");
        $funcionarioModel = new Funcionario();
        $funcionarioModel->setAtivo($id, $ativo === 1);
        header("Location: index.php?controller=funcionario&action=index");
        exit;
    }

    public function deletar(): void
    {
        acl_exigirPerfil([]);
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) die("ID inválido.");

        $funcionarioModel = new Funcionario();
        if (!$funcionarioModel->buscarPorId($id)) die("Funcionário não encontrado.");
        $funcionarioModel->deletar($id);

        header("Location: index.php?controller=funcionario&action=index");
        exit;
    }

    // -------------------------
    // Cargos (só gerente): criados aqui e escolhidos no cadastro do funcionário
    // -------------------------
    public function salvarCargo(): void
    {
        acl_exigirPerfil([]);
        $nome = trim($_POST['nome_cargo'] ?? '');
        if ($nome === '') {
            $_SESSION['flash_cargo'] = 'Digite o nome do cargo.';
        } elseif (!(new Cargo())->inserir($nome)) {
            $_SESSION['flash_cargo'] = 'Já existe um cargo com esse nome.';
        }
        header("Location: index.php?controller=funcionario&action=index");
        exit;
    }

    public function deletarCargo(): void
    {
        acl_exigirPerfil([]);
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) die("ID inválido.");
        (new Cargo())->deletar($id);
        header("Location: index.php?controller=funcionario&action=index");
        exit;
    }
}
