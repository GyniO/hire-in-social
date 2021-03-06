<?php

declare(strict_types=1);

/*
 * This file is part of the Hire in Social project.
 *
 * (c) Norbert Orzechowicz <norbert@orzechowicz.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HireInSocial\Application;

use function \Safe\json_decode;

final class Config
{
    public const ROOT_PATH = 'root_path';
    public const ENV = 'env';
    public const LOCALE = 'locale';
    public const TIMEZONE = 'timezone';

    public const SYMFONY_SECRET = 'symfony_secret';

    public const REDIS_DSN = 'redis_dsn';
    public const APPLY_EMAIL_TEMPLATE = 'apply_email_template';
    public const APPLY_EMAIL_CONFIG = 'apply_email_config';
    public const MAILER_CONFIG = 'mailer_config';

    public const FILESYSTEM_CONFIG = 'filesystem_config';

    public const RECAPTCHA_KEY = 'recaptcha_key';
    public const RECAPTCHA_SECRET = 'recaptcha_secret';

    public const GOOGLE_MAPS_KEY = 'google_maps_key';

    public const DB_HOST = 'db_host';
    public const DB_PORT = 'db_port';
    public const DB_USER = 'db_user';
    public const DB_USER_PASS = 'db_user_pass';
    public const DB_NAME = 'db_name';

    public const FB_APP_ID = 'facebook_app_id';
    public const FB_APP_SECRET = 'facebook_app_secret';

    public const THROTTLE_DURATION = 'throttle_duration';

    public const REDIS_DB_SYSTEM = 1;
    public const REDIS_DB_DOCTRINE_CACHE = 2;

    private $config;

    private function __construct(array $config)
    {
        $this->config = $config;
    }

    public static function fromEnv(string $projectRootPath) : self
    {
        return new self([
            self::ROOT_PATH => $projectRootPath,
            self::ENV => getenv('HIS_ENV'),
            self::LOCALE => getenv('HIS_LOCALE'),
            self::TIMEZONE => getenv('HIS_TIMEZONE'),
            self::SYMFONY_SECRET => getenv('HIS_SYMFONY_SECRET'),
            self::REDIS_DSN => getenv('HIS_REDIS_DSN'),
            self::APPLY_EMAIL_TEMPLATE => getenv('HIS_APPLY_EMAIL_TEMPLATE'),
            self::APPLY_EMAIL_CONFIG => getenv('HIS_APPLY_EMAIL_CONFIG'),
            self::RECAPTCHA_KEY => getenv('HIS_RECAPTCHA_KEY'),
            self::RECAPTCHA_SECRET => getenv('HIS_RECAPTCHA_SECRET'),
            self::GOOGLE_MAPS_KEY => getenv('HIS_GOOGLE_MAPS_API_KEY'),
            self::MAILER_CONFIG => getenv('HIS_MAILER_CONFIG'),
            self::FB_APP_ID => getenv('HIS_FB_APP_ID'),
            self::FB_APP_SECRET => getenv('HIS_FB_APP_SECRET'),
            self::THROTTLE_DURATION => getenv('HIS_THROTTLE_DURATION'),
            self::FILESYSTEM_CONFIG => getenv('HIS_FILESYSTEM_CONFIG'),
            self::DB_HOST => getenv('HIS_DB_HOST'),
            self::DB_PORT => getenv('HIS_DB_PORT'),
            self::DB_USER => getenv('HIS_DB_USER'),
            self::DB_USER_PASS => getenv('HIS_DB_USER_PASS'),
            self::DB_NAME => getenv('HIS_DB_NAME'),
        ]);
    }

    public function getString(string $key): string
    {
        if (!$this->has($key)) {
            throw new \RuntimeException(sprintf('Missing config key: %s', $key));
        }

        return (string) $this->config[$key];
    }

    /**
     * @throws \Safe\Exceptions\JsonException
     */
    public function getJson(string $key): array
    {
        return json_decode($this->getString($key), true);
    }

    public function getInt(string $key): int
    {
        if (!$this->has($key)) {
            throw new \RuntimeException(sprintf('Missing config key: %s', $key));
        }

        return (int) $this->config[$key];
    }

    public function override(string $key, string $value) : void
    {
        if (!$this->has($key)) {
            throw new \RuntimeException(sprintf('Missing config key: %s', $key));
        }

        $this->config[$key] = $value;
    }

    private function has(string $key) : bool
    {
        return \array_key_exists($key, $this->config);
    }
}
