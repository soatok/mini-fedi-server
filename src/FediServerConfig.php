<?php
declare(strict_types=1);
namespace Soatok\MiniFedi;

use FediE2EE\PKD\Crypto\SecretKey;
use ParagonIE\EasyDB\EasyDB;
use League\Route\Router;
use Soatok\MiniFedi\Exceptions\ConfigException;
use Twig\Environment;

class FediServerConfig
{
    public ?EasyDB $db = null;
    public ?Router $router = null;
    public ?SecretKey $serverSecretKey = null;
    public ?Environment $twig = null;
    public RuntimeVars $vars;

    public function __construct(?RuntimeVars $vars = null)
    {
        if (is_null($vars)) {
            $vars = new RuntimeVars();
        }
        $this->vars = $vars;
    }

    // Singleton instance
    private static ?FediServerConfig $serverConfig = null;
    public static function instance(): self
    {
        if (!self::$serverConfig) {
            self::$serverConfig = new FediServerConfig();
        }
        return self::$serverConfig;
    }

    public function database(): EasyDB
    {
        if (is_null($this->db)) {
            throw new ConfigException('Database connection not set');
        }
        return $this->db;
    }

    public function router(): Router
    {
        if (is_null($this->router)) {
            throw new ConfigException('Router not set');
        }
        return $this->router;
    }

    public function vars(): RuntimeVars
    {
        return $this->vars;
    }

    public function serverSecretKey(): SecretKey
    {
        if (is_null($this->serverSecretKey)) {
            throw new ConfigException('Server secret key not set');
        }
        return $this->serverSecretKey;
    }

    public function twig(): Environment
    {
        if (is_null($this->twig)) {
            throw new ConfigException('Twig environment not set');
        }
        return $this->twig;;
    }

    public function withDatabase(EasyDB $db): self
    {
        $this->db = $db;
        return $this;
    }

    public function withRouter(Router $router): self
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @api
     */
    public function withRuntimeVars(RuntimeVars $runtimeVars): self
    {
        $this->vars = $runtimeVars;
        return $this;
    }

    public function withServerSecretKey(SecretKey $sk): self
    {
        $this->serverSecretKey = $sk;
        return $this;
    }

    public function withTwig(Environment $twig): self
    {
        $this->twig = $twig;
        return $this;
    }
}
