-- Crea rol admin si no existe
INSERT IGNORE INTO roles (name, guard_name, created_at, updated_at)
VALUES ('admin', 'web', NOW(), NOW());

-- Asigna rol admin a Javier Jolon
INSERT IGNORE INTO model_has_roles (role_id, model_type, model_id)
VALUES (
    (SELECT id FROM roles WHERE name = 'admin' AND guard_name = 'web'),
    'App\\Models\\User',
    (SELECT id FROM users WHERE name = 'Javier Jolon')
);

-- Verifica resultado
SELECT u.id, u.name, u.telefono, r.name AS rol
FROM users u
INNER JOIN model_has_roles mhr ON mhr.model_id = u.id AND mhr.model_type = 'App\\Models\\User'
INNER JOIN roles r ON r.id = mhr.role_id
WHERE u.name = 'Javier Jolon';
