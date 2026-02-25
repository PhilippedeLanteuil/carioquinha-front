<?php
// ✅ Use 127.0.0.1 para evitar travas com IPv6 (::1)
$API_BASE = "http://127.0.0.1:8000/api/index.php";

$erro = null;
$bebes = null;
$maternidades = [];

function http_get($url, &$statusCode) {
  $context = stream_context_create([
    "http" => [
      "method" => "GET",
      "ignore_errors" => true,
      "timeout" => 4
    ]
  ]);

  $body = @file_get_contents($url, false, $context);

  $statusCode = 0;
  if (isset($http_response_header[0])) {
    if (preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match)) {
      $statusCode = (int)$match[1];
    }
  }

  return $body;
}

function safe_arr($x) {
  return is_array($x) ? $x : [];
}

function h($s) {
  return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8");
}

function fmt_date_br($iso) {
  // yyyy-mm-dd -> dd/mm/yyyy
  if (!is_string($iso) || strlen($iso) < 10) return $iso;
  $y = substr($iso, 0, 4);
  $m = substr($iso, 5, 2);
  $d = substr($iso, 8, 2);
  if (!ctype_digit($y.$m.$d)) return $iso;
  return $d . "/" . $m . "/" . $y;
}

/* ===============================
   CARREGAR MATERNIDADES
================================= */
$status = 0;
$body = http_get($API_BASE . "/maternidades", $status);

if ($status >= 200 && $status < 300 && $body) {
  $maternidades = safe_arr(json_decode($body, true));
} elseif ($status === 0) {
  $erro = "❌ Não consegui acessar a API mock. Rode na raiz do projeto: php -S 127.0.0.1:8000";
}

/* ===============================
   CONSULTAR BEBÊS (somente ao pesquisar)
================================= */
if ($erro === null && !empty($_GET)) {
  $params = [];

  if (!empty($_GET["nome"])) $params["nome"] = $_GET["nome"];
  if (!empty($_GET["dataNascimento"])) $params["dataNascimento"] = $_GET["dataNascimento"];
  if (!empty($_GET["maternidadeId"])) $params["maternidadeId"] = $_GET["maternidadeId"];

  $url = $API_BASE . "/bebes" . (count($params) ? "?" . http_build_query($params) : "");

  $status = 0;
  $body = http_get($url, $status);

  if ($status === 404) {
    $erro = "⚠️ Nenhum bebê encontrado";
  } elseif ($status >= 200 && $status < 300 && $body) {
    $bebes = safe_arr(json_decode($body, true));

    // ✅ Garantir ordenação A–Z por nomeBebe (mesmo que a API já ordene)
    usort($bebes, function($a, $b) {
      return strcasecmp($a["nomeBebe"] ?? "", $b["nomeBebe"] ?? "");
    });
  } elseif ($status === 0) {
    $erro = "❌ API não respondeu (timeout). Verifique se o servidor PHP está rodando.";
  } else {
    $erro = "❌ Erro na API (HTTP $status)";
  }
}

// contadores (para cards)
$qtMats = is_array($maternidades) ? count($maternidades) : 0;
$qtBebes = is_array($bebes) ? count($bebes) : 0;
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>CarioquinhaRio • Consulta</title>
  <link rel="stylesheet" href="../shared/styles.css" />
</head>
<body>
  <div class="container">
    <header class="topbar">
      <div class="brand">
        <div class="logo" aria-hidden="true">CR</div>
        <div>
          <div class="brand-title">CarioquinhaRio</div>
          <div class="brand-subtitle">Cadastro & Consulta de Nascimentos</div>
        </div>
      </div>

      <nav class="nav">
        <a class="pill" href="../cadastro/index.html">🍼 Cadastro</a>
        <a class="pill active" href="./index.php">🔎 Consulta</a>
      </nav>
    </header>

    <section class="card hero">
      <div>
        <h1>Consulta com carinho 💙🌸</h1>
        <p>
          Filtre por nome, data e maternidade. A listagem vem da <b>API mock</b> e é exibida em ordem alfabética.
        </p>
        <div class="chips">
          <span class="chip blue">Ordenado A–Z</span>
          <span class="chip pink">Fotos exibidas</span>
          <span class="chip cream">Tema maternidade</span>
        </div>
      </div>

      <div class="hero-right">
        <div class="stat">
          <div class="stat-label">Maternidades</div>
          <div class="stat-value"><?= h($qtMats) ?></div>
        </div>
        <div class="stat">
          <div class="stat-label">Resultados</div>
          <div class="stat-value"><?= h($qtBebes) ?></div>
        </div>
      </div>
    </section>

    <section class="card">
      <h2>Filtros</h2>
      <p class="muted">Dica: nome é parcial (contém). Data deve ser exatamente igual.</p>

      <form method="GET" class="form">
        <div class="row">
          <div class="field">
            <label>Nome (parcial)</label>
            <input name="nome" value="<?= h($_GET["nome"] ?? "") ?>" placeholder="Ex.: Maria"/>
          </div>

          <div class="field">
            <label>Data de nascimento</label>
            <input type="date" name="dataNascimento" value="<?= h($_GET["dataNascimento"] ?? "") ?>"/>
          </div>
        </div>

        <div class="row">
          <div class="field">
            <label>Maternidade</label>
            <select name="maternidadeId">
              <option value="">Todas</option>
              <?php foreach ($maternidades as $m): ?>
                <option value="<?= h($m["id"] ?? "") ?>"
                  <?= (($_GET["maternidadeId"] ?? "") == ($m["id"] ?? "")) ? "selected" : "" ?>>
                  <?= h($m["nome"] ?? "") ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label>&nbsp;</label>
            <div class="actions">
              <button class="btn primary" type="submit">Pesquisar</button>
              <a class="btn ghost" href="./index.php" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">
                Limpar filtros
              </a>
            </div>
          </div>
        </div>

        <?php if ($erro): ?>
          <div class="toast show <?= (str_starts_with($erro, "⚠️")) ? "ok" : "err" ?>">
            <?= h($erro) ?>
          </div>
        <?php endif; ?>
      </form>
    </section>

    <?php if (is_array($bebes) && count($bebes) > 0): ?>
      <section class="card">
        <h2>Listagem</h2>
        <p class="muted">Resultados em ordem alfabética pelo nome do bebê.</p>

        <div style="overflow:auto; border-radius: 18px;">
          <table class="table">
            <thead>
              <tr>
                <th>Foto</th>
                <th>Nome do Bebê</th>
                <th>Nome da Mãe</th>
                <th>Data</th>
                <th>Maternidade</th>
                <th>Mensagem</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($bebes as $b): ?>
                <tr>
                  <td>
                    <?php if (!empty($b["fotoUrl"])): ?>
                      <img class="avatar" src="<?= h($b["fotoUrl"]) ?>" alt="Foto do bebê"/>
                    <?php else: ?>
                      <span class="small">(sem foto)</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div style="font-weight:950;"><?= h($b["nomeBebe"] ?? "") ?></div>
                    <div class="small">Pai: <?= h($b["nomePai"] ?? "") ?></div>
                  </td>
                  <td><?= h($b["nomeMae"] ?? "") ?></td>
                  <td>
                    <span class="badge"><?= h(fmt_date_br($b["dataNascimento"] ?? "")) ?></span>
                  </td>
                  <td><?= h($b["maternidadeNome"] ?? "") ?></td>
                  <td style="max-width: 340px;">
                    <span class="small"><?= h($b["mensagemResponsavel"] ?? "") ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <hr/>
        <div class="small">
          API mock: <code>/api/index.php</code> • Base: <code><?= h($API_BASE) ?></code>
        </div>
      </section>
    <?php endif; ?>

  </div>
</body>
</html>