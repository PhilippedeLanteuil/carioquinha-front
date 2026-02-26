const API_BASE = "http://localhost:8080";

const form = document.getElementById("formBebe");
const toastEl = document.getElementById("toast");
const btnSalvar = document.getElementById("btnSalvar");
const btnLimpar = document.getElementById("btnLimpar");

function showToast(msg, type = "ok") {
  toastEl.className = `toast show ${type}`;
  toastEl.textContent = msg;
  setTimeout(() => {
    toastEl.className = "toast";
    toastEl.textContent = "";
  }, 3000);
}

function setLoading(isLoading) {
  btnSalvar.disabled = isLoading;
  btnSalvar.textContent = isLoading ? "Salvando..." : "Salvar cadastro";
}

async function fetchJson(url, options = {}) {
  const res = await fetch(url, options);
  const text = await res.text();
  let json = null;
  try { json = text ? JSON.parse(text) : null; } catch {}
  return { ok: res.ok, status: res.status, json, text };
}

async function carregarMaternidades() {
  const select = document.getElementById("maternidadeId");

  try {
    const { ok, json } = await fetchJson(`${API_BASE}/maternidades`);

    if (!ok || !Array.isArray(json)) {
      select.innerHTML = `<option value="">Erro ao carregar</option>`;
      return;
    }

    select.innerHTML =
      `<option value="">Selecione...</option>` +
      json.map(m => `<option value="${m.id}">${m.nome}</option>`).join("");

  } catch {
    select.innerHTML = `<option value="">Erro ao carregar</option>`;
  }
}

btnLimpar.addEventListener("click", () => {
  form.reset();
  showToast("Formulário limpo 🌸");
});

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const data = document.getElementById("dataNascimento").value;

  const payload = {
    // ✅ backend espera "nome"
    nome: document.getElementById("nomeBebe").value.trim(),

    // ✅ backend espera LocalDateTime no formato "yyyy-MM-dd HH:mm"
    dataNascimento: data ? `${data} 00:00` : "",

    nomeMae: document.getElementById("nomeMae").value.trim(),

    // ✅ opcional no backend
    nomePai: document.getElementById("nomePai").value.trim(),

    maternidadeId: Number(document.getElementById("maternidadeId").value),

    mensagemResponsavel: document.getElementById("mensagem").value.trim()
  };

  // ✅ nomePai NÃO é obrigatório
  if (
    !payload.nome ||
    !payload.dataNascimento ||
    !payload.nomeMae ||
    !payload.maternidadeId ||
    !payload.mensagemResponsavel
  ) {
    showToast("Preencha todos os campos obrigatórios *", "err");
    return;
  }

  setLoading(true);

  try {
    const { ok, status, json } = await fetchJson(`${API_BASE}/bebes`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Operador": "frontend"
      },
      body: JSON.stringify(payload)
    });

    if (ok) {
      showToast("Cadastro realizado com sucesso! 🍼💙", "ok");
      form.reset();
      return;
    }

    const msg = json?.message || `Erro HTTP ${status}`;
    showToast(msg, "err");

  } catch {
    showToast("Erro de conexão com a API.", "err");
  } finally {
    setLoading(false);
  }
});

carregarMaternidades();