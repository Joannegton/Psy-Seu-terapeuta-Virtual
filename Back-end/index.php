<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controller/InteracaoController.php';
require_once __DIR__ . '/controller/UserController.php';
require_once __DIR__ . '/model/Interacao.php';
require_once __DIR__ . '/model/Usuario.php';


// Função para criar instâncias de controllers
function getController(string $controllerName, PDO $pdo): ?object
{
    switch ($controllerName) {
        case 'users':
            return new UserController(new Usuario($pdo));
        case 'interacoes':
            return new InteracaoController(new Interacao($pdo));
        default:
            return null;
    }
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');
$uriParts = explode('/', $uri);

try {
    if (isset($uriParts[0]) && $uriParts[0] === 'api' && $uriParts[1] === 'v1') {
        if (isset($uriParts[2])) {
            $controllerName = $uriParts[2];
            $pdo = getDbConnection();
            $controller = getController($controllerName, $pdo);

            if ($controller !== null) {
                $method = isset($uriParts[3]) ? $uriParts[3] . 'Action' : 'listAction';

                if (method_exists($controller, $method)) {
                    $controller->{$method}();
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Método não encontrado']);
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Recurso não encontrado']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Recurso não encontrado']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'API não encontrada']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor', 'message' => $e->getMessage()]);
}
