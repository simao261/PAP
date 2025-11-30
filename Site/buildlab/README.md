# BuildLab — O que este site faz

O **BuildLab** é uma loja online de PCs pré-montados e serviço de montagem personalizada. Foi criado para permitir a visualização, compra e pedido de orçamentos de builds completos, bem como suporte ao cliente e um simulador de componentes.

---

## Índice rápido
- Instalação (passo a passo)
- Estrutura do projeto
- Como desenvolver (comentários linha-a-linha)
- Testes e execução local
- Contas de teste
- Resolução de problemas comuns

---

## ⚙️ Instalação — passo a passo (Windows / XAMPP)
1. Instalar XAMPP:
   - Descarregue e instale XAMPP (https://www.apachefriends.org/).
   - Inicie o Apache e o MySQL no painel do XAMPP.

2. Copiar ficheiros:
   - Copie a pasta `buildlab` para `C:\xampp\htdocs\buildlab\` ou `C:\wamp64\www\buildlab\` conforme o seu ambiente.

3. Criar base de dados:
   - Abra http://localhost/phpmyadmin
   - Crie uma base de dados (ex.: `buildlab`) ou importe o ficheiro `database.sql`.
   - Se importar, verifique se as tabelas foram criadas (`users`, `builds`, `components`, `orders`, `order_items`, `support_messages`, `build_requests`, etc.).

4. Configurar credenciais:
   - Abra `includes/config.php`.
   - Atualize DB_HOST, DB_NAME, DB_USER, DB_PASS com as suas credenciais MySQL.
   - Verifique constante `SITE_NAME` se desejar alterar o nome do site.

5. Permissões e ficheiros estáticos:
   - No Windows normalmente não é necessário alterar permissões.
   - Coloque imagens na pasta `images/` (ex.: `placeholder.jpg`, `build1.jpg`).

6. Aceder ao site:
   - Navegue para http://localhost/buildlab

---

## Estrutura do projeto (visão detalhada)
- admin/ — painel administrativo (gestão de builds, encomendas, suporte, orçamentos, utilizadores)
- api/ — endpoints API (ex.: notifications)
- css/style.css — estilos principais
- includes/ — configuração e utilitários (config.php, notifications.php, recommendation.php)
- index.php — página principal
- shop.php — listagem de builds
- build.php — detalhes de uma build
- cart.php — carrinho de compras
- checkout.php — finalizar compra
- login.php / register.php / logout.php — autenticação
- profile.php — perfil do utilizador (encomendas, pedidos, suporte)
- support.php — formulário de suporte
- budget_request.php — pedido de orçamento
- build_simulator.php — simulador de componentes
- database.sql — script da base de dados

---

## Como executar localmente e testar
1. Inicie Apache e MySQL no XAMPP.
2. Certifique-se que `includes/config.php` aponta para a BD correta.
3. Aceda a http://localhost/buildlab
4. Teste fluxo básico:
   - Registar conta
   - Login
   - Navegar loja
   - Adicionar build ao carrinho (se existirem builds)
   - Fazer checkout (requer sessão e itens no carrinho)
   - Pedir orçamento e enviar mensagem de suporte

---

## Contas de teste (pré-definidas)
- Administrador:
  - Email: admin@buildlab.pt
  - Password: 12345
- Utilizador:
  - Email: joao@buildlab.pt
  - Password: 12345

(Se estas contas não existirem na sua importação, crie-as manualmente via phpMyAdmin.)

---

## Comentários linha-a-linha no código (workflow que vou seguir)
Vou adicionar comentários curtos linha-a-linha aos ficheiros principais do projeto para explicar o que cada linha/bloco faz. Proponho fazer em 2 passes para evitar entregas muito grandes:

- Passo 1 (entrega imediata): ficheiros principais que controlam fluxo de utilizador e store:
  - index.php, shop.php, build.php, cart.php, checkout.php, login.php, register.php, logout.php, profile.php, support.php, budget_request.php, build_simulator.php, notifications.php

- Passo 2 (seguinte): admin/*, includes/*, api/*, css/style.css e restantes ficheiros.

Confirme se concorda com este plano. Após confirmação, vou enviar patches com os ficheiros atualizados (comentados) em lotes. Cada ficheiro enviado conterá comentários curtos em português de Portugal sem alterar a lógica.

---

## Resolução de problemas rápidos
- Página em branco / erros PHP: ative display_errors em php.ini durante desenvolvimento ou verifique `apache\logs\php_error.log`.
- Erro de BD: verifique credenciais e se o MySQL está a correr.
- Imagens faltam: coloque ficheiros em `images/` com os nomes referidos no README/INSTALACAO.md.

---

## Contribuir / Checklist para envio de PR
- Validar que `includes/config.php` não contém credenciais sensíveis em repositório público.
- Executar testes manuais do fluxo de autenticação, carrinho e checkout.
- Colocar capturas de ecrã em /docs se for criar UI changes.

---


