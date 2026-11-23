<?php

class Database
{
    public function connect(): PDO
    {
        $databaseUrl = getenv('DATABASE_URL');

        // RENDER: Use the hosted PostgreSQL database
        if (!empty($databaseUrl)) {
            $parts = parse_url(trim($databaseUrl));
            if (
                $parts === false ||
                empty($parts['host']) ||
                empty($parts['user']) ||
                empty($parts['path'])
            ) {
                throw new RuntimeException('Invalid DATABASE_URL configuration.');
            }

            $host = $parts['host'];
            $port = $parts['port'] ?? 5432;
            $dbname = ltrim($parts['path'], '/');
            $username = rawurldecode($parts['user']);
            $password = isset($parts['pass'])
                ? rawurldecode($parts['pass'])
                : '';

            // Use the SSL mode from the URL, or require SSL by default.
            parse_str($parts['query'] ?? '', $query);
            $sslmode = $query['sslmode'] ?? 'require';

        } else {
            // LOCAL: Use environment settings, or the standard XAMPP/PostgreSQL defaults.
            $host = $this->envString('DB_HOST', 'localhost');
            $port = $this->envString('DB_PORT', '5432');
            $dbname = $this->envString('DB_NAME', 'online_store');
            $username = $this->envString('DB_USER', 'postgres');
            $password = $this->envString('DB_PASSWORD', '');
            $sslmode = $this->envOptional('DB_SSLMODE');
        }

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

        if ($sslmode !== null) {
            $dsn .= ";sslmode={$sslmode}";
        }

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    private function envString(string $name, string $default): string
    {
        $value = getenv($name);
        return is_string($value) && $value !== ''
            ? $value
            : $default;
    }

    private function envOptional(string $name): ?string
    {
        $value = getenv($name);
        return is_string($value) && $value !== ''
            ? $value
            : null;
    }
}
