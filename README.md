# Simple Payments API

Este é um projeto backend desenvolvido como parte de um desafio técnico. Ele implementa uma API RESTful para transferências financeiras entre usuários, com suporte a regras de negócio específicas, integração com serviços externos e testes automatizados.

## Tecnologias Utilizadas
- **PHP 8.4**: Versão moderna com melhorias de desempenho e tipagem.
- **Laravel 12**: Framework robusto para APIs RESTful, com ORM e suporte a testes.
- **Docker**: Containerização para consistência entre ambientes.
- **MySQL**: Banco relacional para persistência de dados.
- **PHPUnit**: Testes unitários e de integração.
- **PHPStan**: Análise estática para garantir qualidade do código.

## Estrutura do Projeto
- **Arquitetura**: Camadas separadas (Controller, Service, Repository) seguindo princípios SOLID.
    - `app/Http/Controllers`: Lógica de entrada da API.
    - `app/Services`: Regras de negócio.
    - `app/Repositories`: Acesso a dados.
- **Modelagem**: Tabelas `users` (usuários) e `wallets` (carteiras).
- **Endpoint**: `POST /api/transfer` para transferências.

## Pré-requisitos
- Docker
- Docker Compose
- Git

## Como Rodar
1. Clone o repositório:
   ```bash
   git clone https://github.com/dandevweb/simple-payments
   cd simple-payments
    ```
2. Inicie os containers:
   ```bash
   docker-compose up -d --build
   ```
3. Copie o arquivo `.env.example` para `.env`:
   ```bash
   cp .env.example .env
   ```
4. Gere a chave de aplicação do Laravel:
   ```bash
   docker-compose exec app php artisan key:generate
   ```
5. Instale as dependências do Composer:
   ```bash
   docker-compose exec app composer install
   ```
6. Configure o banco de dados:
   ```bash
   docker-compose exec app php artisan migrate
   docker-compose exec app php artisan db:seed
   ```

7. Acesse a API:
   - URL: `http://localhost:8000/api/transfer`
   - Método: `POST`
   - Headers: `Content-Type: application/json`
   - Body:
     ```json
     {
       "payer": 1,
       "payee": 2,
       "value": 100.00
     }
     ```
     
## Testes

Para rodar os testes, execute:
```bash
  docker-compose exec app php artisan test
```

## Análise Estática
Para rodar a análise estática com PHPStan, execute:
```bash
  docker-compose exec app vendor/bin/phpstan analyse
```
