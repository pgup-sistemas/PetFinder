<?php
// Simple test runner for PetFinder business rules
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/AnuncioController.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';
require_once __DIR__ . '/../includes/auth.php';

$tests = [];
$results = ['passed' => 0, 'failed' => 0];

function addTest(string $name, callable $fn)
{
    global $tests;
    $tests[] = [$name, $fn];
}

function assertTrue($condition, $message = 'Assertion failed')
{
    if (!$condition) {
        throw new Exception($message);
    }
}

function assertEquals($expected, $actual, $message = 'Values are not equal')
{
    if ($expected != $actual) {
        throw new Exception($message . " (expected " . var_export($expected, true) . ", got " . var_export($actual, true) . ")");
    }
}

function assertStringContains(string $needle, string $haystack, string $message = 'String not found')
{
    if (strpos($haystack, $needle) === false) {
        throw new Exception($message . " (needle: '{$needle}')");
    }
}

class FakeAnuncioModel
{
    public $countActive = 0;
    public $canPublish = true;
    public $createdPayload = null;

    public function countActiveByUser(int $userId): int
    {
        return $this->countActive;
    }

    public function canPublishNewAd(int $userId): bool
    {
        return $this->canPublish;
    }

    public function create(array $data)
    {
        $this->createdPayload = $data;
        return 123;
    }
}

class FakeUsuarioModel
{
    private $user;

    public function __construct(array $user)
    {
        $this->user = $user;
    }

    public function findById(int $id)
    {
        return $this->user;
    }
}

class StubDatabase
{
    public $transactions = 0;

    public function beginTransaction()
    {
        $this->transactions++;
        return true;
    }

    public function commit()
    {
        return true;
    }

    public function rollback()
    {
        return true;
    }

    public function insert($table, $data)
    {
        return 1;
    }

    public function fetchAll($sql, $params = [])
    {
        return [];
    }

    public function query($sql, $params = [])
    {
        return true;
    }
}

class FakeAuthDatabase
{
    public $user;

    public function __construct(array $user)
    {
        $this->user = $user;
    }

    public function fetchOne($sql, $params = [])
    {
        if (stripos($sql, 'FROM usuarios') !== false) {
            $email = $params[0];
            if (strcasecmp($this->user['email'], $email) === 0 && $this->user['ativo']) {
                return $this->user;
            }
            return null;
        }
        return null;
    }

    public function update($table, $data, $where, $params = [])
    {
        if ($table === 'usuarios' && $where === 'id = ?') {
            $id = $params[0];
            if ($id == $this->user['id']) {
                $this->user = array_merge($this->user, $data);
            }
        }
        return true;
    }

    public function insert($table, $data)
    {
        return 1;
    }
}

addTest('Limite de anúncios ativos impede novas publicações', function () {
    $fakeAnuncioModel = new FakeAnuncioModel();
    $fakeAnuncioModel->countActive = MAX_ACTIVE_ADS_PER_USER;
    $fakeUsuarioModel = new FakeUsuarioModel([
        'id' => 1,
        'email_confirmado' => 1,
        'tentativas_login' => 0,
        'bloqueado_ate' => null
    ]);
    $db = new StubDatabase();

    $controller = new AnuncioController(
        $fakeAnuncioModel,
        $fakeUsuarioModel,
        $db,
        function () {
            return 1;
        }
    );

    $data = [
        'tipo' => TIPO_PERDIDO,
        'especie' => ESPECIE_CACHORRO,
        'tamanho' => TAMANHO_MEDIO,
        'descricao' => str_repeat('Detalhe ', 4),
        'data_ocorrido' => date('Y-m-d'),
        'endereco_completo' => 'Rua Principal, 123',
        'bairro' => 'Centro',
        'cidade' => 'Porto Velho',
        'estado' => 'RO',
        'whatsapp' => '69999999999'
    ];

    $result = $controller->create($data, []);
    assertTrue(!$result['success'], 'O resultado deveria indicar falha.');
    $exists = false;
    foreach ($result['errors'] as $error) {
        if (strpos($error, (string)MAX_ACTIVE_ADS_PER_USER) !== false) {
            $exists = true;
            break;
        }
    }
    assertTrue($exists, 'Mensagem de erro deve mencionar limite de anúncios ativos.');
});

addTest('Login bloqueia após três tentativas incorretas', function () {
    $password = 'Senha123';
    $fakeDb = new FakeAuthDatabase([
        'id' => 1,
        'nome' => 'Usuário Teste',
        'email' => 'teste@petfinder.com',
        'telefone' => '69999999999',
        'senha' => hashPassword($password),
        'ativo' => 1,
        'email_confirmado' => 1,
        'tentativas_login' => 0,
        'bloqueado_ate' => null
    ]);

    $auth = new Auth($fakeDb);

    for ($i = 0; $i < MAX_LOGIN_ATTEMPTS; $i++) {
        $result = $auth->login('teste@petfinder.com', 'Errada123');
        assertTrue(!$result['success'], 'Login deve falhar com senha incorreta.');
    }

    $result = $auth->login('teste@petfinder.com', 'Errada123');
    assertTrue(!$result['success'], 'Login deve permanecer bloqueado.');
    assertStringContains('Conta bloqueada', $result['error'], 'Mensagem deve indicar bloqueio.');
});

foreach ($tests as [$name, $fn]) {
    try {
        $fn();
        $results['passed']++;
        echo "[OK] {$name}" . PHP_EOL;
    } catch (Throwable $e) {
        $results['failed']++;
        echo "[FALHA] {$name}: " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL;
echo 'Total: ' . count($tests) . ' | Passou: ' . $results['passed'] . ' | Falhou: ' . $results['failed'] . PHP_EOL;
if ($results['failed'] > 0) {
    exit(1);
}
exit(0);
