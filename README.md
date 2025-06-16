# app_GAC

Sistema de Gestão de Atividades Complementares desenvolvido como parte da disciplina de Engenharia de Software no curso de Ciências da Computação.


**Product Owner (PO)**  
- Davi Gledson da Silva Benedito  

**Scrum Master**  
- Jorge Luiz Silva Braz  

**Developers Team**  
- Davi Gledson da Silva Benedito  

- Matheus Lucas Dantas Lopes  

- Ewerson de Souza Junior  

## Clonando o Repositório

```sh
git clone https://github.com/davigledson/app_GAC.git
cd app_GAC

```


### 1. Instale as dependências PHP:
```sh
composer install

```
### 2. Copie o arquivo de exemplo de variáveis de ambiente

```sh
cp .env.example .env

```

### 3. Gere a chave da aplicação

```sh
php artisan key:generate

```

### 4. Configure o banco de dados no arquivo .env:

### se for usar MySQL
```sh
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario
DB_PASSWORD=senha

```
### se for usar PostgreSQL
```sh
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario
DB_PASSWORD=senha

```


#### Observações:
- `DB_CONNECTION`: Define o driver do banco de dados
- `DB_HOST`: O endereço do servidor do banco de dados; geralmente 127.0.0.1 para desenvolvimento local.
- `DB_PORT`: A porta padrão do PostgreSQL é `5432` e do MySQL é `3306`
- `DB_DATABASE:`: Nome do banco de dados que você criou.
- `DB_USERNAME`: Credencial de acesso ao banco de dados.
- `DB_PASSWORD`: Credencial de acesso ao banco de dados.


### 5. Execute as migrações:

```sh
php artisan migrate


```
### 6. Acesse o painel:
#### Crie um superusuario:
```sh
php artisan make:filament-user

```
#### Faça login:

```sh

http://127.0.0.1:8000/admin/login

```
## Dependências do Projeto


- PHP >= `8.2`

- Laravel Framework: `^12.0`

- Filament: `3.3`

- Laravel Tinker: `^2.10.1`


##  Tecnologias Utilizadas

- Laravel 12

- PHP 8.2

- MySQL ou PostgreSQL

- Filament Admin Panel

- Livewire

- tailwind

###  Scripts Úteis

|       |        |
|-------|-------|
| php artisan serve  |  # Inicia o servidor local  
| php artisan migrate|  # Executa as migrações 
|php artisan db:seed  | # Popula o banco com dados fictícios  
| composer test|  # Roda os testes unitários (PHPUnit) 
| vendor/bin/pint  |  # Formata o código (Laravel Pint)  
| php artisan pail |   # Visualiza os logs (Laravel Pail)
