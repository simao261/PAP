<?php
require_once 'includes/config.php';

// Processar envio de mensagem
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Deve fazer login para enviar uma mensagem de suporte.';
        header('Location: login.php');
        exit();
    }
    
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    
    if (empty($subject) || empty($message)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO support_messages (user_id, subject, message) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $subject, $message]);
            
            $_SESSION['success'] = 'Mensagem de suporte enviada com sucesso! Responderemos em breve.';
            header('Location: support.php');
            exit();
            
        } catch (Exception $e) {
            $error = 'Erro ao enviar mensagem. Tente novamente.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suporte - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo"><?php echo SITE_NAME; ?></a>
            
            <ul class="nav-links">
                <li><a href="index.php">Início</a></li>
                <li><a href="shop.php">Loja</a></li>
                <li><a href="build_simulator.php">Simulador</a></li>
                <li><a href="support.php">Suporte</a></li>
                <li><a href="budget_request.php">Pedir Orçamento</a></li>
            </ul>
            
            <div class="user-menu">
                <a href="cart.php" class="cart-icon">
                    🛒 <span class="cart-count"><?php echo getCartCount(); ?></span>
                </a>
                
                <?php if (isLoggedIn()): ?>
                    <a href="profile.php">Perfil</a>
                    <?php if (isAdmin()): ?>
                        <a href="admin/">Admin</a>
                    <?php endif; ?>
                    <a href="logout.php">Sair</a>
                <?php else: ?>
                    <a href="login.php">Entrar</a>
                    <a href="register.php">Registar</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Page Header -->
    <section style="background: #f8f9fa; padding: 2rem 0;">
        <div class="container">
            <h1 style="text-align: center; color: #333; margin-bottom: 0.5rem;">Suporte ao Cliente</h1>
            <p style="text-align: center; color: #666;">Estamos aqui para ajudar! Entre em contacto connosco</p>
        </div>
    </section>

    <!-- Messages -->
    <div class="container" style="padding: 1rem 0;">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
    </div>

    <!-- Support Content -->
    <section class="container" style="padding: 2rem 0;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
            <!-- Contact Form -->
            <div>
                <h2 style="margin-bottom: 2rem; color: #333;">Enviar Mensagem</h2>
                
                <?php if (!isLoggedIn()): ?>
                    <div style="background: #fff3cd; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                        <h4 style="color: #856404; margin-bottom: 1rem;">🔒 Login Necessário</h4>
                        <p style="color: #856404; margin-bottom: 1rem;">Deve fazer login para enviar uma mensagem de suporte.</p>
                        <a href="login.php" class="btn">Fazer Login</a>
                    </div>
                <?php else: ?>
                    <form method="POST" class="form-container" style="max-width: none; margin: 0; box-shadow: none; padding: 0;">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="subject">Assunto</label>
                            <input type="text" 
                                   id="subject" 
                                   name="subject" 
                                   class="form-control" 
                                   placeholder="Ex: Problema com encomenda, Dúvida sobre produto..."
                                   value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Mensagem</label>
                            <textarea id="message" 
                                      name="message" 
                                      class="form-control" 
                                      placeholder="Descreva o seu problema ou questão..."
                                      rows="6" 
                                      required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn" style="width: 100%;">Enviar Mensagem</button>
                    </form>
                <?php endif; ?>
            </div>
            
            <!-- Contact Info -->
            <div>
                <h2 style="margin-bottom: 2rem; color: #333;">Informações de Contacto</h2>
                
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                    <h3 style="color: #667eea; margin-bottom: 1.5rem;">📞 Contactos</h3>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <strong>Email:</strong><br>
                        <a href="mailto:suporte@buildlab.pt" style="color: #667eea;">suporte@buildlab.pt</a>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <strong>Telefone:</strong><br>
                        <span style="color: #666;">+351 123 456 789</span>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <strong>Horário de Atendimento:</strong><br>
                        <span style="color: #666;">Segunda a Sexta: 9h00 - 18h00</span>
                    </div>
                </div>
                
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                    <h3 style="color: #667eea; margin-bottom: 1.5rem;">❓ Perguntas Frequentes</h3>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <strong>Q: Qual é o prazo de entrega?</strong><br>
                        <span style="color: #666;">A: O prazo de entrega é de 3-5 dias úteis após a confirmação do pagamento.</span>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <strong>Q: Posso personalizar uma build?</strong><br>
                        <span style="color: #666;">A: Sim! Use a nossa funcionalidade "Pedir Orçamento" para solicitar uma build personalizada.</span>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <strong>Q: Oferecem garantia?</strong><br>
                        <span style="color: #666;">A: Sim, todas as nossas builds têm garantia de 2 anos.</span>
                    </div>
                    
                    <div>
                        <strong>Q: Como posso acompanhar a minha encomenda?</strong><br>
                        <span style="color: #666;">A: Pode acompanhar o estado da sua encomenda no seu perfil.</span>
                    </div>
                </div>
                
                <div style="background: #d1ecf1; padding: 2rem; border-radius: 10px;">
                    <h4 style="color: #0c5460; margin-bottom: 1rem;">💡 Dicas para um Melhor Suporte</h4>
                    <ul style="color: #0c5460; line-height: 1.6;">
                        <li>Seja específico sobre o problema</li>
                        <li>Inclua o número da encomenda se aplicável</li>
                        <li>Descreva os passos que já tentou</li>
                        <li>Mencione se é urgente</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 <?php echo SITE_NAME; ?>. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>
</html>
