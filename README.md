# Trivya RH — Sistema de Gestão e Site Institucional

Plataforma completa para a consultoria de recrutamento e seleção **Trivya RH** (São Paulo).
Composta por site institucional público + painel administrativo para gestão de leads, candidatos e conteúdo.

---

## Status do Projeto

| Fase | Descrição | Status |
|------|-----------|--------|
| **FASE 1** | Base estrutural (config, banco, bibliotecas) | ✅ **Concluída** |
| FASE 2 | Layout e templates HTML (header, footer, CSS) | ⏳ Aguardando |
| FASE 3 | Páginas públicas (home, sobre, serviços, contato) | ⏳ Aguardando |
| FASE 4 | Formulários (contato, currículo, LGPD) + API | ⏳ Aguardando |
| FASE 5 | Painel admin (dashboard, leads, candidatos, blog) | ⏳ Aguardando |
| FASE 6 | SEO, otimização, deploy na Hostinger | ⏳ Aguardando |

---

## Estrutura do projeto

A estrutura é a mesma em local (XAMPP) e em produção (Hostinger):
`public_html/` é o document root (o que o Apache serve) e `trivya-protected/`
fica ao lado, fora do alcance do navegador, com tudo que é sensível.

```
trivya-rh/
├── .env.example            → Template de variáveis de ambiente
├── .gitignore
├── README.md
│
├── public_html/            → Document root (Apache/XAMPP e Hostinger)
│   ├── bootstrap.php        → Carrega config/includes de trivya-protected/
│   ├── .htaccess
│   ├── index.php, 404.php, 500.php, sitemap.php, etc.
│   ├── admin/                → Painel administrativo
│   ├── api/                  → Endpoints de formulários (contato, candidato)
│   ├── assets/               → CSS, JS, imagens
│   └── uploads/curriculos/   → Currículos enviados pelos candidatos
│
└── trivya-protected/        → Arquivos sensíveis (fora do document root)
    ├── .env                  → Variáveis de ambiente (não vai pro Git)
    ├── config/
    │   ├── env.php           → Carregador de .env (parser próprio, sem biblioteca)
    │   ├── config.php        → Constantes globais (SITE_URL, DB_*, SMTP_*, etc.)
    │   ├── constants.php     → Constantes de segurança e aplicação
    │   ├── database.php      → Classe Database Singleton (PDO / utf8mb4)
    │   └── session.php       → Sessão segura (HttpOnly, Secure, SameSite Lax)
    ├── includes/
    │   ├── functions.php     → redirect(), e(), getConfig(), formatTelefone(), slug, flash
    │   ├── sanitizers.php    → sanitizeString(), sanitizeEmail(), sanitizeTelefone(), etc.
    │   ├── validators.php    → validarEmail(), validarCNPJ(), validarUpload(), etc.
    │   ├── csrf.php          → generateCsrf(), validateCsrf(), csrfField()
    │   ├── auth.php          → login(), logout(), isLoggedIn(), requireAuth()
    │   ├── logger.php        → Logger::info/warning/error/critical (banco + arquivo)
    │   └── mailer.php        → sendEmail() via PHPMailer + templateEmail()
    ├── lib/PHPMailer/
    │   ├── PHPMailer.php     → Classe principal (standalone, sem Composer)
    │   ├── SMTP.php          → Driver SMTP com STARTTLS/SSL
    │   └── Exception.php     → Exceção personalizada do PHPMailer
    ├── database/
    │   ├── schema.sql        → Tabelas InnoDB / utf8mb4 com foreign keys
    │   ├── schema_update.sql → Tabelas/colunas adicionais (servicos, nichos_marquee, reset_token)
    │   ├── seed.sql          → Admins, configurações, conteúdo e depoimentos
    │   └── migrations/       → Migrations incrementais
    └── logs/
        └── .gitkeep
```

---

## Como configurar localmente (XAMPP)

### 1. Configurar o vhost `trivyarh.local`

Em `c:\xampp\apache\conf\extra\httpd-vhosts.conf`, aponte o `DocumentRoot`
para `public_html/` e adicione `127.0.0.1 trivyarh.local` no
`C:\Windows\System32\drivers\etc\hosts`. Reinicie o Apache.

### 2. Copiar e configurar o .env

```bash
copy .env.example trivya-protected\.env
```

Abra `trivya-protected/.env` e preencha:

```env
DB_HOST=localhost
DB_NAME=trivya_rh
DB_USER=root
DB_PASS=          # sua senha do MySQL local
APP_URL=http://trivyarh.local
APP_ENV=development
APP_DEBUG=true
```

### 3. Criar o banco de dados no phpMyAdmin

1. Acesse `http://localhost/phpmyadmin`
2. Crie um banco chamado `trivya_rh` com charset `utf8mb4` e collation `utf8mb4_unicode_ci`
3. Selecione o banco `trivya_rh`
4. Importe, nesta ordem: `trivya-protected/database/schema.sql`,
   `schema_update.sql`, `seed.sql` e os arquivos em `migrations/`

### 4. Verificar permissões

- A pasta `trivya-protected/logs/` precisa ter permissão de escrita pelo servidor
- A pasta `public_html/uploads/curriculos/` precisa de permissão de escrita

### 5. Acessar o sistema

- **Site público:** `http://trivyarh.local/`
- **Painel admin:** `http://trivyarh.local/admin/`

**Admins de desenvolvimento (criados pelo `seed.sql`):**

| Nome | E-mail | Role |
|------|--------|------|
| Eduardo | eduardo@trivyarh.com.br | super_admin |
| Vitória | vitoria@trivyarh.com.br | admin |
| Maria Luiza | marialuiza@trivyarh.com.br | admin |

> A senha está nos hashes do `seed.sql` (apenas para ambiente local). Em
> produção, troque a senha de cada admin pelo painel logo após o primeiro acesso.

---

## Tecnologias utilizadas

- **PHP 8.1+** com PDO MySQL
- **MySQL 8.0+** (InnoDB, utf8mb4)
- **PHPMailer 6.9** (standalone, sem Composer)
- **Bootstrap 5** *(Fase 2)*
- **Sem frameworks, sem Composer, sem Node.js**

---

## Compatibilidade

- ✅ XAMPP (Windows/Linux) — desenvolvimento local
- ✅ Hostinger Premium (servidor compartilhado PHP 8.1+)
- ✅ LGPD — consentimentos registrados na tabela `consentimentos_lgpd`
- ✅ PHP 8.1 e 8.2

---

## Segurança implementada

- Senhas com bcrypt cost 12
- Proteção contra força bruta (bloqueio de IP após 5 tentativas)
- Tokens CSRF em todos os formulários
- Sessões seguras (HttpOnly, Secure, SameSite Lax)
- Prepared statements (100% das queries via PDO)
- Sanitização e validação de todos os inputs
- Registro de consentimento LGPD com evidência auditável

---

## Workflow de deploy (Git)

O servidor (`domains/trivyarh.com.br/`) é um clone deste repositório. Para
publicar uma alteração:

```bash
# 1. Local — commitar e enviar para o GitHub
git add .
git commit -m "Descrição da mudança"
git push

# 2. No servidor (via PuTTY/SSH)
cd ~/domains/trivyarh.com.br
git pull origin main
```

`trivya-protected/.env`, `trivya-protected/logs/`,
`public_html/uploads/curriculos/` e backups de banco são ignorados pelo Git
(ver `.gitignore`) e não são afetados pelo `git pull`.

---

## Próximos passos (Fase 2)

- Criar layout base com Bootstrap 5
- Definir paleta de cores e tipografia da Trivya RH
- Implementar header, footer, menu responsivo
- Criar sistema de templates PHP
