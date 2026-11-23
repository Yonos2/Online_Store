
<?php

class Database
{
    public function connect(): PDO
    {
        $databaseUrl = getenv('DATABASE_URL');

        // RENDER: Use the hosted PostgreSQL database
        if (!empty($databaseUrl)) {

            $parts = parse_url($databaseUrl);

            if (
                $parts === false ||
                empty($parts['host']) ||
                empty($parts['user']) ||
                !isset($parts['pass']) ||
                empty($parts['path'])
            ) {
                throw new RuntimeException('Invalid DATABASE_URL configuration.');
            }

            $host = $parts['host'];
            $port = $parts['port'] ?? 5432;
            $dbname = ltrim($parts['path'], '/');
            $username = rawurldecode($parts['user']);
            $password = rawurldecode($parts['pass']);

            // Use the SSL mode from the URL, or require SSL by default.
            parse_str($parts['query'] ?? '', $query);
            $sslmode = $query['sslmode'] ?? 'require';

        } else {

            // LOCAL: Use your local PostgreSQL database
            $host = 'localhost';
            $port = 5432;
            $dbname = 'online_store';
            $username = 'postgres';
            $password = 'PUT_YOUR_NEW_LOCAL_POSTGRES_PASSWORD_HERE';
            $sslmode = null;
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
}
