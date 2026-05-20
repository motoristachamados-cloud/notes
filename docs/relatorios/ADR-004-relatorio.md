# Relatório de Execução ADR-004

Data: 2026-05-20
Status: Executado.

## Itens executados
- Endpoints oficiais da extensão implementados:
  - POST /api/auth/google
  - GET /api/me
  - GET /api/wallet
  - POST /api/payments/create
  - POST /api/payments/webhook
  - POST /api/download/xml
  - POST /api/download/pdf
- Bearer Token via Sanctum utilizado nos endpoints protegidos.
- Download retornando binary response (XML/PDF).

## Evidências
- routes/api.php
- app/Http/Controllers/Api/*
- php artisan route:list --path=api exibindo 7 rotas oficiais.

## Pendências
- HTTPS é requisito de deploy/infra, não configurado em código local.
