<?php
declare(strict_types=1);
$id = (int) ($_GET['id'] ?? 0);
$tituloPaginaAdmin = $id ? 'Ajustar formatação do depoimento' : 'Novo Depoimento';
require_once __DIR__ . '/layout/header.php';

$db   = Database::getInstance();
$erro = '';
$reg  = [
    'nome'             => '',
    'cargo'            => '',
    'texto'            => '',
    'nota'             => 5,
    'foto_url'         => '',
    'ordem'            => 0,
    'ativo'            => 1,
    'destaque'         => 0,
    'autorizacao_lgpd' => 0,
    'origem'           => '',
    'cliente_desde'    => '',
];

if ($id) {
    $reg = $db->fetchOne("SELECT * FROM depoimentos WHERE id = :id", [':id' => $id]) ?? $reg;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf('admin_dep_form', ADMIN_URL . '/depoimentos-form.php');

    $dados = [
        'nome'             => sanitizeString($_POST['nome']          ?? ''),
        'cargo'            => sanitizeString($_POST['cargo']         ?? ''),
        'texto'            => sanitizeTexto($_POST['texto']          ?? ''),
        'nota'             => max(1, min(5, (int) ($_POST['nota']    ?? 5))),
        'foto_url'         => sanitizeUrl($_POST['foto_url']         ?? ''),
        'ordem'            => (int) ($_POST['ordem']                 ?? 0),
        'ativo'            => isset($_POST['ativo'])            ? 1 : 0,
        'destaque'         => isset($_POST['destaque'])         ? 1 : 0,
        'autorizacao_lgpd' => isset($_POST['autorizacao_lgpd']) ? 1 : 0,
        'origem'           => in_array($_POST['origem'] ?? '', ['whatsapp','email','linkedin','pessoalmente','outro'], true)
                                ? $_POST['origem']
                                : null,
        'cliente_desde'    => !empty($_POST['cliente_desde'])
                                ? $_POST['cliente_desde']
                                : null,
    ];

    // Validações obrigatórias
    $err = validarObrigatorio($dados['nome'], 'Nome')
        ?: validarObrigatorio($dados['texto'], 'Depoimento');

    // Não permite ativar sem autorização LGPD
    if (!$err && $dados['ativo'] === 1 && $dados['autorizacao_lgpd'] !== 1) {
        $err = 'Não é possível ATIVAR um depoimento sem a Autorização LGPD do cliente.';
    }

    if ($err) {
        $erro = $err;
        $reg  = array_merge($reg, $dados);
    } else {
        if ($id) {
            $db->execute(
                "UPDATE depoimentos
                 SET nome=:n, cargo=:c, texto=:t, nota=:nt, foto_url=:f,
                     ordem=:o, ativo=:a, destaque=:de, autorizacao_lgpd=:lg,
                     origem=:or, cliente_desde=:cd
                 WHERE id=:id",
                [
                    ':n'  => $dados['nome'],
                    ':c'  => $dados['cargo'],
                    ':t'  => $dados['texto'],
                    ':nt' => $dados['nota'],
                    ':f'  => $dados['foto_url'],
                    ':o'  => $dados['ordem'],
                    ':a'  => $dados['ativo'],
                    ':de' => $dados['destaque'],
                    ':lg' => $dados['autorizacao_lgpd'],
                    ':or' => $dados['origem'],
                    ':cd' => $dados['cliente_desde'],
                    ':id' => $id,
                ]
            );
        } else {
            // Próxima ordem disponível
            if ($dados['ordem'] === 0) {
                $maxOrdem = $db->fetchOne("SELECT COALESCE(MAX(ordem), 0) + 1 AS n FROM depoimentos")['n'];
                $dados['ordem'] = (int) $maxOrdem;
            }
            $db->execute(
                "INSERT INTO depoimentos
                    (nome, cargo, texto, nota, foto_url, ordem, ativo,
                     destaque, autorizacao_lgpd, origem, cliente_desde)
                 VALUES
                    (:n, :c, :t, :nt, :f, :o, :a, :de, :lg, :or, :cd)",
                [
                    ':n'  => $dados['nome'],
                    ':c'  => $dados['cargo'],
                    ':t'  => $dados['texto'],
                    ':nt' => $dados['nota'],
                    ':f'  => $dados['foto_url'],
                    ':o'  => $dados['ordem'],
                    ':a'  => $dados['ativo'],
                    ':de' => $dados['destaque'],
                    ':lg' => $dados['autorizacao_lgpd'],
                    ':or' => $dados['origem'],
                    ':cd' => $dados['cliente_desde'],
                ]
            );
        }
        setFlash('sucesso', $id ? 'Depoimento atualizado!' : 'Depoimento criado!');
        redirect(ADMIN_URL . '/depoimentos.php');
    }
}

$csrf = generateCsrf('admin_dep_form');
?>

<?php if ($erro): ?>
  <div class="adm-alert adm-alert-error">⚠️ <?= e($erro) ?></div>
<?php endif; ?>

<!-- Aviso contextual no formulário -->
<div class="aviso-lgpd" style="margin-bottom:20px;">
  <strong>ℹ️ Lembre-se:</strong> Apenas <strong>ajuste a formatação</strong> do texto que o
  cliente enviou. Não altere o conteúdo, não invente frases. Marque "Autorização LGPD" somente
  se o cliente autorizou formalmente (via WhatsApp, e-mail ou pessoalmente).
</div>

<div class="adm-card" style="max-width:720px;">
  <div class="adm-card-header">
    <span class="adm-card-title">
      <?= $id ? '✏️ Ajustar formatação' : '➕ Novo' ?> Depoimento
    </span>
    <a href="depoimentos.php" class="btn btn-outline btn-sm">← Voltar</a>
  </div>

  <form method="POST" action="">
    <input type="hidden" name="<?= CSRF_FIELD_NAME ?>" value="<?= e($csrf) ?>">

    <!-- ── 1. Dados do cliente ────────────────────────── -->
    <p class="adm-section-label">Dados do cliente</p>
    <div class="adm-form-row">
      <div class="adm-form-group">
        <label>Nome completo *</label>
        <input type="text"
               name="nome"
               value="<?= e($reg['nome']) ?>"
               required
               placeholder="Ex: Rafael Mendonça">
      </div>
      <div class="adm-form-group">
        <label>Cargo / Empresa</label>
        <input type="text"
               name="cargo"
               value="<?= e($reg['cargo']) ?>"
               placeholder="Ex: Gerente — Empresa XYZ">
      </div>
    </div>

    <!-- ── 2. Texto do depoimento ────────────────────── -->
    <p class="adm-section-label">Depoimento</p>
    <div class="adm-form-group">
      <label>Texto do depoimento *</label>
      <textarea name="texto"
                rows="5"
                required
                placeholder="O texto exato enviado pelo cliente..."><?= e($reg['texto']) ?></textarea>
      <p class="adm-form-hint">Copie o texto enviado pelo cliente. Apenas ajuste pontuação e capitalização.</p>
    </div>

    <div class="adm-form-row">
      <div class="adm-form-group">
        <label>URL do avatar (opcional)</label>
        <input type="url"
               name="foto_url"
               value="<?= e($reg['foto_url'] ?? '') ?>"
               placeholder="https://...">
        <p class="adm-form-hint">Link da foto do cliente. Se vazio, usa as iniciais do nome.</p>
      </div>
      <div class="adm-form-group">
        <label>Nota (estrelas)</label>
        <select name="nota">
          <?php for ($i = 5; $i >= 1; $i--): ?>
            <option value="<?= $i ?>" <?= (int) $reg['nota'] === $i ? 'selected' : '' ?>>
              <?= str_repeat('★', $i) ?> (<?= $i ?>)
            </option>
          <?php endfor; ?>
        </select>
      </div>
    </div>

    <!-- ── 3. Metadados ───────────────────────────────── -->
    <p class="adm-section-label">Metadados e rastreabilidade</p>
    <div class="adm-form-row">
      <div class="adm-form-group">
        <label>Cliente desde (data aproximada)</label>
        <input type="date"
               name="cliente_desde"
               value="<?= e($reg['cliente_desde'] ?? '') ?>">
        <p class="adm-form-hint">Data em que a parceria começou.</p>
      </div>
      <div class="adm-form-group">
        <label>Origem do depoimento</label>
        <select name="origem">
          <option value="">— Selecione —</option>
          <?php foreach (['whatsapp'=>'WhatsApp','email'=>'E-mail','linkedin'=>'LinkedIn','pessoalmente'=>'Pessoalmente','outro'=>'Outro'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= ($reg['origem'] ?? '') === $val ? 'selected' : '' ?>>
              <?= $label ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="adm-form-row" style="align-items:flex-start;gap:20px;">
      <div class="adm-form-group">
        <label class="adm-form-check">
          <input type="checkbox"
                 name="destaque"
                 value="1"
                 <?= $reg['destaque'] ? 'checked' : '' ?>>
          Depoimento em destaque no site
        </label>
        <p class="adm-form-hint">Exibe badge especial e borda colorida no card.</p>
      </div>
      <div class="adm-form-group">
        <label class="adm-form-check">
          <input type="checkbox"
                 name="autorizacao_lgpd"
                 value="1"
                 <?= $reg['autorizacao_lgpd'] ? 'checked' : '' ?>
                 id="chk-lgpd">
          <strong>✅ Autorização LGPD confirmada</strong>
        </label>
        <p class="adm-form-hint" style="color:#B45309;">
          Marque SOMENTE se o cliente autorizou formalmente o uso do depoimento.
        </p>
      </div>
    </div>

    <!-- ── 4. Configurações de exibição ───────────────── -->
    <p class="adm-section-label">Configurações de exibição</p>
    <div class="adm-form-row">
      <div class="adm-form-group">
        <label>Ordem</label>
        <input type="number"
               name="ordem"
               value="<?= (int) $reg['ordem'] ?>"
               min="0">
        <p class="adm-form-hint">Menor número = aparece primeiro.</p>
      </div>
      <div class="adm-form-group">
        <label class="adm-form-check" style="margin-top:28px;">
          <input type="checkbox"
                 name="ativo"
                 value="1"
                 <?= $reg['ativo'] ? 'checked' : '' ?>>
          Depoimento ativo (visível no site)
        </label>
        <p class="adm-form-hint" style="color:#B45309;">
          Só é possível ativar com Autorização LGPD marcada.
        </p>
      </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px;padding-top:16px;border-top:1px solid #E9ECEF;">
      <a href="depoimentos.php" class="btn btn-outline">Cancelar</a>
      <button type="submit" class="btn btn-primary">💾 Salvar</button>
    </div>
  </form>
</div>

<style>
.aviso-lgpd {
  background: #FFFBEB;
  border-left: 4px solid #F59E0B;
  border-radius: 6px;
  padding: 14px 18px;
  font-size: 13.5px;
  color: #78350F;
  line-height: 1.6;
}

/* Separadores de seção dentro do formulário */
.adm-section-label {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: #9CA3AF;
  margin: 20px 0 10px;
  padding-bottom: 6px;
  border-bottom: 1px solid #E9ECEF;
}
</style>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
