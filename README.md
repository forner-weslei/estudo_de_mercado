# Estudo de Mercado Avançado (MVP) – Laravel

Este projeto é um **MVP** (primeira versão funcional) da aplicação “Estudo de Mercado Avançado”, pensado para rodar em **HostGator (cPanel)**.

> Observação: por ser Laravel, o repositório **não inclui** a pasta `vendor/` (dependências). Você instala via **Composer** no seu computador ou via **SSH** no HostGator, conforme o passo a passo abaixo.

## O que este MVP entrega
- Login e cadastro (Laravel Breeze)
- CRUD de Estudos de Mercado
- CRUD de Amostras (manual) + upload de fotos
- Cálculo de preço/m² e cenários (Otimista/Mercado/Competitivo) com percentuais configuráveis por estudo
- Tela de **Apresentação** (principal) dentro do app
- Branding (logo + cores + rodapé) por usuário + override por estudo
- Botão **Exportar PDF** (gera um PDF do mesmo layout da apresentação)

## Requisitos
- PHP 8.1+ (ideal 8.2)
- MySQL/MariaDB
- Composer
- Extensões PHP comuns do Laravel (openssl, pdo_mysql, mbstring, tokenizer, xml, ctype, json, bcmath)

## Instalação (local – recomendado)
1) Instale PHP e Composer no seu PC.
2) No terminal:
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```
3) Configure seu banco no `.env` (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
4) Rode as migrações:
   ```bash
   php artisan migrate
   php artisan storage:link
   ```
5) Rode o servidor:
   ```bash
   php artisan serve
   ```

## Deploy na HostGator (cPanel)
Existem 2 formas comuns:

### A) Subdomínio apontando para /public (recomendado)
- Crie um subdomínio (ex: app.canaloportunidadesimob.com.br) e aponte o “Document Root” para a pasta `public/` do Laravel.
- Envie os arquivos do projeto para uma pasta (ex: `/home/SEUUSUARIO/laravel/estudo-mercado-avancado`)
- Configure o `.env` com os dados do MySQL da HostGator
- Via Terminal/SSH (se disponível), rode:
  ```bash
  composer install --no-dev
  php artisan key:generate
  php artisan migrate --force
  php artisan storage:link
  ```

### B) Rodar no domínio raiz (public_html)
Se você precisar usar diretamente `public_html`, existem jeitos de ajustar o caminho do `public/`.
Sugestão: use subdomínio para evitar dor de cabeça.

## Upload de arquivos (fotos e logos)
- Os uploads vão para `storage/app/public`
- O comando `php artisan storage:link` cria o link público `public/storage`

## PDF
- Usa `barryvdh/laravel-dompdf` para exportar a mesma view da apresentação.
- Caso a HostGator limite memória/tempo, a exportação pode precisar de ajustes.

## Próximos passos (após MVP)
- Inserção por link com extração (scrapers/adapters)
- Fila de processamento (jobs) para extrair links sem travar a tela
- Mapa com geocoding melhor (cache e normalização de endereço)
- Templates diferentes de apresentação (modelos)

