<?php
$pageTitle = 'Désigner un chef de classe';
ob_start();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="h2">Désigner un chef de classe</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?= BASE_URL ?>admin/classes" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Classe: <?= htmlspecialchars($classe['intitule']) ?> <?= $classe['code'] ? '(' . htmlspecialchars($classe['code']) . ')' : '' ?></h6>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>admin/classes/<?= (int)$classe['id'] ?>/designer-chef/nommer" method="POST">
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email du chef de classe</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-user-check"></i> Nommer chef de classe</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>