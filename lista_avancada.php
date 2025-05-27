<?php
// Incluir arquivo de conexão
require_once 'conexao.php';

// Inicializar variáveis
$mensagem = "";
$contatos = [];

// Processar exclusão de contato
if (isset($_GET['excluir']) && !empty($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    
    // Conectar ao banco de dados
    $conexao = conectarBD();
    
    // Preparar e executar a query de exclusão
    $stmt = $conexao->prepare("DELETE FROM contatos WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    // Verificar se a exclusão foi bem-sucedida
    if ($stmt->execute()) {
        $mensagem = "<div class='alert alert-success'>Contato excluído com sucesso!</div>";
    } else {
        $mensagem = "<div class='alert alert-danger'>Erro ao excluir contato: " . $stmt->error . "</div>";
    }
    
    // Fechar statement
    $stmt->close();
    fecharConexao($conexao);
}

// Conectar ao banco de dados
$conexao = conectarBD();

// Buscar todos os contatos
$sql = "SELECT id, nome, sobrenome, telefone FROM contatos ORDER BY nome, sobrenome";
$resultado = $conexao->query($sql);

// Verificar se há contatos
if ($resultado->num_rows > 0) {
    // Armazenar contatos em um array
    while ($row = $resultado->fetch_assoc()) {
        $contatos[] = $row;
    }
} else {
    $mensagem = "<div class='alert alert-info'>Nenhum contato cadastrado.</div>";
}

// Fechar conexão
fecharConexao($conexao);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CICA - Contatos Integrados Condominio Aruã</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Cabeçalho com logo -->
    <header class="app-header">
        <div class="container">
            <div class="logo-container">
                <img src="assets\img\LogoPadrão-750px.png" alt="CICA Logo">
            </div>
            <p>CICA - Contatos Integrados Condominio Aruãs</p>
        </div>
    </header>
    
    <div class="app-container">
        <!-- Exibir mensagens -->
        <?php echo $mensagem; ?>
        
        <div class="app-card card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-cogs me-2"></i>Gerenciamento Avançado de Contatos</h5>
                <a href="index.php" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Voltar
                </a>
            </div>
            <div class="card-body">
                <?php if (count($contatos) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Sobrenome</th>
                                    <th>Telefone</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contatos as $contato): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($contato['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($contato['sobrenome']); ?></td>
                                        <td><?php echo htmlspecialchars($contato['telefone']); ?></td>
                                        <td class="action-buttons">
                                            <a href="editar.php?id=<?php echo $contato['id']; ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-pencil-alt me-1"></i>Editar
                                            </a>
                                            <a href="detalhes.php?telefone=<?php echo urlencode($contato['telefone']); ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye me-1"></i>Visualizar
                                            </a>
                                            <a href="javascript:void(0);" onclick="confirmarExclusao(<?php echo $contato['id']; ?>, '<?php echo addslashes($contato['nome'] . ' ' . $contato['sobrenome']); ?>')" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash-alt me-1"></i>Excluir
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="lead">Nenhum contato cadastrado ainda.</p>
                        <a href="index.php" class="btn btn-primary mt-2">
                            <i class="fas fa-user-plus me-2"></i>Adicionar Contato
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i>Adicionar Novo Contato
            </a>
            <a href="lista_contatos.php" class="btn btn-outline-info ms-2">
                <i class="fas fa-list me-2"></i>Lista Simples
            </a>
        </div>
    </div>
    
    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="modalConfirmacao" tabindex="-1" aria-labelledby="modalConfirmacaoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfirmacaoLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Tem certeza que deseja excluir o contato <span id="nomeContato" class="fw-bold"></span>?
                    <p class="text-muted small mt-2">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="#" id="btnConfirmarExclusao" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-1"></i>Excluir
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Rodapé -->
    <footer class="app-footer">
        <div class="container">
            <p>Henry Sawaya &copy; <?php echo date('Y'); ?> - Todos os direitos reservados</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Função para confirmar exclusão
        function confirmarExclusao(id, nome) {
            document.getElementById('nomeContato').textContent = nome;
            document.getElementById('btnConfirmarExclusao').href = 'lista_avancada.php?excluir=' + id;
            
            // Exibir modal de confirmação
            var modal = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
            modal.show();
        }
    </script>
</body>
</html>
