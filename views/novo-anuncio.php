<?php
require_once __DIR__ . '/../config.php';

$pageTitle = 'Publicar Anúncio - PetFinder';

// Requer login
requireLogin();

$errors = [];
$formData = [];
$step = (int)($_GET['step'] ?? 1);
$controller = new AnuncioController();

$cacheTmpDir = UPLOAD_PATH . '/tmp/anuncios';

// Processa o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Erro de validação do formulário. Atualize a página e tente novamente.';
    } else {
        // Salva dados na sessão para multi-step
        $_SESSION['anuncio_temp'] = array_merge($_SESSION['anuncio_temp'] ?? [], $_POST);

        if (!empty($_FILES['fotos']) && is_array($_FILES['fotos']['name'])) {
            $hasUpload = false;
            foreach ($_FILES['fotos']['error'] as $err) {
                if ($err !== UPLOAD_ERR_NO_FILE) {
                    $hasUpload = true;
                    break;
                }
            }

            if ($hasUpload) {
                if (!is_dir($cacheTmpDir)) {
                    mkdir($cacheTmpDir, 0755, true);
                }

                foreach (($_SESSION['anuncio_temp_fotos'] ?? []) as $old) {
                    if (!empty($old['path']) && file_exists($old['path'])) {
                        @unlink($old['path']);
                    }
                }

                $_SESSION['anuncio_temp_fotos'] = [];

                $fileCount = count($_FILES['fotos']['name']);
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($_FILES['fotos']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    $origName = (string)$_FILES['fotos']['name'][$i];
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    $tmpName = $_FILES['fotos']['tmp_name'][$i];

                    $tmpFilename = uniqid('tmp_', true) . ($ext ? ('.' . $ext) : '');
                    $destPath = $cacheTmpDir . '/' . $tmpFilename;

                    if (is_uploaded_file($tmpName)) {
                        @move_uploaded_file($tmpName, $destPath);
                    } else {
                        @rename($tmpName, $destPath);
                    }

                    if (!file_exists($destPath)) {
                        continue;
                    }

                    $_SESSION['anuncio_temp_fotos'][] = [
                        'name' => $origName,
                        'type' => $_FILES['fotos']['type'][$i] ?? 'application/octet-stream',
                        'size' => (int)($_FILES['fotos']['size'][$i] ?? 0),
                        'path' => $destPath,
                        'relative' => 'tmp/anuncios/' . $tmpFilename
                    ];
                }
            }
        }

        if (isset($_POST['finalizar'])) {
            $files = $_FILES;
            $hasFiles = !empty($files['fotos']) && is_array($files['fotos']['name']) && count(array_filter($files['fotos']['name'])) > 0;

            if (!$hasFiles && !empty($_SESSION['anuncio_temp_fotos'])) {
                $validSessionFotos = [];
                foreach ($_SESSION['anuncio_temp_fotos'] as $foto) {
                    if (!empty($foto['path']) && file_exists($foto['path'])) {
                        $validSessionFotos[] = $foto;
                    }
                }

                $_SESSION['anuncio_temp_fotos'] = $validSessionFotos;

                if (empty($validSessionFotos)) {
                    $errors[] = 'As fotos anexadas expiraram. Por favor, selecione as fotos novamente.';
                    $step = 2;
                }
            }

            if (!$hasFiles && empty($errors) && !empty($_SESSION['anuncio_temp_fotos'])) {
                $files['fotos'] = [
                    'name' => [],
                    'type' => [],
                    'tmp_name' => [],
                    'error' => [],
                    'size' => []
                ];

                foreach ($_SESSION['anuncio_temp_fotos'] as $foto) {
                    $files['fotos']['name'][] = $foto['name'];
                    $files['fotos']['type'][] = $foto['type'];
                    $files['fotos']['tmp_name'][] = $foto['path'];
                    $files['fotos']['error'][] = UPLOAD_ERR_OK;
                    $files['fotos']['size'][] = $foto['size'];
                }
            }

            if (!empty($errors)) {
                // Não prossegue para salvar enquanto existirem erros locais
                // (ex.: cache de fotos expirado)
                $result = ['success' => false];
            } else {
                $result = $controller->create($_SESSION['anuncio_temp'], $files);
            }

            if (!empty($result['success'])) {
                foreach (($_SESSION['anuncio_temp_fotos'] ?? []) as $old) {
                    if (!empty($old['path']) && file_exists($old['path'])) {
                        @unlink($old['path']);
                    }
                }
                unset($_SESSION['anuncio_temp']);
                unset($_SESSION['anuncio_temp_fotos']);
                setFlashMessage('Anúncio publicado com sucesso!', MSG_SUCCESS);
                redirect('/anuncio.php?id=' . $result['id']);
            } else {
                $errors = $result['errors'];
            }
        } else {
            // Avança para próximo passo
            $step = (int)($_POST['next_step'] ?? ($step + 1));
        }
    }
}

$formData = $_SESSION['anuncio_temp'] ?? [];

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Início</a></li>
            <li class="breadcrumb-item active">Publicar Anúncio</li>
        </ol>
    </nav>
    
    <!-- Título -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold mb-3">📢 Publicar Anúncio</h1>
        <p class="lead text-muted">Preencha os dados em 3 passos simples</p>
    </div>
    
    <!-- Stepper -->
    <div class="stepper mb-5">
        <div class="row">
            <div class="col-md-4">
                <div class="step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                    <div class="step-icon">
                        <?php echo $step > 1 ? '<i class="bi bi-check-lg"></i>' : '1'; ?>
                    </div>
                    <div class="step-label">O que aconteceu?</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                    <div class="step-icon">
                        <?php echo $step > 2 ? '<i class="bi bi-check-lg"></i>' : '2'; ?>
                    </div>
                    <div class="step-label">Fotos e Detalhes</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">
                    <div class="step-icon">3</div>
                    <div class="step-label">Onde e Contato</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Card do Formulário -->
            <div class="card shadow border-0">
                <div class="card-body p-4 p-md-5">
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <strong>Ops! Corrija os seguintes erros:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo sanitize($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" id="anuncioForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <?php if ($step == 1): ?>
                            <!-- PASSO 1: Tipo -->
                            <div class="step-content">
                                <h3 class="mb-4">O que aconteceu?</h3>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input type="radio" 
                                               class="btn-check" 
                                               name="tipo" 
                                               id="tipo_perdido" 
                                               value="perdido"
                                               <?php echo ($formData['tipo'] ?? '') === 'perdido' ? 'checked' : ''; ?>
                                               required>
                                        <label class="btn btn-tipo btn-outline-danger w-100 p-4" for="tipo_perdido">
                                            <div class="tipo-icon">🔴</div>
                                            <h4>PERDI MEU PET</h4>
                                            <p class="mb-0 small">Meu animal de estimação está perdido</p>
                                        </label>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <input type="radio" 
                                               class="btn-check" 
                                               name="tipo" 
                                               id="tipo_encontrado" 
                                               value="encontrado"
                                               <?php echo ($formData['tipo'] ?? '') === 'encontrado' ? 'checked' : ''; ?>
                                               required>
                                        <label class="btn btn-tipo btn-outline-success w-100 p-4" for="tipo_encontrado">
                                            <div class="tipo-icon">🟢</div>
                                            <h4>ENCONTREI UM PET</h4>
                                            <p class="mb-0 small">Encontrei um animal perdido</p>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="text-end mt-4">
                                    <button type="submit" name="next_step" value="2" class="btn btn-primary btn-lg">
                                        Próximo <i class="bi bi-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                            
                        <?php elseif ($step == 2): ?>
                            <!-- PASSO 2: Fotos e Detalhes -->
                            <div class="step-content">
                                <h3 class="mb-4">Fotos e Detalhes</h3>
                                
                                <!-- Upload de Fotos -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        📸 Adicione até 2 fotos
                                    </label>

                                    <?php if (!empty($_SESSION['anuncio_temp_fotos'])): ?>
                                        <div class="alert alert-info">
                                            Fotos já adicionadas: <?php echo count($_SESSION['anuncio_temp_fotos']); ?>
                                        </div>

                                        <div class="d-flex gap-2 flex-wrap mb-3">
                                            <?php foreach ($_SESSION['anuncio_temp_fotos'] as $foto): ?>
                                                <?php if (!empty($foto['relative']) && !empty($foto['path']) && file_exists($foto['path'])): ?>
                                                    <img src="<?php echo BASE_URL; ?>/uploads/<?php echo sanitize($foto['relative']); ?>" alt="Foto" style="width: 96px; height: 96px; object-fit: cover; border-radius: 12px;">
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="upload-area" id="uploadArea">
                                        <input type="file" 
                                               name="fotos[]" 
                                               id="fotos" 
                                               accept="image/*" 
                                               multiple 
                                               max="2"
                                               style="display: none;">
                                        
                                        <div class="upload-placeholder" onclick="document.getElementById('fotos').click()">
                                            <i class="bi bi-camera-fill"></i>
                                            <p>Clique para adicionar fotos</p>
                                            <small class="text-muted">Máximo 2 fotos, 2MB cada</small>
                                        </div>
                                        
                                        <div id="preview" class="preview-grid"></div>
                                    </div>
                                </div>
                                
                                <!-- Espécie -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        Qual animal? <span class="text-danger">*</span>
                                    </label>
                                    <div class="btn-group-especies">
                                        <input type="radio" class="btn-check" name="especie" id="esp_cachorro" value="cachorro" <?php echo (($formData['especie'] ?? '') === 'cachorro') ? 'checked' : ''; ?> required>
                                        <label class="btn btn-outline-primary" for="esp_cachorro">
                                            🐕 Cachorro
                                        </label>
                                        
                                        <input type="radio" class="btn-check" name="especie" id="esp_gato" value="gato" <?php echo (($formData['especie'] ?? '') === 'gato') ? 'checked' : ''; ?> required>
                                        <label class="btn btn-outline-primary" for="esp_gato">
                                            🐈 Gato
                                        </label>
                                        
                                        <input type="radio" class="btn-check" name="especie" id="esp_ave" value="ave" <?php echo (($formData['especie'] ?? '') === 'ave') ? 'checked' : ''; ?> required>
                                        <label class="btn btn-outline-primary" for="esp_ave">
                                            🦜 Ave
                                        </label>
                                        
                                        <input type="radio" class="btn-check" name="especie" id="esp_outro" value="outro" <?php echo (($formData['especie'] ?? '') === 'outro') ? 'checked' : ''; ?> required>
                                        <label class="btn btn-outline-primary" for="esp_outro">
                                            🐾 Outro
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Tamanho -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        Tamanho? <span class="text-danger">*</span>
                                    </label>
                                    <div class="btn-group-tamanhos">
                                        <input type="radio" class="btn-check" name="tamanho" id="tam_pequeno" value="pequeno" <?php echo (($formData['tamanho'] ?? '') === 'pequeno') ? 'checked' : ''; ?> required>
                                        <label class="btn btn-outline-secondary" for="tam_pequeno">
                                            Pequeno
                                        </label>
                                        
                                        <input type="radio" class="btn-check" name="tamanho" id="tam_medio" value="medio" <?php echo (($formData['tamanho'] ?? '') === 'medio') ? 'checked' : ''; ?> required>
                                        <label class="btn btn-outline-secondary" for="tam_medio">
                                            Médio
                                        </label>
                                        
                                        <input type="radio" class="btn-check" name="tamanho" id="tam_grande" value="grande" <?php echo (($formData['tamanho'] ?? '') === 'grande') ? 'checked' : ''; ?> required>
                                        <label class="btn btn-outline-secondary" for="tam_grande">
                                            Grande
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Nome (opcional) -->
                                <div class="mb-3">
                                    <label for="nome_pet" class="form-label">Nome do Pet</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="nome_pet" 
                                           name="nome_pet"
                                           value="<?php echo sanitize($formData['nome_pet'] ?? ''); ?>"
                                           placeholder="Ex: Rex, Mimi...">
                                </div>
                                
                                <!-- Raça e Cor -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="raca" class="form-label">Raça</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="raca" 
                                               name="raca"
                                               value="<?php echo sanitize($formData['raca'] ?? ''); ?>"
                                               placeholder="Ex: Labrador, SRD...">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="cor" class="form-label">Cor</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="cor" 
                                               name="cor"
                                               value="<?php echo sanitize($formData['cor'] ?? ''); ?>"
                                               placeholder="Ex: Caramelo, Preto...">
                                    </div>
                                </div>
                                
                                <!-- Descrição -->
                                <div class="mb-4">
                                    <label for="descricao" class="form-label">
                                        Descreva o pet
                                    </label>
                                    <textarea class="form-control" 
                                              id="descricao" 
                                              name="descricao" 
                                              rows="4"
                                              placeholder="Características, marcas, comportamento..."><?php echo sanitize($formData['descricao'] ?? ''); ?></textarea>
                                    <small class="text-muted">Mínimo 20 caracteres</small>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="submit" name="next_step" value="1" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Voltar
                                    </button>
                                    <button type="submit" name="next_step" value="3" class="btn btn-primary btn-lg">
                                        Próximo <i class="bi bi-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                            
                        <?php elseif ($step == 3): ?>
                            <!-- PASSO 3: Localização e Contato -->
                            <div class="step-content">
                                <h3 class="mb-4">Onde e Contato</h3>
                                
                                <!-- Data -->
                                <div class="mb-3">
                                    <label for="data_ocorrido" class="form-label fw-bold">
                                        Quando? <span class="text-danger">*</span>
                                    </label>
                                    <?php $dataOcorridoValue = $formData['data_ocorrido'] ?? date('Y-m-d'); ?>
                                    <input type="date" 
                                           class="form-control" 
                                           id="data_ocorrido" 
                                           name="data_ocorrido"
                                           value="<?php echo htmlspecialchars($dataOcorridoValue, ENT_QUOTES, 'UTF-8'); ?>"
                                           max="<?php echo date('Y-m-d'); ?>"
                                           required>
                                </div>
                                
                                <!-- CEP -->
                                <div class="mb-3">
                                    <label for="cep" class="form-label fw-bold">CEP</label>
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control" 
                                               id="cep" 
                                               name="cep"
                                               inputmode="numeric"
                                               pattern="\d*"
                                               data-mask="cep"
                                               placeholder="00000-000"
                                               maxlength="9"
                                               value="<?php echo sanitize($formData['cep'] ?? ''); ?>">
                                        
                                        <button class="btn btn-outline-primary" type="button" id="btn-buscar-cep" onclick="buscarCEP()">
                                            Buscar
                                        </button>
                                        <button class="btn btn-outline-secondary" type="button" id="btn-gps" onclick="usarGPS()">
                                            <i class="bi bi-geo-alt"></i> Usar GPS
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Endereço -->
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="endereco" class="form-label">Endereço</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="endereco" 
                                               name="endereco_completo"
                                               placeholder="Rua, Avenida..."
                                               value="<?php echo sanitize($formData['endereco_completo'] ?? ''); ?>"
                                               required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="bairro" class="form-label">Bairro</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="bairro" 
                                               name="bairro"
                                               value="<?php echo sanitize($formData['bairro'] ?? ''); ?>"
                                               required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="cidade" class="form-label">Cidade</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="cidade" 
                                               name="cidade"
                                               value="<?php echo sanitize($formData['cidade'] ?? ''); ?>"
                                               required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="estado" class="form-label">UF</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="estado" 
                                               name="estado"
                                               maxlength="2"
                                               value="<?php echo sanitize($formData['estado'] ?? ''); ?>"
                                               required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Marque no mapa</label>
                                    <div id="mapPicker" class="petfinder-map"></div>
                                    <small class="text-muted">Clique no mapa para ajustar a posição (ou arraste o marcador).</small>
                                </div>
                                
                                <!-- Ponto de Referência -->
                                <div class="mb-3">
                                    <label for="ponto_referencia" class="form-label">Ponto de Referência</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="ponto_referencia" 
                                           name="ponto_referencia"
                                           placeholder="Ex: Próximo ao Shopping..."
                                           value="<?php echo sanitize($formData['ponto_referencia'] ?? ''); ?>">
                                </div>
                                
                                <!-- Contatos -->
                                <hr class="my-4">
                                <h5 class="mb-3">Seus Contatos</h5>
                                
                                <div class="mb-3">
                                    <label for="whatsapp" class="form-label fw-bold">
                                        WhatsApp <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="whatsapp" 
                                           name="whatsapp"
                                           placeholder="(00) 00000-0000"
                                           value="<?php echo sanitize($formData['whatsapp'] ?? ''); ?>"
                                           required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="telefone_contato" class="form-label">Telefone Fixo</label>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="telefone_contato" 
                                           name="telefone_contato"
                                           placeholder="(00) 0000-0000"
                                           value="<?php echo sanitize($formData['telefone_contato'] ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="email_contato" class="form-label">E-mail de Contato</label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email_contato" 
                                           name="email_contato"
                                           placeholder="seu@email.com"
                                           value="<?php echo sanitize($formData['email_contato'] ?? ''); ?>">
                                </div>
                                
                                <!-- Recompensa -->
                                <div class="mb-4">
                                    <label for="recompensa" class="form-label">Oferece Recompensa?</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="recompensa" 
                                           name="recompensa"
                                           placeholder="Ex: R$ 100,00"
                                           value="<?php echo sanitize($formData['recompensa'] ?? ''); ?>">
                                </div>
                                
                                <div class="mt-4 d-flex justify-content-between align-items-center">
                                    <button type="submit" name="next_step" value="2" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Voltar
                                    </button>
                                    <button type="submit" name="finalizar" value="1" class="btn btn-success btn-lg">
                                        <i class="bi bi-check-circle"></i> Publicar Anúncio
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="latitude" id="latitude" value="<?php echo sanitize($formData['latitude'] ?? ''); ?>">
                            <input type="hidden" name="longitude" id="longitude" value="<?php echo sanitize($formData['longitude'] ?? ''); ?>">
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stepper {
    position: relative;
}

.stepper::before {
    content: '';
    position: absolute;
    top: 30px;
    left: 25%;
    right: 25%;
    height: 2px;
    background: #e0e0e0;
    z-index: 0;
}

.step {
    text-align: center;
    position: relative;
    z-index: 1;
}

.step-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: white;
    border: 3px solid #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 1.5em;
    font-weight: bold;
    color: #999;
}

.step.active .step-icon {
    background: #2196F3;
    border-color: #2196F3;
    color: white;
}

.step.completed .step-icon {
    background: #4CAF50;
    border-color: #4CAF50;
    color: white;
}

.btn-tipo {
    height: 180px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.btn-tipo:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.tipo-icon {
    font-size: 3em;
    margin-bottom: 10px;
}

.upload-area {
    border: 2px dashed #ddd;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s;
}

.upload-area:hover {
    border-color: #2196F3;
    background: #f8f9fa;
}

.upload-placeholder {
    cursor: pointer;
    padding: 40px;
}

.upload-placeholder i {
    font-size: 3em;
    color: #2196F3;
    display: block;
    margin-bottom: 15px;
}

.preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.preview-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
}

.preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-item .remove-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(255,0,0,0.8);
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
}

.btn-group-especies,
.btn-group-tamanhos {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-group-especies .btn,
.btn-group-tamanhos .btn {
    flex: 1;
    min-width: 120px;
}

.petfinder-map {
    height: 280px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid rgba(0,0,0,0.08);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('mapPicker') && window.PetFinderMap) {
        window.__petfinderMapPicker = window.PetFinderMap.init({
            containerId: 'mapPicker',
            latInputId: 'latitude',
            lngInputId: 'longitude'
        });

        if (window.__petfinderMapPicker && window.__petfinderMapPicker.fitToPoint) {
            window.__petfinderMapPicker.fitToPoint();
        }
    }

    const cepInput = document.getElementById('cep');
    if (cepInput) {
        let cepAutoLookupTimer = null;
        let lastCepLookedUp = null;

        cepInput.addEventListener('input', function () {
            const digits = (cepInput.value || '').replace(/\D/g, '');

            if (cepAutoLookupTimer) {
                clearTimeout(cepAutoLookupTimer);
            }

            if (digits.length !== 8) {
                lastCepLookedUp = null;
                return;
            }

            if (lastCepLookedUp === digits) {
                return;
            }

            cepAutoLookupTimer = setTimeout(function () {
                lastCepLookedUp = digits;
                buscarCEP();
            }, 450);
        });

        cepInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                buscarCEP();
            }
        });
    }
});

// Preview de fotos
document.getElementById('fotos')?.addEventListener('change', function(e) {
    const preview = document.getElementById('preview');
    const files = Array.from(e.target.files);
    
    // Limita a 2 fotos
    if (files.length > 2) {
        alert('Máximo 2 fotos permitidas!');
        e.target.value = '';
        return;
    }
    
    preview.innerHTML = '';
    
    files.forEach((file, index) => {
        if (file.size > 2 * 1024 * 1024) {
            alert(`Foto ${index + 1} muito grande! Máximo 2MB`);
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="remove-btn" onclick="removePhoto(${index})">×</button>
            `;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
});

// Buscar CEP
async function buscarCEP() {
    const cepInput = document.getElementById('cep');
    const cepButton = document.getElementById('btn-buscar-cep');
    const cep = cepInput.value.replace(/\D/g, '');

    if (cep.length !== 8) {
        alert('Informe um CEP válido com 8 dígitos.');
        cepInput.focus();
        return;
    }

    const originalLabel = cepButton.dataset.originalLabel || cepButton.innerHTML;
    cepButton.dataset.originalLabel = originalLabel;
    cepButton.disabled = true;
    cepButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Buscando';

    try {
        const response = await fetch(`<?php echo BASE_URL; ?>/api/cep.php?cep=${cep}`, {
            headers: {
                'Accept': 'application/json'
            }
        });

        const payload = await response.json();

        if (!response.ok || !payload.success) {
            const message = payload?.message || 'CEP não encontrado ou serviço indisponível.';
            throw new Error(message);
        }

        const data = payload.data || {};

        preencherCamposEndereco({
            logradouro: data.logradouro,
            bairro: data.bairro,
            cidade: data.cidade,
            estado: data.estado,
            cep: data.cep
        }, { limparCoordenadas: true });

        if (!data.logradouro) {
            const enderecoEl = document.getElementById('endereco');
            if (enderecoEl && !String(enderecoEl.value || '').trim()) {
                enderecoEl.focus();
            }
            alert('CEP encontrado, mas sem rua/logradouro (CEP geral). Preencha a rua manualmente.');
        }

        if (!document.getElementById('whatsapp').value && data.ddd) {
            document.getElementById('whatsapp').value = `(${data.ddd}) `;
        }
    } catch (error) {
        alert(error.message || 'Erro ao buscar CEP. Tente novamente.');
    } finally {
        cepButton.disabled = false;
        cepButton.innerHTML = cepButton.dataset.originalLabel;
    }
}

// Usar GPS
function usarGPS() {
    if (!navigator.geolocation) {
        alert('Geolocalização não suportada pelo navegador!');
        return;
    }

    navigator.geolocation.getCurrentPosition(onGeolocationSuccess, onGeolocationError, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
    });
}

function onGeolocationSuccess(position) {
    const lat = position.coords.latitude;
    const lng = position.coords.longitude;
    const gpsButton = document.getElementById('btn-gps');

    const originalLabel = gpsButton.dataset.originalLabel || gpsButton.innerHTML;
    gpsButton.dataset.originalLabel = originalLabel;
    gpsButton.disabled = true;
    gpsButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Carregando';

    fetch(`<?php echo BASE_URL; ?>/api/geocode.php?lat=${lat}&lng=${lng}`, {
        headers: { 'Accept': 'application/json' }
    })
        .then(async response => {
            const payload = await response.json();

            if (!response.ok || !payload.success) {
                const message = payload?.message || 'Não foi possível converter sua localização em endereço.';
                throw new Error(message);
            }

            preencherCamposEndereco(payload.data || {}, { manterCep: true });
        })
        .catch(error => {
            alert(error.message || 'Erro ao obter endereço a partir da localização.');
        })
        .finally(() => {
            gpsButton.disabled = false;
            gpsButton.innerHTML = gpsButton.dataset.originalLabel;
        });
}

function onGeolocationError(error) {
    const mensagens = {
        1: 'Permita o acesso à sua localização para preencher os dados automaticamente.',
        2: 'Sua localização não pôde ser determinada. Tente novamente mais tarde.',
        3: 'Tempo limite atingido ao tentar obter a localização.'
    };

    alert(mensagens[error.code] || 'Não foi possível obter sua localização.');
}

function preencherCamposEndereco(data = {}, opcoes = {}) {
    if (!opcoes.manterCep) {
        document.getElementById('cep').value = data.cep ? formatarCEP(data.cep) : document.getElementById('cep').value;
    }

    const enderecoEl = document.getElementById('endereco');
    const bairroEl = document.getElementById('bairro');
    const cidadeEl = document.getElementById('cidade');
    const estadoEl = document.getElementById('estado');

    if (data.logradouro) {
        enderecoEl.value = data.logradouro;
    }
    if (data.bairro) {
        bairroEl.value = data.bairro;
    }
    if (data.cidade) {
        cidadeEl.value = data.cidade;
    }
    if (data.estado) {
        estadoEl.value = data.estado;
    }

    if (data.latitude && data.longitude) {
        document.getElementById('latitude').value = data.latitude;
        document.getElementById('longitude').value = data.longitude;

        if (window.__petfinderMapPicker && window.__petfinderMapPicker.setPoint) {
            window.__petfinderMapPicker.setPoint(Number(data.latitude), Number(data.longitude));
        }
    } else if (opcoes.limparCoordenadas) {
        document.getElementById('latitude').value = '';
        document.getElementById('longitude').value = '';
    }
}

function formatarCEP(cep) {
    const apenasNumeros = (cep || '').replace(/\D/g, '');
    if (apenasNumeros.length !== 8) {
        return cep;
    }
    return `${apenasNumeros.substring(0, 5)}-${apenasNumeros.substring(5)}`;
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>