<?php

declare(strict_types=1);

namespace S2J\WebinarService\Core;

final class PanelistDiff
{
    /**
     * @param list<array{name?: string, email?: string}> $previous
     * @param list<array{name?: string, email?: string}> $current
     * @return array{add: list<array{name: string, email: string}>, remove: list<array{email: string}>}
     */
    public static function diff(array $previous, array $current): array
    {
        $prevByEmail = [];
        foreach ($previous as $row) {
            $email = isset($row['email']) && is_string($row['email']) ? PanelistEmail::normalize($row['email']) : '';
            if ($email === '') {
                continue;
            }
            $name = isset($row['name']) && is_string($row['name']) ? trim($row['name']) : '';
            $prevByEmail[$email] = $name;
        }

        $currByEmail = [];
        $currOrder = [];
        foreach ($current as $row) {
            $email = isset($row['email']) && is_string($row['email']) ? PanelistEmail::normalize($row['email']) : '';
            if ($email === '') {
                continue;
            }
            $name = isset($row['name']) && is_string($row['name']) ? trim($row['name']) : '';
            $currByEmail[$email] = $name;
            $currOrder[] = $email;
        }

        $remove = [];
        foreach ($prevByEmail as $email => $prevName) {
            if (! array_key_exists($email, $currByEmail)) {
                $remove[] = ['email' => $email];
                continue;
            }
            if ($currByEmail[$email] !== $prevName) {
                $remove[] = ['email' => $email];
            }
        }

        $add = [];
        foreach ($currOrder as $email) {
            if (! array_key_exists($email, $prevByEmail)) {
                $add[] = ['name' => $currByEmail[$email], 'email' => $email];
                continue;
            }
            if ($prevByEmail[$email] !== $currByEmail[$email]) {
                $add[] = ['name' => $currByEmail[$email], 'email' => $email];
            }
        }

        return ['add' => $add, 'remove' => $remove];
    }
}
