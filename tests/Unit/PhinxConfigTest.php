<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxphinxconfig.class.php';

class PhinxConfigTest extends TestCase
{
    public function testNormalizeUtf8WebCharsetToUtf8mb4WhenPreferred(): void
    {
        $this->assertSame('utf8mb4', sxPhinxConfig::normalizeMysqlCharset('UTF-8', true));
        $this->assertSame('utf8', sxPhinxConfig::normalizeMysqlCharset('UTF-8', false));
    }

    public function testExtractDsnCharset(): void
    {
        $this->assertSame(
            'utf8',
            sxPhinxConfig::extractDsnCharset('mysql:host=localhost;dbname=x;charset=utf8')
        );
        $this->assertNull(sxPhinxConfig::extractDsnCharset('mysql:host=localhost;dbname=x'));
    }

    public function testDefaultCollation(): void
    {
        $this->assertSame('utf8_general_ci', sxPhinxConfig::defaultMysqlCollation('utf8'));
        $this->assertSame('utf8mb4_unicode_ci', sxPhinxConfig::defaultMysqlCollation('utf8mb4'));
    }

    public function testMigrationTableUsesPrefix(): void
    {
        $this->assertSame('modx_sendex_migrations', sxPhinxConfig::migrationTableName('modx_'));
        $this->assertSame('sendex_migrations', sxPhinxConfig::migrationTableName(''));
    }

    public function testBuildDbConfigPrefersDsnCharset(): void
    {
        $modx = new class {
            public $opts = array(
                'database_dsn' => 'mysql:host=db;dbname=sendex;charset=utf8',
                'charset' => 'UTF-8',
                'dbname' => 'sendex',
                'username' => 'u',
                'password' => 'p',
                'host' => 'db',
                'table_prefix' => 'modx_',
            );

            public function getOption($key, $options = null, $default = null)
            {
                return array_key_exists($key, $this->opts) ? $this->opts[$key] : $default;
            }
        };

        $db = sxPhinxConfig::buildDbConfig($modx);
        $this->assertSame('utf8', $db['charset']);
        $this->assertSame('utf8_general_ci', $db['collation']);
        $this->assertSame('modx_', $db['table_prefix']);
        $this->assertSame('sendex', $db['name']);
    }
}
