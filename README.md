# SMNT Online - Sistema de Gestão de Frotas e Manutenção de Viaturas
> **Corpo de Bombeiros Militar do Estado de Mato Grosso (CBMMT)**  
> *Vehicle Fleet Management & Maintenance System*

[![Language: PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![Database: MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Frontend: Bootstrap](https://img.shields.io/badge/Frontend-Bootstrap_5-7952B3?style=flat&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![Security: JWT](https://img.shields.io/badge/Auth-JWT-000000?style=flat&logo=jsonwebtokens&logoColor=white)](https://jwt.io/)
[![Architecture: DAO Pattern](https://img.shields.io/badge/Architecture-DAO_Pattern-brightgreen?style=flat)](#)

---

### Idiomas / Languages
- [Português (Brasil)](#-português-brasil)
- [English](#-english)

---

## 🇧🇷 Português (Brasil)

### Sobre o Projeto
O **SMNT Online** é uma plataforma corporativa para controle, fiscalização e gestão do ciclo de vida das viaturas operacionais e administrativas do CBMMT. O sistema abrange desde cautelas de viaturas, controle de condutores e manutenções preventivas/corretivas até o registro de avarias com evidências fotográficas, relatórios em PDF e auditoria em tempo real.

### Destaques de Arquitetura e Engenharia de Software
O projeto foi estruturado seguindo boas práticas de engenharia de software voltadas para alta manutenibilidade, segurança e desacoplamento:
- **Padrão DAO (Data Access Object):** Separação estrita entre regras de negócio e persistência SQL. Toda comunicação com o banco ocorre por meio de DAOs dedicados via `PDO` com *Prepared Statements* (prevenção nativa contra SQL Injection).
- **Backend Orientado a API RESTful:** Endpoints modulares em PHP retornando respostas estritamente em JSON (`json_encode()`), com tratamento de códigos de status HTTP semânticos (200, 400, 401, 403, 500).
- **Frontend Assíncrono e Reativo:** Interface baseada em Bootstrap 5 consumindo as APIs através de `Fetch API` assíncrona, desacoplando a interface gráfica da camada de processamento.
- **Autenticação Segura via JWT:** Sessões autenticadas através de JSON Web Tokens (`firebase/php-jwt`), com validação rigorosa de expiração, claims de usuário e middleware de proteção.
- **Gerenciamento de Ambiente (.env):** Credenciais de banco de dados e segredos de criptografia isolados em variáveis de ambiente, nunca expostos no código-fonte nem versionados no repositório.
- **Auditoria e Logs Estruturados:** Rastreabilidade de ações críticas (operações CRUD) integrada com a biblioteca `Monolog`, registrando identificação de usuário, perfil e entidade afetada.
- **Gerenciamento de Dependências com Composer:** Conformidade com o padrão PSR-4 para carregamento automático de classes (`autoload`).

### Principais Módulos do Sistema
1. **Gestão de Viaturas:** Cadastro técnico, situação operacional, quilometragem, histórico e transferências de unidade.
2. **Cautela e Descautela:** Fluxo completo de entrega e devolução de viaturas para missões com geração de termo de responsabilidade em PDF via `Dompdf`.
3. **Plano de Manutenção (Preventiva e Corretiva):** Acompanhamento de revisões periódicas, cronogramas por quilometragem ou tempo e controle de oficinas credenciadas.
4. **Registro e Gestão de Avarias:** Apuração de danos, fotos de vistorias e relatórios periciais.
5. **Controle de Condutores e Acessórios:** Habilitação, permissões de direção militar e monitoramento de equipamentos embarcados.
6. **Dashboard & Notificações:** Visão executiva em tempo real com alertas de manutenções vencidas e cautelas em aberto.

### Estrutura de Diretórios
```text
├── .env.example             # Modelo de variáveis de ambiente
├── .gitignore               # Configurações de exclusão do Git
├── composer.json            # Dependências e mapeamento PSR-4
├── index.php                # Ponto de entrada / redirecionamento
├── login.php                # Tela de autenticação
├── principal.php            # Shell da aplicação autenticada
├── api/                     # Endpoints da API RESTful (Backend)
├── assets/                  # Imagens e recursos estáticos
├── components/              # Camada de lógica e serviços
│   ├── Auth/                # Handler JWT e segurança
│   ├── ConexaoDB/           # Conexão PDO (Singleton)
│   ├── Config/              # Carregador de variáveis de ambiente (.env)
│   ├── DAO/                 # Classes de Acesso a Dados (DAO Pattern)
│   └── Log/                 # Serviço de auditoria e logs estruturados
├── css/                     # Estilos customizados e utilitários
├── js/                      # Scripts e lógica client-side (Fetch API)
├── pages/                   # Visualizações e templates dos módulos
└── templates/               # Componentes visuais compartilhados (Header/Sidebar/Footer)
```

### Instalação e Execução Local

#### Pré-requisitos
- PHP 8.1 ou superior (com extensões `pdo_mysql`, `mbstring`, `json`, `openssl`)
- MySQL 8.0+ ou MariaDB
- Composer 2+
- Servidor Web Apache / Nginx ou servidor embutido do PHP

#### Passo a Passo
1. **Clonar o Repositório:**
   ```bash
   git clone https://github.com/rafaelreisGH/manutencao.git
   cd manutencao
   ```

2. **Instalar Dependências via Composer:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Configurar as Variáveis de Ambiente:**
   Copie o arquivo `.env.example` para `.env` e configure as credenciais da sua base de dados e a chave JWT:
   ```bash
   cp .env.example .env
   ```
   Edite o `.env`:
   ```dotenv
   DB_HOST=localhost
   DB_NAME=controle_viaturas
   DB_USER=seu_usuario
   DB_PASS=sua_senha
   JWT_SECRET=sua_chave_secreta_jwt_longa_e_forte
   ```

4. **Executar a Aplicação:**
   Caso esteja utilizando o servidor embutido do PHP:
   ```bash
   php -S localhost:8000
   ```
   Acesse no navegador: `http://localhost:8000`

---

## 🇺🇸 English

### About the Project
**SMNT Online** is an enterprise-grade vehicle fleet management and maintenance system developed for the Military Fire Department of the State of Mato Grosso (**CBMMT**). The application covers the complete lifecycle of operational and administrative vehicles, including vehicle checkouts (cautelas), driver credentialing, preventive and corrective maintenance scheduling, damage inspections with photo uploads, PDF reports, and real-time activity auditing.

### Architecture & Software Engineering Highlights
The codebase was designed with emphasis on decoupling, security, and maintainability:
- **Data Access Object (DAO) Pattern:** Clear separation of concerns between business logic and database access. All persistence operations are isolated in dedicated DAO classes using `PDO` with Prepared Statements, eliminating SQL Injection vulnerabilities.
- **RESTful API Backend:** Modular PHP backend endpoints providing strictly JSON responses (`json_encode()`), adhering to semantic HTTP status codes (200, 400, 401, 403, 500).
- **Asynchronous & Reactive Frontend:** Clean UI crafted with Bootstrap 5 interacting with backend services via modern JavaScript `Fetch API`, preventing unnecessary full-page reloads.
- **Stateless Authentication with JWT:** Robust security powered by JSON Web Tokens (`firebase/php-jwt`), featuring token expiration enforcement, secure payload claims, and auth middleware.
- **Environment Configuration (.env):** Zero hardcoded credentials. Database settings and application secrets are safely resolved from environment variables using a custom [EnvLoader.php](file:///c:/Users/01116875195/Documents/Projetos/manutencao/components/Config/EnvLoader.php).
- **Audit Trails & Structured Logging:** Complete auditability for all critical state changes (CRUD) backed by `Monolog`, tracking user IDs, profiles, and subject entities.
- **Composer & PSR-4 Autoloading:** Modern dependency management and standardized namespace loading.

### Key Modules
1. **Fleet Management:** Vehicle registration, status tracking, mileage records, and inter-unit transfers.
2. **Vehicle Check-in / Check-out (Cautelas):** Formal custody workflow with digital signatures and instant PDF issuance using `Dompdf`.
3. **Preventive & Corrective Maintenance:** Periodical revision plans based on odometer readings or time intervals, plus authorized mechanic shop management.
4. **Damage & Incident Reports:** Damage investigation logs with photographic evidence.
5. **Drivers & Equipment Inventory:** Driving licenses, military permissions, and vehicle onboard accessories.
6. **Executive Dashboard & Real-Time Alerts:** High-level metrics, maintenance notifications, and pending handover reminders.

### Directory Structure
```text
├── .env.example             # Sample environment configuration
├── .gitignore               # Git ignore rules for sensitive data and builds
├── composer.json            # Project dependencies and PSR-4 mapping
├── index.php                # Entrypoint & redirection
├── login.php                # Authentication interface
├── principal.php            # Main authenticated application shell
├── api/                     # Backend RESTful API endpoints
├── assets/                  # Static assets and UI illustrations
├── components/              # Core business services and abstractions
│   ├── Auth/                # JWT Token handling and validation
│   ├── ConexaoDB/           # PDO database connection (Singleton)
│   ├── Config/              # Environment loader (.env)
│   ├── DAO/                 # Data Access Object implementations
│   └── Log/                 # Monolog audit logger service
├── css/                     # Project stylesheets
├── js/                      # Frontend scripts and async Fetch API calls
├── pages/                   # Module views and UI components
└── templates/               # Shared UI templates (Header/Sidebar/Footer)
```

### Getting Started

#### Prerequisites
- PHP 8.1+ with `pdo_mysql`, `mbstring`, `json`, and `openssl` extensions enabled
- MySQL 8.0+ or MariaDB
- Composer 2+
- Web server (Apache, Nginx, or PHP Built-in Server)

#### Step-by-Step Installation
1. **Clone the Repository:**
   ```bash
   git clone https://github.com/rafaelreisGH/manutencao.git
   cd manutencao
   ```

2. **Install Dependencies:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Configure Environment Variables:**
   Create your local `.env` configuration file from the template:
   ```bash
   cp .env.example .env
   ```
   Configure your database credentials and secret key:
   ```dotenv
   DB_HOST=localhost
   DB_NAME=controle_viaturas
   DB_USER=your_db_user
   DB_PASS=your_db_password
   JWT_SECRET=your_super_secret_and_long_jwt_key
   ```

4. **Run the Development Server:**
   ```bash
   php -S localhost:8000
   ```
   Open your browser at `http://localhost:8000`.

---

### Author / Autor
Desenvolvido por **Rafael Corrêa dos Reis**  
Seção de Tecnologia da Informação e Comunicação (STIC) — CBMMT  
📧 Contato: [rafael.infosys@gmail.com](mailto:rafael.infosys@gmail.com)
