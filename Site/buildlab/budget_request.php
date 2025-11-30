<?php
require_once 'includes/config.php';

// Processar pedido de orçamento
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Deve fazer login para pedir um orçamento.';
        header('Location: login.php');
        exit();
    }
    
    $cpu_preference = sanitizeInput($_POST['cpu_preference']);
    $gpu_preference = sanitizeInput($_POST['gpu_preference']);
    $ram_preference = sanitizeInput($_POST['ram_preference']);
    $storage_preference = sanitizeInput($_POST['storage_preference']);
    $budget = isset($_POST['budget']) && $_POST['budget'] > 0 ? (float)$_POST['budget'] : null;
    $notes = sanitizeInput($_POST['notes']);
    
    if (empty($cpu_preference) && empty($gpu_preference) && empty($ram_preference) && empty($storage_preference)) {
        $error = 'Por favor, especifique pelo menos uma preferência de componente.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO build_requests (user_id, cpu_preference, gpu_preference, ram_preference, storage_preference, budget, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $cpu_preference, $gpu_preference, $ram_preference, $storage_preference, $budget, $notes]);
            
            $_SESSION['success'] = 'Pedido de orçamento enviado com sucesso! Entraremos em contacto em breve.';
            header('Location: budget_request.php');
            exit();
            
        } catch (Exception $e) {
            $error = 'Erro ao enviar pedido. Tente novamente.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedir Orçamento - <?php echo SITE_NAME; ?></title>
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
            <h1 style="text-align: center; color: #333; margin-bottom: 0.5rem;">Pedir Orçamento Personalizado</h1>
            <p style="text-align: center; color: #666;">Criamos a build perfeita para as suas necessidades</p>
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

    <!-- Budget Request Content -->
    <section class="container" style="padding: 2rem 0;">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3rem;">
            <!-- Request Form -->
            <div>
                <h2 style="margin-bottom: 2rem; color: #333;">Especificações da Build</h2>
                
                <?php if (!isLoggedIn()): ?>
                    <div style="background: #fff3cd; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                        <h4 style="color: #856404; margin-bottom: 1rem;">🔒 Login Necessário</h4>
                        <p style="color: #856404; margin-bottom: 1rem;">Deve fazer login para pedir um orçamento personalizado.</p>
                        <a href="login.php" class="btn">Fazer Login</a>
                    </div>
                <?php else: ?>
                    <form method="POST" class="form-container" style="max-width: none; margin: 0; box-shadow: none; padding: 0;">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                            <h3 style="color: #333; margin-bottom: 1rem;">💻 Componentes</h3>
                            
                            <div class="form-group">
                                <label for="cpu_preference">Processador (CPU)</label>
                                <input type="text" 
                                       id="cpu_preference" 
                                       name="cpu_preference" 
                                       class="form-control" 
                                       placeholder="Ex: Intel Core i7, AMD Ryzen 7, etc."
                                       value="<?php echo isset($_POST['cpu_preference']) ? htmlspecialchars($_POST['cpu_preference']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="gpu_preference">Placa Gráfica (GPU)</label>
                                <input type="text" 
                                       id="gpu_preference" 
                                       name="gpu_preference" 
                                       class="form-control" 
                                       placeholder="Ex: RTX 4070, RX 7800 XT, etc."
                                       value="<?php echo isset($_POST['gpu_preference']) ? htmlspecialchars($_POST['gpu_preference']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="ram_preference">Memória RAM</label>
                                <input type="text" 
                                       id="ram_preference" 
                                       name="ram_preference" 
                                       class="form-control" 
                                       placeholder="Ex: 16GB DDR4, 32GB DDR5, etc."
                                       value="<?php echo isset($_POST['ram_preference']) ? htmlspecialchars($_POST['ram_preference']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="storage_preference">Armazenamento</label>
                                <input type="text" 
                                       id="storage_preference" 
                                       name="storage_preference" 
                                       class="form-control" 
                                       placeholder="Ex: 1TB SSD NVMe, 2TB HDD, etc."
                                       value="<?php echo isset($_POST['storage_preference']) ? htmlspecialchars($_POST['storage_preference']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                            <h3 style="color: #333; margin-bottom: 1rem;">💰 Orçamento</h3>
                            
                            <div class="form-group">
                                <label for="budget">Orçamento Máximo (€)</label>
                                <input type="number" 
                                       id="budget" 
                                       name="budget" 
                                       class="form-control" 
                                       placeholder="Ex: 1500"
                                       min="0" 
                                       step="0.01"
                                       value="<?php echo isset($_POST['budget']) ? htmlspecialchars($_POST['budget']) : ''; ?>">
                                <small style="color: #666; font-size: 0.9rem;">Deixe em branco se não tiver um orçamento específico</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Observações Adicionais</label>
                            <textarea id="notes" 
                                      name="notes" 
                                      class="form-control" 
                                      placeholder="Descreva o uso pretendido (gaming, trabalho, edição, etc.), preferências de marca, requisitos especiais..."
                                      rows="4"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn" style="width: 100%; margin-top: 1rem;">Enviar Pedido de Orçamento</button>
                    </form>
                <?php endif; ?>
            </div>
            
            <!-- Info Panel -->
            <div>
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                    <h3 style="color: #667eea; margin-bottom: 1.5rem;">🎯 Como Funciona</h3>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="background: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">1</div>
                            <span>Preencha as suas preferências</span>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="background: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">2</div>
                            <span>Analisamos as suas necessidades</span>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="background: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">3</div>
                            <span>Enviamos uma proposta personalizada</span>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="background: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">4</div>
                            <span>Pode aceitar ou pedir alterações</span>
                        </div>
                    </div>
                </div>
                
                <div style="background: #d1ecf1; padding: 2rem; border-radius: 10px; margin-bottom: 2rem;">
                    <h4 style="color: #0c5460; margin-bottom: 1rem;">💡 Dicas para um Melhor Orçamento</h4>
                    <ul style="color: #0c5460; line-height: 1.6;">
                        <li>Seja específico sobre o uso (gaming, trabalho, etc.)</li>
                        <li>Mencione jogos ou software que pretende usar</li>
                        <li>Indique se precisa de periféricos</li>
                        <li>Especifique se tem preferências de marca</li>
                    </ul>
                </div>
                
                <div style="background: #d4edda; padding: 2rem; border-radius: 10px;">
                    <h4 style="color: #155724; margin-bottom: 1rem;">✅ Vantagens</h4>
                    <ul style="color: #155724; line-height: 1.6;">
                        <li>Build 100% personalizada</li>
                        <li>Otimizada para as suas necessidades</li>
                        <li>Melhor relação qualidade/preço</li>
                        <li>Suporte especializado</li>
                        <li>Garantia completa</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 <?php echo SITE_NAME; ?>. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>
</html>
