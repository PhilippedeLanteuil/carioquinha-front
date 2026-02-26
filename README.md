# 🍼 CarioquinhaRio - Frontend

Frontend desenvolvido para o projeto **CarioquinhaRio**, responsável pelo cadastro e consulta de nascimentos em maternidades.

Este projeto segue rigorosamente os requisitos solicitados:

* ✅ Tela de Inclusão desenvolvida em **HTML + JavaScript**
* ✅ Tela de Consulta desenvolvida em **PHP**
* ✅ Consumo de API REST
* ✅ Parse de JSON
* ✅ Listagem ordenada
* ✅ Tratamento de erro (404 quando não encontra registros)
* ✅ Interface moderna com tema maternidade (azul e rosa pastel)

---

## 🎨 Design

A interface foi desenvolvida com identidade visual inspirada em:

* Azul pastel
* Rosa pastel
* Tema maternidade / nascimento
* Layout moderno, leve e responsivo

---

## 📁 Estrutura do Projeto

```
carioquinha-front
│
├── api
│   ├── index.php        # Mock da API REST
│   └── storage.json     # "Banco de dados" em JSON
│
└── frontend
    ├── shared
    │   └── styles.css   # Estilos globais (tema pastel)
    │
    ├── cadastro
    │   ├── index.html   # Tela de inclusão (HTML + JS)
    │   └── app.js       # Lógica de envio para API
    │
    └── consulta
        └── index.php    # Tela de consulta (PHP consumindo API)
```

---

## 🚀 Como Executar o Projeto

### 1️⃣ Pré-requisitos

* PHP 8 ou superior
* Navegador moderno

---

### 2️⃣ Executar o servidor

Na raiz do projeto:

```bash
php -S 127.0.0.1:8000
```

---

### 3️⃣ Acessar no navegador

### 🍼 Tela de Cadastro (HTML + JS)

```
http://127.0.0.1:8000/frontend/cadastro/index.html
```

---

### 🔎 Tela de Consulta (PHP)

```
http://127.0.0.1:8000/frontend/consulta/index.php
```

---

## 🔌 API Mock

Para facilitar os testes, foi implementada uma API simulada em PHP.

### Endpoints disponíveis:

### 📌 GET Maternidades

```
GET /api/index.php/maternidades
```

---

### 📌 GET Bebês (com filtros)

```
GET /api/index.php/bebes?nome=&dataNascimento=&maternidadeId=
```

---

### 📌 POST Criar Bebê

```
POST /api/index.php/bebes
```

Formato esperado:

```json
{
  "nomeBebe": "Maria Clara",
  "dataNascimento": "2025-01-12",
  "nomeMae": "Ana Souza",
  "nomePai": "João Souza",
  "maternidadeId": 2,
  "mensagemResponsavel": "Bem-vinda ao mundo!"
}
```

---

## 📋 Funcionalidades Implementadas

### ✔ Tela de Inclusão

* Validação de campos obrigatórios
* Envio via `fetch()` (POST JSON)
* Feedback visual ao usuário
* Integração com API

---

### ✔ Tela de Consulta

* Desenvolvida em PHP
* Consumo de API via HTTP
* Parse do JSON
* Listagem ordenada A–Z por nome do bebê
* Filtros por:

  * Nome parcial
  * Data de nascimento
  * Maternidade
* Retorno:

  * ⚠️ "Nenhum bebê encontrado" quando não houver registros

---

## 🛠 Tecnologias Utilizadas

* HTML5
* CSS3
* JavaScript (Fetch API)
* PHP
* JSON (persistência simulada)

---

## 📌 Observações

Este frontend foi desenvolvido para:

* Demonstrar integração com API REST
* Separação clara entre:

  * Camada de apresentação (HTML/JS)
  * Camada de consumo (PHP)
* Seguir fielmente os requisitos do projeto

A API mock pode ser facilmente substituída por uma API real (ex: Quarkus), bastando alterar a URL base nos arquivos:

* `frontend/cadastro/app.js`
* `frontend/consulta/index.php`

---

## 👨‍💻 Autor

Projeto desenvolvido como solução para o desafio técnico **CarioquinhaRio**.