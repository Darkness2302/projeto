<?php
require_once __DIR__ . '/../config/db.php';

class Empresa
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /** Só existe uma configuração (id fixo = 1). Retorna null se o gerente ainda não cadastrou nada. */
    public function buscar(): ?array
    {
        try {
            $stmt = $this->conn->query("SELECT * FROM empresa WHERE id = 1");
            $r = $stmt->fetch();
            return $r ?: null;
        } catch (Exception $e) {
            return null; // tabela ainda não migrada — ver migracao_empresa.sql
        }
    }

    public function salvar(string $cnpj, string $razaoSocial, ?string $lugar, ?string $telefone): void
    {
        $stmt = $this->conn->prepare("
            INSERT INTO empresa (id, cnpj, razao_social, lugar, telefone)
            VALUES (1, :cnpj, :razao_social, :lugar, :telefone)
            ON DUPLICATE KEY UPDATE
                cnpj = VALUES(cnpj),
                razao_social = VALUES(razao_social),
                lugar = VALUES(lugar),
                telefone = VALUES(telefone)
        ");
        $stmt->execute([
            ':cnpj'         => $cnpj,
            ':razao_social' => $razaoSocial,
            ':lugar'        => $lugar,
            ':telefone'     => $telefone
        ]);
    }

    /**
     * Valida o dígito verificador do CNPJ (algoritmo padrão mod-11).
     * Só confere o formato/checksum — não confirma que o CNPJ existe
     * de verdade na Receita Federal (quem faz essa parte é a consulta
     * no navegador, via BrasilAPI).
     */
    public static function cnpjValido(string $cnpj): bool
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);
        if (strlen($cnpj) !== 14) return false;
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) return false; // todos os dígitos iguais

        $digitos = array_map('intval', str_split($cnpj));

        $pesos1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;
        foreach ($pesos1 as $i => $p) $soma += $digitos[$i] * $p;
        $resto = $soma % 11;
        $dv1 = $resto < 2 ? 0 : 11 - $resto;
        if ($dv1 !== $digitos[12]) return false;

        $pesos2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;
        foreach ($pesos2 as $i => $p) $soma += $digitos[$i] * $p;
        $resto = $soma % 11;
        $dv2 = $resto < 2 ? 0 : 11 - $resto;
        if ($dv2 !== $digitos[13]) return false;

        return true;
    }
}
