-- ============================================================
-- Migration 004: Garantir que os depoimentos fictícios estejam ativos
-- Em desenvolvimento eles aparecem mesmo com autorizacao_lgpd = 0
-- Em produção defina APP_ENV=production para aplicar o filtro LGPD
-- ============================================================
UPDATE `depoimentos` SET `ativo` = 1 WHERE `id` IN (1, 2, 3);
