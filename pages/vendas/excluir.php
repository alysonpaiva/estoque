<?php
/**
 * Página de Exclusão de Vendas
 * Sistema de Controle de Estoque - Pizzaria
 */

require_once '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $id = (int)$_POST['id'];
        $venda = Venda::buscarPorId($id);
        
        if ($venda) {
            if ($venda->excluir()) {
                header('Location: listar.php?sucesso=Venda excluída com sucesso!');
            } else {
                header('Location: listar.php?erro=Erro ao excluir venda. Tente novamente.');
            }
        } else {
            header('Location: listar.php?erro=Venda não encontrada.');
        }
    } catch (Exception $e) {
        header('Location: listar.php?erro=Erro: ' . urlencode($e->getMessage()));
    }
} else {
    header('Location: listar.php?erro=Requisição inválida.');
}

exit;
?>

