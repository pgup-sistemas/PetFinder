<?php
// CLI script para disparo de alertas de busca

if (php_sapi_name() !== 'cli') {
    exit("Este script deve ser executado somente via linha de comando." . PHP_EOL);
}

require_once __DIR__ . '/../config.php';

// Ajustes para execução prolongada
set_time_limit(0);
ini_set('memory_limit', '512M');

echo '[' . date('Y-m-d H:i:s') . "] Iniciando processamento de alertas..." . PHP_EOL;

$alertaModel = new Alerta();
$anuncioModel = new Anuncio();
$usuarioModel = new Usuario();
$controller = new AlertaController($alertaModel, $anuncioModel, $usuarioModel);

$limit = 100;
$alertas = $alertaModel->listDueAlerts($limit);
$total = count($alertas);
$enviados = 0;

if ($total === 0) {
    echo 'Nenhum alerta pendente para processamento.' . PHP_EOL;
    exit(0);
}

echo "Encontrados {$total} alertas aptos para disparo." . PHP_EOL;

foreach ($alertas as $alerta) {
    $id = $alerta['id'];
    try {
        $resultado = $controller->dispararResumo($alerta);
        if ($resultado) {
            $enviados++;
            echo "[#{$id}] E-mail enviado." . PHP_EOL;
        } else {
            echo "[#{$id}] Nenhum anúncio relevante encontrado ou e-mail não enviado." . PHP_EOL;
        }
    } catch (Throwable $e) {
        error_log('[process_alerts] Falha ao processar alerta ' . $id . ': ' . $e->getMessage());
        echo "[#{$id}] Erro: " . $e->getMessage() . PHP_EOL;
    }
}

echo "Processo finalizado. Alertas enviados: {$enviados} / {$total}." . PHP_EOL;
exit(0);
