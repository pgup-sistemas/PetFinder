<?php
require_once __DIR__ . '/../config.php';

$pageTitle = 'Doar - PetFinder';

$doacaoController = new DoacaoController();
$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Falha na validação do formulário. Recarregue a página.';
    } else {
        $resultado = $doacaoController->criar($_POST);

        if (!empty($resultado['success'])) {
            $successMessage = 'Sua doação foi registrada! Em instantes você receberá instruções de pagamento.';
        } elseif (!empty($resultado['errors'])) {
            $errors = $resultado['errors'];
        }
    }
}

$resumo = $doacaoController->resumoDashboard();
$metaAtual = $doacaoController->metaAtual();
$mural = $doacaoController->mural(6);

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="display-4">💚</div>
                        <div>
                            <h1 class="h3 fw-bold mb-1">Ajude a Manter o PetFinder Gratuito</h1>
                            <p class="text-muted mb-0">Sua contribuição mantém o site online, gratuito e sem anúncios.</p>
                        </div>
                    </div>

                    <?php if (!empty($successMessage)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i><?php echo sanitize($successMessage); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <h6 class="fw-bold">Corrija os seguintes pontos:</h6>
                            <ul class="mb-0">
                                <?php foreach ($errors as $erro): ?>
                                    <li><?php echo sanitize($erro); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="mt-4">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="mb-4">
                            <label class="form-label fw-bold">Escolha um valor</label>
                            <div class="row g-2">
                                <?php foreach ([5, 10, 20, 50] as $valor): ?>
                                    <div class="col-6 col-md-3">
                                        <input type="radio" class="btn-check" name="valor" id="valor_<?php echo $valor; ?>" value="<?php echo $valor; ?>">
                                        <label class="btn btn-outline-success w-100" for="valor_<?php echo $valor; ?>">
                                            R$ <?php echo $valor; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                                <div class="col-12 mt-2">
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="number" class="form-control" name="valor" min="<?php echo MIN_DONATION_AMOUNT; ?>" step="1" placeholder="Outro valor (mínimo R$ <?php echo MIN_DONATION_AMOUNT; ?>)">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Método de pagamento</label>
                            <div class="row g-2">
                                <?php $metodos = ['PIX' => 'pix', 'Cartão de Crédito' => 'cartao', 'Cartão de Débito' => 'debito', 'Boleto' => 'boleto']; ?>
                                <?php foreach ($metodos as $label => $valor): ?>
                                    <div class="col-6 col-md-3">
                                        <input type="radio" class="btn-check" name="metodo_pagamento" id="metodo_<?php echo $valor; ?>" value="<?php echo $valor; ?>">
                                        <label class="btn btn-outline-primary w-100" for="metodo_<?php echo $valor; ?>"><?php echo $label; ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" role="switch" id="recorrente" name="recorrente">
                            <label class="form-check-label" for="recorrente">Quero doar mensalmente</label>
                        </div>

                        <h5 class="fw-bold mb-3">Seus Dados</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome</label>
                                <input type="text" class="form-control" name="nome_doador" placeholder="Seu nome completo">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email_doador" placeholder="seu@email.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CPF</label>
                                <input type="text" class="form-control" name="cpf_doador" placeholder="000.000.000-00">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Mensagem (opcional)</label>
                                <textarea class="form-control" name="mensagem" rows="3" placeholder="Deixe um recado para nossa equipe"></textarea>
                            </div>
                        </div>

                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" value="1" id="exibirMural" name="exibir_mural" checked>
                            <label class="form-check-label" for="exibirMural">
                                Quero aparecer no mural de doadores ❤️
                            </label>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100 mt-4">
                            <i class="bi bi-heart-fill me-2"></i>Doar agora
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Meta Mensal</h5>
                    <?php if ($metaAtual): ?>
                        <?php
                            $valorMeta = (float)($metaAtual['valor_meta'] ?? 0);
                            $valorArrecadado = (float)($metaAtual['valor_arrecadado'] ?? 0);
                            $percentual = $valorMeta > 0 ? min(100, round(($valorArrecadado / $valorMeta) * 100)) : 0;
                        ?>
                        <p class="text-muted mb-2"><?php echo sanitize($metaAtual['descricao'] ?? ''); ?></p>
                        <div class="progress mb-2" style="height: 12px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $percentual; ?>%" aria-valuenow="<?php echo $percentual; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span class="fw-semibold">Arrecadado: <?php echo formatMoney($valorArrecadado); ?></span>
                            <span class="text-muted">Meta: <?php echo formatMoney($valorMeta); ?></span>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Ainda não há meta ativa. Sua doação será essencial para manter o projeto.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Mural de Doadores</h5>
                    <?php if (empty($mural)): ?>
                        <p class="text-muted">Seja o primeiro a aparecer por aqui! 💚</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($mural as $doacao): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="fw-bold mb-1"><?php echo sanitize($doacao['nome_doador'] ?? 'Apoiador anônimo'); ?></h6>
                                            <?php if (!empty($doacao['mensagem'])): ?>
                                                <p class="mb-1 text-muted small"><?php echo sanitize($doacao['mensagem']); ?></p>
                                            <?php endif; ?>
                                            <span class="badge bg-light text-dark">
                                                <?php echo formatMoney($doacao['valor']); ?> · <?php echo date('d/m/Y', strtotime($doacao['data_doacao'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.btn-outline-success,
.btn-outline-primary {
    border-radius: 12px;
}

.btn-outline-success:hover,
.btn-outline-success:checked,
.btn-check:checked + .btn-outline-success {
    color: #fff;
    background-color: #00a86b;
    border-color: #00a86b;
}

.btn-outline-primary:hover,
.btn-check:checked + .btn-outline-primary {
    color: #fff;
    background-color: #2196F3;
    border-color: #2196F3;
}

.card {
    border-radius: 16px;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
