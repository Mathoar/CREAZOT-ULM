-- Patterns SMS sous moteur d'intégration générique
-- Idempotent : suppression préalable si déjà présents
DELETE FROM integration_variable
  WHERE pattern_id IN (SELECT id FROM integration_pattern WHERE code IN ('twilio_sms', 'messagebird_sms'));
DELETE FROM integration_pattern_client
  WHERE integration_pattern_id IN (SELECT id FROM integration_pattern WHERE code IN ('twilio_sms', 'messagebird_sms'));
DELETE FROM integration_pattern WHERE code IN ('twilio_sms', 'messagebird_sms');

-- ============================================================
-- Pattern 1 : Twilio SMS
-- ============================================================
INSERT INTO integration_pattern
  (name, code, capability, required_module, method, url_template, headers, query_params, body_template, content_type, description, active, cache_ttl, fallback_url_template, created_at, updated_at)
VALUES (
  'Twilio SMS',
  'twilio_sms',
  'sms_send',
  'hasSMS',
  'POST',
  'https://api.twilio.com/2010-04-01/Accounts/{{twilio_account_sid}}/Messages.json',
  '[{"name":"Authorization","value":"Basic {{twilio_basic_auth}}"}]'::json,
  NULL,
  'To={{to}}&From={{sender_id}}&Body={{body}}&StatusCallback=https%3A%2F%2Flogic-ciel.com%2Fwebhook%2Ftwilio%2Fstatus',
  'application/x-www-form-urlencoded',
  'Envoi SMS via Twilio (capability sms_send). Authentication Basic Auth depuis SiteSettings.twilioBasicAuth (calculé à la volée).',
  TRUE,
  NULL,
  NULL,
  NOW(),
  NOW()
);

INSERT INTO integration_variable (pattern_id, variable_name, source, source_field, default_value, required) VALUES
  ((SELECT id FROM integration_pattern WHERE code = 'twilio_sms'), 'twilio_account_sid', 'site_settings', 'twilioAccountSid', NULL, TRUE),
  ((SELECT id FROM integration_pattern WHERE code = 'twilio_sms'), 'twilio_basic_auth',  'site_settings', 'twilioBasicAuth',  NULL, TRUE),
  ((SELECT id FROM integration_pattern WHERE code = 'twilio_sms'), 'to',                 'context',       'to',               NULL, TRUE),
  ((SELECT id FROM integration_pattern WHERE code = 'twilio_sms'), 'body',               'context',       'body',             NULL, TRUE),
  ((SELECT id FROM integration_pattern WHERE code = 'twilio_sms'), 'sender_id',          'context',       'sender_id',        NULL, TRUE);

-- ============================================================
-- Pattern 2 : MessageBird (Bird) SMS
-- ============================================================
INSERT INTO integration_pattern
  (name, code, capability, required_module, method, url_template, headers, query_params, body_template, content_type, description, active, cache_ttl, fallback_url_template, created_at, updated_at)
VALUES (
  'MessageBird SMS',
  'messagebird_sms',
  'sms_send',
  'hasSMS',
  'POST',
  'https://rest.messagebird.com/messages',
  '[{"name":"Authorization","value":"AccessKey {{messagebird_access_key}}"}]'::json,
  NULL,
  '{"originator":"{{sender_id}}","recipients":["{{to}}"],"body":"{{body}}"}',
  'application/json',
  'Envoi SMS via MessageBird/Bird (capability sms_send). Provider alternatif moins cher pour les DOM.',
  TRUE,
  NULL,
  NULL,
  NOW(),
  NOW()
);

INSERT INTO integration_variable (pattern_id, variable_name, source, source_field, default_value, required) VALUES
  ((SELECT id FROM integration_pattern WHERE code = 'messagebird_sms'), 'messagebird_access_key', 'site_settings', 'messageBirdAccessKey', NULL, TRUE),
  ((SELECT id FROM integration_pattern WHERE code = 'messagebird_sms'), 'to',                     'context',       'to',                   NULL, TRUE),
  ((SELECT id FROM integration_pattern WHERE code = 'messagebird_sms'), 'body',                   'context',       'body',                 NULL, TRUE),
  ((SELECT id FROM integration_pattern WHERE code = 'messagebird_sms'), 'sender_id',              'context',       'sender_id',            NULL, TRUE);

-- ============================================================
-- Auto-association : tout client qui a hasSMS = true → pattern Twilio par défaut
-- (le super_admin pourra basculer vers MessageBird via l'UI Patterns API)
-- ============================================================
INSERT INTO integration_pattern_client (integration_pattern_id, client_id)
SELECT
  (SELECT id FROM integration_pattern WHERE code = 'twilio_sms'),
  c.id
FROM client c
WHERE c.has_sms = TRUE
ON CONFLICT DO NOTHING;
