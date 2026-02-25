<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
  http_response_code(204);
  exit;
}

$storagePath = __DIR__ . "/storage.json";

function read_storage($path) {
  return json_decode(file_get_contents($path), true);
}

function write_storage($path, $data) {
  file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function get_path() {
  $uri = $_SERVER["REQUEST_URI"];
  $q = strpos($uri, "?");
  if ($q !== false) $uri = substr($uri, 0, $q);
  $pos = strpos($uri, "index.php");
  return $pos === false ? "/" : substr($uri, $pos + strlen("index.php"));
}

$path = get_path();
$method = $_SERVER["REQUEST_METHOD"];
$data = read_storage($storagePath);

if ($method === "GET" && $path === "/maternidades") {
  echo json_encode($data["maternidades"]);
  exit;
}

if ($method === "GET" && $path === "/bebes") {

  $result = $data["bebes"];

  if (!empty($_GET["nome"])) {
    $needle = mb_strtolower($_GET["nome"]);
    $result = array_filter($result, fn($b) =>
      strpos(mb_strtolower($b["nomeBebe"]), $needle) !== false
    );
  }

  if (!empty($_GET["dataNascimento"])) {
    $result = array_filter($result, fn($b) =>
      $b["dataNascimento"] === $_GET["dataNascimento"]
    );
  }

  if (!empty($_GET["maternidadeId"])) {
    $mid = (int)$_GET["maternidadeId"];
    $result = array_filter($result, fn($b) =>
      (int)$b["maternidadeId"] === $mid
    );
  }

  foreach ($result as &$b) {
    foreach ($data["maternidades"] as $m) {
      if ($m["id"] == $b["maternidadeId"]) {
        $b["maternidadeNome"] = $m["nome"];
      }
    }
  }

  usort($result, fn($a,$b) =>
    strcasecmp($a["nomeBebe"], $b["nomeBebe"])
  );

  if (count($result) === 0) {
    http_response_code(404);
    echo json_encode(["message" => "Nenhum bebê encontrado"]);
    exit;
  }

  echo json_encode(array_values($result));
  exit;
}

if ($method === "POST" && $path === "/bebes") {

  $body = json_decode(file_get_contents("php://input"), true);

  $required = ["nomeBebe","dataNascimento","nomeMae","nomePai","maternidadeId","mensagemResponsavel"];
  foreach ($required as $r) {
    if (empty($body[$r])) {
      http_response_code(400);
      echo json_encode(["message" => "Campo obrigatório: $r"]);
      exit;
    }
  }

  $new = [
    "id" => $data["nextBebeId"],
    "nomeBebe" => $body["nomeBebe"],
    "dataNascimento" => $body["dataNascimento"],
    "nomeMae" => $body["nomeMae"],
    "nomePai" => $body["nomePai"],
    "maternidadeId" => (int)$body["maternidadeId"],
    "mensagemResponsavel" => $body["mensagemResponsavel"],
    "fotoUrl" => "https://placehold.co/60x60/pink/white?text=B"
  ];

  $data["nextBebeId"]++;
  $data["bebes"][] = $new;

  write_storage($storagePath, $data);

  http_response_code(201);
  echo json_encode($new);
  exit;
}

http_response_code(404);
echo json_encode(["message" => "Rota não encontrada"]);