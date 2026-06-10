-- ============================================================
-- Trivya RH — Dados Iniciais (Seed)
-- Versão: 1.0.0  |  Data: 2025-01-01
--
-- ATENÇÃO: Execute SOMENTE em instalação nova ou após resetar o banco.
-- Os hashes abaixo correspondem a uma senha padrão de DESENVOLVIMENTO.
-- Em produção, REMOVA ou altere as senhas imediatamente após o primeiro
-- acesso (Admin → Usuários → trocar senha).
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = "+00:00";

-- ============================================================
-- ADMINS
-- ============================================================

INSERT INTO `admins` (`nome`, `email`, `senha_hash`, `role`, `ativo`) VALUES

-- Super Administrador — acesso total
('Eduardo', 'eduardo@trivyarh.com.br',
 '$2y$12$kwuqLyymMEYQ5RSzsSdAEOZPsIUgaSw6BfqCkxcyYE8H8JYgJ.r0.',
 'super_admin', 1),

-- Administradora — acesso completo ao painel
('Vitória', 'vitoria@trivyarh.com.br',
 '$2y$12$kwuqLyymMEYQ5RSzsSdAEOZPsIUgaSw6BfqCkxcyYE8H8JYgJ.r0.',
 'admin', 1),

-- Administradora — acesso completo ao painel
('Maria Luiza', 'marialuiza@trivyarh.com.br',
 '$2y$12$kwuqLyymMEYQ5RSzsSdAEOZPsIUgaSw6BfqCkxcyYE8H8JYgJ.r0.',
 'admin', 1);

-- ============================================================
-- CONFIGURAÇÕES GLOBAIS
-- ============================================================

INSERT INTO `configuracoes` (`chave`, `valor`, `descricao`, `grupo`, `tipo`) VALUES

-- Contato e informações da empresa
('whatsapp',                '5511999999999',                     'WhatsApp no formato internacional (sem +)', 'contato', 'telefone'),
('telefone_exibicao',       '(11) 9 9999-9999',                  'Telefone formatado para exibição no site',  'contato', 'telefone'),
('email_contato',           'contato@trivyarh.com.br',           'E-mail exibido no site',                    'contato', 'email'),
('email_notificacoes',      'contato@trivyarh.com.br',           'E-mail que recebe notificações internas',   'contato', 'email'),
('endereco',                'São Paulo, SP',                      'Endereço ou cidade de atuação',             'contato', 'texto'),
('horario_atendimento',     'Seg a Sex, das 9h às 18h',          'Horário de atendimento',                    'contato', 'texto'),

-- Redes sociais
('instagram_url',           'https://instagram.com/trivyarh',    'URL do perfil no Instagram',                'redes_sociais', 'url'),
('linkedin_url',            'https://linkedin.com/company/trivyarh', 'URL da página no LinkedIn',             'redes_sociais', 'url'),
('facebook_url',            '',                                   'URL da página no Facebook',                 'redes_sociais', 'url'),

-- SEO global
('meta_title_home',         'Trivya RH | Recrutamento e Seleção em São Paulo',
                            'Meta title da home (até 60 chars)', 'seo', 'texto'),
('meta_description_home',
    'Consultoria de recrutamento e seleção em São Paulo. Conectamos talentos às melhores empresas. Conheça nossos serviços.',
    'Meta description da home (até 160 chars)', 'seo', 'texto'),

-- Configurações do blog
('blog_ativo',              '1',                                  '1 = blog visível no site',                  'geral', 'booleano'),
('posts_por_pagina',        '9',                                  'Posts listados por página no blog',         'geral', 'numero'),

-- LGPD
('versao_politica_privacidade', '1.0',                           'Versão atual da Política de Privacidade',   'lgpd', 'texto'),
('url_politica_privacidade',    '/politica-privacidade',         'URL da página de Política de Privacidade',  'lgpd', 'url'),

-- Analytics
('ga4_measurement_id',      '',                                   'ID do Google Analytics 4 (G-XXXXXXXXXX)',  'analytics', 'texto'),
('recaptcha_site_key',      '',                                   'Chave pública do reCAPTCHA v3',             'seguranca', 'texto');

-- ============================================================
-- CONTEÚDO DO SITE (Seção: hero)
-- ============================================================

INSERT INTO `conteudo` (`secao`, `campo`, `valor`, `tipo`, `ordem`) VALUES

-- Hero (banner principal)
('hero', 'titulo',          'Conectamos os melhores talentos às melhores empresas',  'texto', 1),
('hero', 'subtitulo',       'Consultoria especializada em recrutamento e seleção em São Paulo', 'texto', 2),
('hero', 'cta_empresa',     'Contratar Agora',                                       'texto', 3),
('hero', 'cta_candidato',   'Enviar Currículo',                                      'texto', 4),
('hero', 'imagem_fundo',    '/assets/img/hero-bg.jpg',                               'imagem', 5),

-- Sobre
('sobre', 'titulo',         'Sobre a Trivya RH',                                     'texto', 1),
('sobre', 'texto_principal',
    'A Trivya RH é uma consultoria de recrutamento e seleção com foco em conectar profissionais talentosos '
    'às empresas certas. Com um processo seletivo criterioso e personalizado, garantimos a melhor experiência '
    'tanto para candidatos quanto para empresas parceiras.',
    'texto', 2),
('sobre', 'missao',
    'Nossa missão é transformar carreiras e fortalecer equipes por meio de processos seletivos eficientes, '
    'humanizados e alinhados à cultura organizacional de cada cliente.',
    'texto', 3),
('sobre', 'visao',          'Ser referência em consultoria de RH em São Paulo até 2027.',             'texto', 4),
('sobre', 'valores',        'Ética, Transparência, Excelência, Humanização e Resultado.',              'texto', 5),
('sobre', 'anos_mercado',   '5',                                                     'numero', 6),
('sobre', 'clientes_atendidos', '200',                                               'numero', 7),
('sobre', 'vagas_preenchidas',  '1500',                                              'numero', 8),
('sobre', 'taxa_retencao',  '92',                                                    'numero', 9),

-- Serviços
('servicos', 'titulo',           'Nossos Serviços',                                  'texto', 1),
('servicos', 'subtitulo',        'Soluções completas de RH para empresas de todos os portes', 'texto', 2),
('servicos', 'recrutamento_titulo',   'Recrutamento & Seleção',                      'texto', 3),
('servicos', 'recrutamento_texto',
    'Processo seletivo completo: desde a definição do perfil ideal até a contratação. '
    'Triagem de currículos, entrevistas técnicas e comportamentais, aplicação de testes.',
    'texto', 4),
('servicos', 'headhunting_titulo',    'Headhunting Executivo',                        'texto', 5),
('servicos', 'headhunting_texto',
    'Busca ativa e discreta de profissionais de alta performance para cargos de liderança e especialistas.',
    'texto', 6),
('servicos', 'banco_talentos_titulo', 'Banco de Talentos',                            'texto', 7),
('servicos', 'banco_talentos_texto',
    'Mantemos um banco ativo de profissionais qualificados em diversas áreas, '
    'acelerando o processo de contratação para nossos clientes.',
    'texto', 8),
('servicos', 'consultoria_titulo',    'Consultoria de RH',                            'texto', 9),
('servicos', 'consultoria_texto',
    'Suporte estratégico em descrição de cargos, estruturação de carreiras, '
    'onboarding e políticas de retenção de talentos.',
    'texto', 10),

-- Diferenciais
('diferenciais', 'titulo',       'Por que escolher a Trivya RH?',                   'texto', 1),
('diferenciais', 'item1_titulo', 'Processo Humanizado',                              'texto', 2),
('diferenciais', 'item1_texto',  'Cada candidato é tratado com respeito e atenção individual.', 'texto', 3),
('diferenciais', 'item2_titulo', 'Agilidade',                                        'texto', 4),
('diferenciais', 'item2_texto',  'Shortlist de candidatos qualificados em até 5 dias úteis.',   'texto', 5),
('diferenciais', 'item3_titulo', 'Garantia de Resultado',                            'texto', 6),
('diferenciais', 'item3_texto',  'Reposição gratuita se o candidato não passar pelo período de experiência.', 'texto', 7),
('diferenciais', 'item4_titulo', 'Expertise no Mercado',                             'texto', 8),
('diferenciais', 'item4_texto',  'Especialistas em múltiplos segmentos: tech, varejo, indústria e serviços.', 'texto', 9),

-- CTA (Call to Action)
('cta', 'titulo_empresa',    'Precisa contratar? Fale com a Trivya RH',             'texto', 1),
('cta', 'texto_empresa',     'Entre em contato e receba uma proposta personalizada para sua empresa.', 'texto', 2),
('cta', 'botao_empresa',     'Solicitar Proposta',                                   'texto', 3),
('cta', 'titulo_candidato',  'Em busca de novas oportunidades?',                     'texto', 4),
('cta', 'texto_candidato',   'Cadastre seu currículo e seja encontrado pelas melhores empresas.', 'texto', 5),
('cta', 'botao_candidato',   'Cadastrar Currículo',                                  'texto', 6),

-- Footer
('footer', 'texto_sobre',
    'Consultoria especializada em recrutamento e seleção em São Paulo.',
    'texto', 1),
('footer', 'copyright',     '© 2025 Trivya RH. Todos os direitos reservados.',      'texto', 2),
('footer', 'cnpj',          'CNPJ: XX.XXX.XXX/0001-XX',                             'texto', 3);

-- ============================================================
-- DEPOIMENTOS
-- ============================================================

INSERT INTO `depoimentos` (`nome`, `cargo`, `texto`, `nota`, `ativo`, `ordem`) VALUES

('Ana Lima', 'Diretora de RH — TechCorp SP',
 'A Trivya RH superou nossas expectativas. Em menos de uma semana tivemos 3 finalistas para a vaga sênior. '
 'O processo foi transparente e os candidatos já chegaram bem alinhados com nossa cultura.',
 5, 1, 1),

('Rodrigo Mendes', 'Analista de Marketing — Contratado pela Trivya',
 'Estava há 4 meses buscando recolocação. A Trivya me apresentou a uma empresa incrível que nem estava '
 'no meu radar. Hoje estou na melhor posição da minha carreira!',
 5, 1, 2),

('Fernanda Costa', 'CEO — Grupo Inovar',
 'Excelente custo-benefício. A consultoria entendeu nosso negócio, apresentou profissionais que realmente '
 'queriam trabalhar conosco. Nosso turnover caiu significativamente após as contratações.',
 5, 1, 3);

-- ============================================================
-- POST INICIAL DO BLOG
-- ============================================================

INSERT INTO `posts` (`titulo`, `slug`, `resumo`, `conteudo`, `status`, `categoria`, `admin_id`, `publicado_em`) VALUES

('Como se destacar em processos seletivos em 2025',
 'como-se-destacar-processos-seletivos-2025',
 'Dicas práticas de especialistas em RH para você se preparar melhor e aumentar suas chances de contratação em um mercado cada vez mais competitivo.',
 '<h2>O mercado de trabalho em 2025</h2>
<p>O mercado de trabalho está em constante transformação. Com o avanço da tecnologia e a digitalização das empresas, os profissionais precisam se reinventar continuamente.</p>
<h2>Dicas dos nossos especialistas</h2>
<h3>1. Currículo objetivo e atualizado</h3>
<p>Mantenha seu currículo com no máximo 2 páginas, com foco nos resultados que você gerou — não apenas nas responsabilidades.</p>
<h3>2. LinkedIn ativo</h3>
<p>Ter um perfil completo e ativo no LinkedIn aumenta em até 70% suas chances de ser encontrado por recrutadores.</p>
<h3>3. Prepare-se para entrevistas comportamentais</h3>
<p>Use o método STAR (Situação, Tarefa, Ação, Resultado) para estruturar suas respostas.</p>
<h2>Conclusão</h2>
<p>O candidato que investe em autoconhecimento e preparo tem muito mais chances de se destacar. A Trivya RH pode te ajudar nessa jornada!</p>',
 'publicado', 'Dicas de Carreira', 1, NOW());
