INSERT INTO users(
    username,
    password,
    token,
    roles
) VALUES (
    'admin',
    '$2y$13$Bd4lYnshGMcjlbdCOqoiiOyCwre1pp9SmWw2KsgQNDPOh7GdrxRyy',
    'admin:$2y$13$Bd4lYnshGMcjlbdCOqoiiOyCwre1pp9SmWw2KsgQNDPOh7GdrxRyy',
    '["ROLE_LIST_VIEW", "ROLE_ADD", "ROLE_EDIT", "ROLE_DELETE"]'
);

INSERT INTO users(
    username,
    password,
    token
) VALUES (
    'user',
    '$2y$13$S/d.vUaY4PtHfR113dUBy.nhPp1XRZO6ougzeq6mQWdMieGRgeUrm',
    'user:$2y$13$S/d.vUaY4PtHfR113dUBy.nhPp1XRZO6ougzeq6mQWdMieGRgeUrm'
);