# ADR-004 — API Oficial da Extensão Chrome

## Status

Aprovado

---

# Objetivo

Definir os endpoints oficiais consumidos pela extensão Chrome.

---

# Arquitetura Oficial

A extensão:

* deverá consumir exclusivamente API Laravel;
* deverá usar Bearer Token;
* deverá usar HTTPS obrigatório.

---

# Fluxo Oficial

```txt
Chrome Extension
        ↓
Laravel API
```

---

# Endpoints Oficiais

## Autenticação Google

```txt
POST /api/auth/google
```

---

## Usuário Atual

```txt
GET /api/me
```

---

## Saldo Atual

```txt
GET /api/wallet
```

---

## Criar Pagamento

```txt
POST /api/payments/create
```

---

## Webhook Mercado Pago

```txt
POST /api/payments/webhook
```

---

## Download XML

```txt
POST /api/download/xml
```

Payload:

```json
{
  "access_key": "3519..."
}
```

---

## Download PDF

```txt
POST /api/download/pdf
```

Payload:

```json
{
  "access_key": "3519..."
}
```

---

# Respostas Oficiais

## Sucesso

```json
{
  "success": true
}
```

---

## Erro

```json
{
  "success": false,
  "message": "Saldo insuficiente"
}
```

---

# Regras Obrigatórias

A extensão:

* nunca poderá calcular saldo;
* nunca poderá validar créditos;
* nunca poderá decidir cobrança;
* nunca poderá possuir lógica financeira;
* nunca poderá acessar MeuDanfe diretamente.

---

# Download

A API Laravel deverá:

* devolver stream;
* devolver blob;
* devolver binary response.

---

# Invariantes Obrigatórias

Obrigatório:

* HTTPS;
* Sanctum;
* Bearer Token;
* validação backend;
* logs mínimos.

---
