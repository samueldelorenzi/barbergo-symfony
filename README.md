# BarberGo - Symfony

BarberGo é uma aplicação web desenvolvida com o framework PHP Symfony que conecta barbeiros e clientes de forma simples e intuitiva.

## 🛠 Tecnologias

* **PHP** 8.2+
* **Symfony** 7.2
* **MySQL** 8.0+
* **Composer** (para dependências PHP)
* **Doctrine ORM** (abstração de banco de dados)
* **Twig** (motor de templates)
* **Symfony Forms**
* **Security Bundle** (autenticação)

## 🚀 Instalação

1. Clone o repositório:

```bash
git clone https://github.com/samueldelorenzi/barbergo-symfony.git
cd barbergo-symfony
```

2. Instale as dependências PHP:

```bash
composer install
```

## 🗄 Configuração do Banco de Dados

1. Configure sua conexão com o banco de dados no arquivo `.env`:

```ini
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/barbergo?serverVersion=8.0"
```

2. Crie o banco de dados:

```bash
php bin/console doctrine:database:create
```

3. Execute as migrações:

```bash
php bin/console doctrine:migrations:migrate
```

## 🧪 Fixtures

Carregue dados de exemplo com o Doctrine Fixtures:

```bash
php bin/console doctrine:fixtures:load
```

Isso irá popular seu banco de dados com:

* Usuários de exemplo (clientes, barbeiros, administradores)
* Barbearias
* Serviços
* Agendamentos
* Horários

## 🏃 Executando a Aplicação

Inicie o servidor de desenvolvimento do Symfony:

```bash
symfony server:start
```

Em seguida, acesse a aplicação em:
[http://127.0.0.1:8000/](http://127.0.0.1:8000/)

## 📁 Estrutura de Diretórios

```
barbergo-symfony/
├── assets/              # Arquivos frontend (JS, CSS, imagens)
├── bin/                 # Comandos de console
├── config/              # Configuração da aplicação
├── migrations/          # Migrações do banco de dados
├── public/              # Raiz pública do projeto
├── src/
│   ├── Controller/      # Controladores da aplicação
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   └── UserController.php
│   │   ├── API/
│   │   │   ├── BarberController.php
│   │   │   ├── ScheduleController.php
│   │   │   └── ServiceController.php
│   │   ├── Auth/
│   │   │   ├── LoginController.php
│   │   │   └── RegisterController.php
│   │   ├── Barber/
│   │   │   ├── AppointmentController.php
│   │   │   ├── BarbershopController.php
│   │   │   └── DashboardController.php
│   │   ├── Client/
│   │   │   ├── AppointmentController.php
│   │   │   └── DashboardController.php
│   │   ├── Home/
│   │   │   └── HomeController.php
│   │   └── User/
│   │       └── ProfileController.php
│   ├── Entity/          # Entidades do banco de dados
│   ├── Form/            # Tipos de formulários
│   ├── Repository/      # Repositórios personalizados  
│   ├── Security/        # Autenticação
│   └── DataFixtures/    # Dados de exemplo
├── templates/           # Templates Twig
│   ├── admin/           # Interface administrativa
│   ├── auth/            # Login/registro
│   ├── barber/          # Painel do barbeiro
│   ├── client/          # Interface do cliente
│   └── components/      # Componentes de UI
├── tests/               # Testes
└── translations/        # Arquivos de tradução
```

## 📜 Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para mais detalhes.

---

Desenvolvido com ❤️ usando Symfony 7.2
