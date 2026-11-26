# Sistema de Gestão e Pedidos (`projetobernardolohana`)

Aplicação web em **PHP** estruturada no padrão **MVC** com controlo de acessos baseado em perfis/cargos (**ACL**), gestão de catálogo com variações de produtos, entradas/movimentações de mercadorias, carrinho de compras, pedidos, vendas e emissão/visualização de nota fiscal, além de scripts SQL para criação e migração da base de dados.

## ✨ Funcionalidades

- **Autenticação e Controlo de Acessos (ACL):** login, registo de utilizadores, gestão de utilizadores/funcionários/cargos e bloqueio de rotas não autorizadas (`acesso_negado.php`).
- **Gestão de Produtos, Variações e Categorias:** cadastro e edição de produtos, variações de produto, categorias e upload de imagens.
- **Estoque, Entradas e Movimentações:** registo de entradas de mercadorias, histórico de movimentações e controlo de estoque.
- **Fornecedores e Dados da Empresa:** gestão de fornecedores (com imagens) e informações da empresa, incluindo consulta de CEP via JavaScript (`cep.js`).
- **Carrinho, Pedidos, Vendas e Nota Fiscal:** fluxo de carrinho, acompanhamento de pedidos do cliente, registo de vendas e visualização de nota fiscal.
- **Painel Administrativo (`dashboard.php`):** visão geral operacional do sistema.

## 🛠️ Tecnologias utilizadas

- **Backend:** PHP (`config/db.php`, `controllers/`, `models/`, `helpers/acl.php`, `index.php`)
- **Base de Dados:** SQL (`Arquivos importantes/schema_completo.sql`, `projetorrgi51.sql` e scripts de migração)
- **Frontend:** HTML/PHP nas views (`views/`), CSS (`public/assets/css/style.css`) e JavaScript (`public/assets/js/cep.js`)

## 📁 Estrutura do projeto

```text
projetobernardolohana/
├── Arquivos importantes/     # Schemas SQL, dump e scripts de migração
├── config/                   # Configuração da base de dados (db.php)
├── controllers/              # Controladores (auth, produto, venda, pedido, etc.)
├── helpers/                  # Funções auxiliares e ACL (acl.php)
├── models/                   # Modelos de dados (produto, venda, entrada, etc.)
├── public/
│   ├── assets/               # CSS, JS (cep.js) e imagens padrão
│   └── uploads/              # Imagens enviadas (produtos, fornecedores e img)
├── views/                    # Páginas e interfaces do sistema
└── index.php                 # Ponto de entrada da aplicação
