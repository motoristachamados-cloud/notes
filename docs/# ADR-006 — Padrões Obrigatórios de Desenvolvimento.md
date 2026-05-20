
# ADR-006 — Padrões Obrigatórios de Desenvolvimento

## Status

Aprovado

---

# Backend

Obrigatório:

* Laravel 12;
* PHP 8.4;
* PostgreSQL;
* Docker/Sail.

---

# Estrutura Obrigatória

```txt
app/
├── Actions/
├── DTOs/
├── Http/
├── Models/
├── Services/
└── Support/
```

---

# Padrões Obrigatórios

Obrigatório:

* Services para regras negócio;
* FormRequest para validação;
* DTO para payload complexo;
* Response padronizada JSON;
* UUID para entidades expostas.

---

# Proibições

Proibido:

* lógica negócio em Controller;
* SQL inline em Controller;
* lógica financeira frontend;
* lógica MeuDanfe frontend;
* salvar XML/PDF.

---

# Extensão Chrome

Obrigatório:

* MV3;
* frontend fino;
* chamadas apenas Laravel API.

---

# Banco

Obrigatório:

* migrations Laravel;
* foreign keys;
* índices;
* unique constraints.

---

# Segurança

Obrigatório:

* HTTPS;
* tokens;
* validação backend;
* rate limiting futuro.

---

# Objetivo Final

O sistema deverá permanecer:

* simples;
* barato;
* escalável;
* seguro;
* sustentável;
* fácil manutenção;
* sem complexidade desnecessária.
