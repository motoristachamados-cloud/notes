
# ADR-005 — Regras Financeiras Oficiais

## Status

Aprovado

---

# Objetivo

Definir regras financeiras oficiais do sistema.

---

# Modelo Oficial

O sistema utilizará:

```txt
saldo pré-pago por créditos
```

---

# Definição Oficial

## 1 crédito

```txt
1 download XML ou PDF
```

---

# Valor do Crédito

O valor:

* deverá ser global;
* deverá ser configurável;
* deverá ser armazenado em system_settings.

---

# Configuração Oficial

```txt
credit_price = 6
provider_cost = 3
minimum_purchase = 50
```

---

# Significado

```txt
R$ 0,06 por download
R$ 0,03 custo MeuDanfe
50 créditos mínimo
```

---

# Regras Obrigatórias

## Compra mínima

Obrigatório:

```txt
minimum_purchase >= 50
```

---

## Pagamentos

Obrigatório:

* Mercado Pago;
* webhook obrigatório;
* validação posterior via API Mercado Pago.

---

# Proibições

Proibido:

* confiar apenas no webhook;
* confiar no frontend;
* confiar no retorno client-side;
* usar float;
* usar decimal para créditos.

---

# Normas Obrigatórias para IA

A IA deverá:

* utilizar centavos em INTEGER;
* utilizar transações atômicas;
* utilizar auditoria financeira;
* utilizar lockForUpdate;
* impedir saldo negativo;
* impedir consumo duplicado.

---
