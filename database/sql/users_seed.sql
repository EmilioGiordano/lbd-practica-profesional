INSERT INTO users (
    name,
    email,
    email_verified_at,
    password,
    remember_token,
    created_at,
    updated_at
) VALUES
(
    'Ada Lovelace',
    'ada@example.com',
    NOW(),
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9LLl2jM4A6h2YF5xY5uG8e',
    NULL,
    NOW(),
    NOW()
),
(
    'Grace Hopper',
    'grace@example.com',
    NOW(),
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9LLl2jM4A6h2YF5xY5uG8e',
    NULL,
    NOW(),
    NOW()
),
(
    'Alan Turing',
    'alan@example.com',
    NOW(),
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9LLl2jM4A6h2YF5xY5uG8e',
    NULL,
    NOW(),
    NOW()
),
(
    'Katherine Johnson',
    'katherine@example.com',
    NOW(),
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9LLl2jM4A6h2YF5xY5uG8e',
    NULL,
    NOW(),
    NOW()
),
(
    'Donald Knuth',
    'donald@example.com',
    NULL,
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9LLl2jM4A6h2YF5xY5uG8e',
    NULL,
    NOW(),
    NOW()
);
