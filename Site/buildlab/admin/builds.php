<?php
require_once '../includes/config.php';

// Verificar se é administrador
requireAdmin();

// Processar ações
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'create') {
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $image_path = sanitizeInput($_POST['image_path']);
        
        if (!empty($title) && !empty($description) && $price > 0) {
            try {
                $stmt = $pdo->prepare("INSERT INTO builds (title, description, price, stock, image_path, created_by) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $price, $stock, $image_path, $_SESSION['user_id']]);
                $_SESSION['success'] = 'Build criada com sucesso!';
            } catch (Exception $e) {
                $_SESSION['error'] = 'Erro ao criar build.';
            }
        } else {
            $_SESSION['error'] = 'Por favor, preencha todos os campos obrigatórios.';
        }
    } elseif ($action == 'update') {
        $id = (int)$_POST['id'];
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $image_path = sanitizeInput($_POST['image_path']);
        
        try {
            $stmt = $pdo->prepare("UPDATE builds SET title = ?, description = ?, price = ?, stock = ?, image_path = ? WHERE id = ?");
            $stmt->execute([$title, $description, $price, $stock, $image_path, $id]);
            $_SESSION['success'] = 'Build atualizada com sucesso!';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar build.';
        }
    } elseif ($action == 'delete') {
        $id = (int)$_POST['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM builds WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = 'Build eliminada com sucesso!';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro ao eliminar build.';
        }
    }
    
    header('Location: builds.php');
    exit();
}

// Buscar todas as builds
$stmt = $pdo->query("SELECT * FROM builds ORDER BY created_at DESC");
$builds = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Builds - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div style="display: flex; min-height: 100vh;">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div style="padding: 2rem; text-align: center; border-bottom: 1px solid #495057;">
                <h2 style="color: white; margin-bottom: 0.5rem;"><?php echo SITE_NAME; ?></h2>
                <p style="color: #adb5bd; font-size: 0.9rem;">Painel de Administração</p>
            </div>
            
            <ul>
                <li><a href="index.php">📊 Dashboard</a></li>
                <li><a href="builds.php">🖥️ Builds</a></li>
                <li><a href="orders.php">📦 Encomendas</a></li>
                <li><a href="budget_requests.php">💰 Pedidos de Orçamento</a></li>
                <li><a href="support.php">💬 Suporte</a></li>
                <li><a href="users.php">👥 Utilizadores</a></li>
                <li><a href="../index.php">🏠 Voltar ao Site</a></li>
                <li><a href="../logout.php">🚪 Sair</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="admin-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="color: #333;">Gerir Builds</h1>
                <button onclick="document.getElementById('createModal').style.display='block'" class="btn">+ Nova Build</button>
            </div>
            
            <!-- Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <!-- Builds Table -->
            <div style="background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagem</th>
                            <th>Título</th>
                            <th>Preço</th>
                            <th>Stock</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($builds as $build): ?>
                            <tr>
                                <td>#<?php echo $build['id']; ?></td>
                                <td>
                                    <img src="../<?php echo $build['image_path'] ?: 'images/placeholder.jpg'; ?>" 
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                </td>
                                <td><?php echo htmlspecialchars($build['title']); ?></td>
                                <td><?php echo formatPrice($build['price']); ?></td>
                                <td><?php echo $build['stock']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($build['created_at'])); ?></td>
                                <td>
                                    <button onclick="editBuild(<?php echo htmlspecialchars(json_encode($build)); ?>)" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.9rem;">Editar</button>
                                    <button onclick="deleteBuild(<?php echo $build['id']; ?>)" class="btn btn-danger" style="padding: 0.3rem 0.8rem; font-size: 0.9rem;">Eliminar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Create/Edit Modal -->
    <div id="createModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div style="background: white; margin: 5% auto; padding: 2rem; border-radius: 10px; width: 80%; max-width: 600px;">
            <h2 id="modalTitle">Nova Build</h2>
            
            <form method="POST" id="buildForm">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="buildId">
                
                <div class="form-group">
                    <label for="title">Título</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Descrição</label>
                    <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="price">Preço (€)</label>
                        <input type="number" id="price" name="price" class="form-control" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input type="number" id="stock" name="stock" class="form-control" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="image_path">Caminho da Imagem</label>
                    <input type="text" id="image_path" name="image_path" class="form-control" placeholder="images/build1.jpg">
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn" style="flex: 1;">Guardar</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary" style="flex: 1;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Modal -->
    <div id="deleteModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div style="background: white; margin: 20% auto; padding: 2rem; border-radius: 10px; width: 80%; max-width: 400px; text-align: center;">
            <h3 style="color: #dc3545; margin-bottom: 1rem;">Confirmar Eliminação</h3>
            <p style="margin-bottom: 2rem;">Tem certeza que deseja eliminar esta build?</p>
            
            <form method="POST" id="deleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteId">
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-danger" style="flex: 1;">Eliminar</button>
                    <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary" style="flex: 1;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function editBuild(build) {
            document.getElementById('modalTitle').textContent = 'Editar Build';
            document.getElementById('formAction').value = 'update';
            document.getElementById('buildId').value = build.id;
            document.getElementById('title').value = build.title;
            document.getElementById('description').value = build.description;
            document.getElementById('price').value = build.price;
            document.getElementById('stock').value = build.stock;
            document.getElementById('image_path').value = build.image_path || '';
            document.getElementById('createModal').style.display = 'block';
        }
        
        function deleteBuild(id) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('createModal').style.display = 'none';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            const createModal = document.getElementById('createModal');
            const deleteModal = document.getElementById('deleteModal');
            if (event.target === createModal) {
                createModal.style.display = 'none';
            }
            if (event.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
