<?php
require_once 'includes/config.php';
require_once 'includes/recommendation.php';

// Buscar componentes disponíveis
$components = [];
$categories = ['CPU', 'GPU', 'RAM', 'Storage'];

foreach ($categories as $category) {
    $stmt = $pdo->prepare("SELECT * FROM individual_components WHERE category = ? ORDER BY price ASC");
    $stmt->execute([$category]);
    $components[$category] = $stmt->fetchAll();
}

// Processar seleção de componentes
$selected_components = [];
$total_price = 0;
$compatibility_issues = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['selected_components'])) {
    $selected_components = $_POST['selected_components'];
    
    foreach ($selected_components as $component_id) {
        $stmt = $pdo->prepare("SELECT * FROM individual_components WHERE id = ?");
        $stmt->execute([$component_id]);
        $component = $stmt->fetch();
        
        if ($component) {
            $total_price += $component['price'];
        }
    }
    
    // Verificar compatibilidade básica
    $compatibility_issues = checkCompatibility($selected_components);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulador de Build - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <style>
        .component-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .component-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .component-card.selected {
            border: 2px solid #007bff;
            background-color: #f8f9fa;
        }
        .price-display {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
        }
        .compatibility-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 10px;
            margin: 10px 0;
        }
    </style>
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
                    <a href="logout.php">Sair</a>
                <?php else: ?>
                    <a href="login.php">Entrar</a>
                    <a href="register.php">Registar</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Simulador de Build -->
    <section class="container py-5">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-5">Simulador de Build Personalizada</h1>
                
                <div class="row">
                    <!-- Componentes -->
                    <div class="col-lg-8">
                        <form method="POST" id="buildForm">
                            <?php foreach ($categories as $category): ?>
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h3><?php echo $category; ?></h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <?php foreach ($components[$category] as $component): ?>
                                                <div class="col-md-6 col-lg-4 mb-3">
                                                    <div class="card component-card h-100" 
                                                         data-component-id="<?php echo $component['id']; ?>"
                                                         data-category="<?php echo $category; ?>">
                                                        <div class="card-body">
                                                            <h6 class="card-title"><?php echo $component['name']; ?></h6>
                                                            <p class="card-text small"><?php echo $component['brand']; ?> <?php echo $component['model']; ?></p>
                                                            <p class="card-text"><?php echo formatPrice($component['price']); ?></p>
                                                            <?php if ($component['compatibility_notes']): ?>
                                                                <small class="text-muted"><?php echo $component['compatibility_notes']; ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg">Calcular Preço Total</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Resumo -->
                    <div class="col-lg-4">
                        <div class="card sticky-top">
                            <div class="card-header">
                                <h4>Resumo da Build</h4>
                            </div>
                            <div class="card-body">
                                <div id="selectedComponents">
                                    <p class="text-muted">Selecione os componentes</p>
                                </div>
                                
                                <hr>
                                
                                <div class="price-display text-center">
                                    <span id="totalPrice">0,00 €</span>
                                </div>
                                
                                <?php if (!empty($compatibility_issues)): ?>
                                    <div class="compatibility-warning">
                                        <h6>⚠️ Avisos de Compatibilidade:</h6>
                                        <ul class="mb-0">
                                            <?php foreach ($compatibility_issues as $issue): ?>
                                                <li><?php echo $issue; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mt-3">
                                    <button class="btn btn-success w-100" onclick="saveBuild()">Guardar Build</button>
                                </div>
                            </div>
                        </div>
                    </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedComponents = {};
        
        function selectComponent(componentId, category) {
            // Remover seleção anterior da categoria
            document.querySelectorAll(`[data-category="${category}"]`).forEach(card => {
                card.classList.remove('selected');
            });
            
            // Selecionar novo componente
            const card = document.querySelector(`[data-component-id="${componentId}"]`);
            if (card) {
                card.classList.add('selected');
                selectedComponents[category] = componentId;
                updateSummary();
            }
        }
        
        // Adicionar event listeners aos cards
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.component-card').forEach(card => {
                card.addEventListener('click', function() {
                    const componentId = this.getAttribute('data-component-id');
                    const category = this.getAttribute('data-category');
                    if (componentId && category) {
                        selectComponent(componentId, category);
                    }
                });
            });
        });
        
        function updateSummary() {
            const summaryDiv = document.getElementById('selectedComponents');
            const totalPriceSpan = document.getElementById('totalPrice');
            
            let html = '';
            let totalPrice = 0;
            
            for (const [category, componentId] of Object.entries(selectedComponents)) {
                const card = document.querySelector(`[data-component-id="${componentId}"]`);
                if (card) {
                    const title = card.querySelector('.card-title').textContent;
                    const priceText = card.querySelector('.card-text:last-of-type').textContent;
                    const price = parseFloat(priceText.replace(/[^\d,]/g, '').replace(',', '.'));
                    
                    html += `<div class="mb-2"><strong>${category}:</strong> ${title}</div>`;
                    totalPrice += price;
                }
            }
            
            if (Object.keys(selectedComponents).length === 0) {
                html = '<p class="text-muted">Selecione os componentes</p>';
            }
            
            summaryDiv.innerHTML = html;
            totalPriceSpan.textContent = totalPrice.toFixed(2) + ' €';
        }
        
        function saveBuild() {
            if (Object.keys(selectedComponents).length < 4) {
                alert('Por favor, selecione todos os componentes necessários.');
                return;
            }
            
            // Implementar salvamento da build
            alert('Build guardada com sucesso!');
        }
    </script>
</body>
</html>

<?php
function checkCompatibility($selected_components) {
    $issues = [];
    
    // Verificações básicas de compatibilidade
    if (count($selected_components) < 4) {
        $issues[] = "Selecione todos os componentes necessários (CPU, GPU, RAM, Storage)";
    }
    
    // Aqui poderiam ser adicionadas mais verificações específicas
    // como compatibilidade de socket, DDR4 vs DDR5, etc.
    
    return $issues;
}
?>
