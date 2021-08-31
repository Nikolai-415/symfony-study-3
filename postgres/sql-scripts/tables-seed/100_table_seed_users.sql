-- Добавление записей в таблицу пользователей
INSERT INTO users(username, password, token, roles)
VALUES
    (
        'admin',                                                                -- Логин    : admin
        '$2y$13$Bd4lYnshGMcjlbdCOqoiiOyCwre1pp9SmWw2KsgQNDPOh7GdrxRyy',         -- Пароль   : password
        'admin:$2y$13$Bd4lYnshGMcjlbdCOqoiiOyCwre1pp9SmWw2KsgQNDPOh7GdrxRyy',   -- Токен для API
        '["ROLE_LIST_VIEW", "ROLE_ADD", "ROLE_EDIT", "ROLE_DELETE"]'            -- Роли пользователя
    ),
    (
        'user',                                                                 -- Логин    : user
        '$2y$13$S/d.vUaY4PtHfR113dUBy.nhPp1XRZO6ougzeq6mQWdMieGRgeUrm',         -- Пароль   : password
        'user:$2y$13$S/d.vUaY4PtHfR113dUBy.nhPp1XRZO6ougzeq6mQWdMieGRgeUrm',    -- Токен для API
        '[]'                                                                    -- Роли пользователя
    )
;