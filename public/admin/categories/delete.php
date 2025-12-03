<?php
require_once '../auth.php';
use App\Models\Category;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$categoryModel = new Category();
$categoryModel->deleteCategory(intval($_GET['id']));
header('Location: index.php?success=deleted');
exit;
?>
