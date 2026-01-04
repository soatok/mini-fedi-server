<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Traits;

use FediE2EE\PKD\Crypto\HttpSignature;
use JsonException;
use Laminas\Diactoros\{
    Response,
    Stream
};
use Psr\Http\Message\ResponseInterface;
use Soatok\MiniFedi\Exceptions\TableException;
use Soatok\MiniFedi\FediServerConfig;
use Soatok\MiniFedi\Table;
use Soatok\MiniFedi\Tables\Actors;
use TypeError;

trait ReqTrait
{
    /**
     * @throws TableException
     */
    public function table(string $tableName): Table
    {
        return match ($tableName) {
            'Actors' => new Actors(),
            default => throw new TableException('Unknown table: ' . $tableName),
        };
    }

    public function error(string $message, int $code = 400): ResponseInterface
    {
        return $this->json(['error' => $message], $code);
    }

    public function signResponse(ResponseInterface $response): ResponseInterface
    {
        $config = FediServerConfig::instance();
        $signer = new HttpSignature();
        $response = $signer->sign(
            $config->serverSecretKey(),
            $response
        );
        if (!($response instanceof ResponseInterface)) {
            throw new TypeError('PKD Crypto did not return a response');
        }
        return $response;
    }

    public function json(
        array|object $data,
        int $status = 200,
        array $headers = []
    ): ResponseInterface {
        if (!array_key_exists('Content-Type', $headers)) {
            $headers['Content-Type'] = 'application/json';
        }
        $json = json_encode(
            $data,
            JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        if (!is_string($json)) {
            throw new JsonException(json_last_error_msg(), json_last_error());
        }
        $stream = new Stream('php://temp', 'wb');
        $stream->write($json);
        $stream->rewind();
        return $this->signResponse(
            new Response(
                $stream,
                $status,
                $headers
            )
        );
    }

    public function twig(string $template, array $vars = [], int $status = 200, array $headers = []): ResponseInterface
    {
        $html = FediServerConfig::instance()->twig()->render($template, $vars);
        $stream = new Stream('php://temp', 'wb');
        $stream->write($html);
        $stream->rewind();
        $headers['Content-Type'] = 'text/html; charset=utf-8';
        return new Response(
            $stream,
            $status,
            $headers
        );
    }
}
