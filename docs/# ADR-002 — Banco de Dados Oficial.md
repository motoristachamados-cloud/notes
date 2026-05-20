
# ADR-002 — Banco de Dados Oficial

## Status

Aprovado

---

# Objetivo

Definir a estrutura oficial mínima do banco PostgreSQL.

---

# Estrutura Oficial

## users

Responsável por:

* autenticação;
* perfil;
* login Google.

```sql
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    uuid UUID NOT NULL UNIQUE,

    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,

    google_id VARCHAR(255) UNIQUE,
    avatar TEXT,

    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## wallets

Responsável por:

* saldo atual.

```sql
CREATE TABLE wallets (
    id BIGSERIAL PRIMARY KEY,

    user_id BIGINT NOT NULL UNIQUE
        REFERENCES users(id)
        ON DELETE CASCADE,

    balance INTEGER NOT NULL DEFAULT 0,

    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## financial_transactions

Responsável por:

* auditoria financeira;
* créditos;
* débitos;
* pagamentos.

```sql
CREATE TABLE financial_transactions (
    id BIGSERIAL PRIMARY KEY,
    uuid UUID NOT NULL UNIQUE,

    user_id BIGINT NOT NULL
        REFERENCES users(id)
        ON DELETE CASCADE,

    type VARCHAR(20) NOT NULL,
    amount INTEGER NOT NULL,

    description TEXT,

    mercadopago_payment_id VARCHAR(120),

    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## access_keys

Responsável por:

* impedir cobrança duplicada;
* registrar downloads realizados.

```sql
CREATE TABLE access_keys (
    id BIGSERIAL PRIMARY KEY,
    uuid UUID NOT NULL UNIQUE,

    user_id BIGINT NOT NULL
        REFERENCES users(id)
        ON DELETE CASCADE,

    access_key VARCHAR(44) NOT NULL,

    type VARCHAR(10) NOT NULL,

    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## system_settings

Responsável por:

* configurações globais do sistema.

```sql
CREATE TABLE system_settings (
    id BIGSERIAL PRIMARY KEY,

    key VARCHAR(120) NOT NULL UNIQUE,
    value TEXT NOT NULL,

    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

# Configurações Oficiais

## credit_price

```txt
Preço por crédito em centavos.
```

---

## provider_cost

```txt
Custo MeuDanfe em centavos.
```

---

## minimum_purchase

```txt
Quantidade mínima de créditos.
```

---

# Variantes Permitidas

Permitido adicionar:

* índices;
* tabelas auxiliares;
* logs;
* auditoria;
* tabelas administrativas.

---

# Invariantes Obrigatórias

Obrigatório:

* access_key deverá possuir VARCHAR(44);
* balance deverá usar INTEGER;
* amount deverá usar INTEGER;
* proibição absoluta de FLOAT;
* proibição absoluta de salvar XML/PDF;
* proibição absoluta de salvar conteúdo fiscal.

---

# Constraints Obrigatórias

```sql
ALTER TABLE access_keys
ADD CONSTRAINT uq_access_key_user_type
UNIQUE (user_id, access_key, type);
```

---

# Índices Obrigatórios

```sql
CREATE INDEX idx_access_key
ON access_keys(access_key);
```

---

