
# ADR-003 — Integração MeuDanfe

## Status

Aprovado

---

# Objetivo

Definir a integração oficial com API MeuDanfe.

---

# Decisão Arquitetural

A API MeuDanfe:

* deverá ser acessada exclusivamente pelo backend Laravel;
* nunca poderá ser acessada pela extensão;
* deverá possuir Api-Key armazenada apenas em .env.

---

# Fluxo Oficial

```txt
Extensão
    ↓
Laravel
    ↓
MeuDanfe
```

---

# Endpoints Oficiais MeuDanfe

## Adicionar NF

```txt
PUT /v2/fd/add/{access_key}
```

---

## Buscar XML

```txt
GET /v2/fd/get/xml/{access_key}
```

---

## Buscar PDF

```txt
GET /v2/fd/get/da/{access_key}
```

---

# Regras Obrigatórias

## Download

Antes de chamar MeuDanfe:

1. verificar saldo;
2. verificar histórico em access_keys;
3. impedir cobrança duplicada.

---

# Regras de Cobrança

## Já possui chave

Se usuário já possuir:

```txt
(user_id, access_key, type)
```

Então:

* não descontar saldo;
* não registrar novo débito.

---

## Não possui chave

Obrigatório:

* decrementar wallet.balance;
* criar financial_transaction debit;
* registrar access_key;
* chamar MeuDanfe.

---

# Invariantes Obrigatórias

Proibido:

* salvar XML;
* salvar PDF;
* salvar ZIP;
* salvar conteúdo fiscal;
* expor Api-Key;
* expor endpoint MeuDanfe.

---

# Normas Obrigatórias para IA

A IA deverá:

* utilizar Http Client Laravel;
* utilizar Services para integração;
* utilizar DTOs quando necessário;
* utilizar transações financeiras;
* utilizar lockForUpdate para débito saldo;
* tratar erros MeuDanfe adequadamente.

---

