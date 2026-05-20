# Relatório de Execução ADR-006

Data: 2026-05-20
Status: Executado com 1 desvio de versão de runtime.

## Itens executados
- Estrutura exigida criada:
  - app/DTOs
  - app/Services
  - app/Support
- Padrões aplicados:
  - Services para regras de negócio;
  - FormRequest para validação API;
  - DTO para payload de download;
  - resposta JSON padronizada.
- Proibições respeitadas:
  - sem lógica financeira no frontend;
  - sem SQL inline em controller;
  - sem persistência de XML/PDF.

## Pendências
- Requisito ADR de PHP 8.4+: composer atual está em ^8.3.
