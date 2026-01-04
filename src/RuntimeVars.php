<?php
declare(strict_types=1);
namespace Soatok\MiniFedi;

class RuntimeVars
{
    public bool $debug = false;
    public string $hostname = 'localhost:80';

    public static function fromJson(string $json): RuntimeVars
    {
        $self = new RuntimeVars();
        $properties = json_decode($json, true);
        foreach ($properties as $property => $value) {
            if (property_exists($self, $property)) {
                $self->$property = $value;
            }
        }
        return $self;
    }

    public function toJson(): string
    {
        return json_encode([
            'debug' => $this->debug,
        ]);
    }
}
