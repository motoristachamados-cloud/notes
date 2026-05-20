# ADR-001 — Arquitetura Geral do SaaS NF-e

## Status

Aprovado

---

# Objetivo

Definir a arquitetura oficial do sistema SaaS responsável por:

* autenticação de usuários;
* controle financeiro por créditos;
* integração com API MeuDanfe;
* consumo via extensão Chrome;
* gerenciamento de downloads NF-e/CT-e;
* monetização pay-per-use.

---

# Decisão Arquitetural

O sistema deverá possuir:

* Backend centralizado em Laravel 12;
* PostgreSQL como banco principal;
* Extensão Chrome atuando apenas como cliente/UI;
* Mercado Pago como gateway de pagamento;
* API MeuDanfe utilizada exclusivamente pelo backend;
* Docker/Sail como ambiente padrão de desenvolvimento.

---

# Arquitetura Oficial

```txt
Chrome Extension
        ↓
Laravel API
        ↓
MeuDanfe API
        ↓
PostgreSQL
```

---

# Variantes Permitidas

## Backend

Permitido:

* Laravel 12+
* PHP 8.4+
* Sanctum
* Socialite
* Redis opcional

---

## Banco

Permitido:

* PostgreSQL 16+

---

## Infraestrutura

Permitido:

* Docker
* Laravel Sail
* Railway
* VPS Linux

---

# Invariantes Obrigatórias

## Segurança

Obrigatório:

* API MeuDanfe nunca poderá ser chamada diretamente pela extensão;
* Api-Key MeuDanfe nunca poderá existir no frontend;
* Toda lógica financeira deverá existir exclusivamente no backend;
* Toda validação de saldo deverá ocorrer exclusivamente no backend;
* Toda cobrança deverá ocorrer exclusivamente no backend.

---

## Extensão

A extensão:

* não poderá possuir lógica financeira;
* não poderá possuir lógica de licenciamento;
* não poderá conhecer endpoints do MeuDanfe;
* não poderá armazenar dados fiscais permanentes;
* deverá atuar apenas como interface cliente.

---

## Banco

Obrigatório:

* uso de UUID para entidades expostas via API;
* uso de INTEGER para créditos;
* proibição absoluta de FLOAT para valores financeiros;
* uso obrigatório de timestamps;
* uso obrigatório de constraints UNIQUE;
* uso obrigatório de índices nas colunas de busca.

---

# Normas Obrigatórias para IA

A IA deverá:

* respeitar integralmente esta arquitetura;
* não propor Firebase;
* não propor armazenamento local sensível;
* não propor lógica financeira client-side;
* não propor autenticação local na extensão;
* não propor armazenamento permanente de XML/PDF;
* não propor microserviços;
* não propor multi-database tenancy;
* não propor MongoDB;
* não propor Redis obrigatório para MVP;
* não propor filas obrigatórias no MVP.

---
