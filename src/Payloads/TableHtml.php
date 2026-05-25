<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\xrDebug\Laravel\Payloads;

final class TableHtml
{
    /**
     * @param array<string, mixed> $pairs
     */
    public function __construct(
        public readonly array $pairs
    ) {
    }

    public function __toString(): string
    {
        $html = '<table>';
        foreach ($this->pairs as $key => $value) {
            if ($value === null) {
                continue;
            }
            $html .= '<tr><th align="left">'
                . htmlspecialchars($key)
                . '</th><td>'
                . htmlspecialchars($this->normalizeValue($value))
                . '</td></tr>';
        }
        $html .= '</table>';

        return $html;
    }

    public function normalizeValue(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_array($value) || is_object($value)) {
            return (string) json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return '';
    }
}
