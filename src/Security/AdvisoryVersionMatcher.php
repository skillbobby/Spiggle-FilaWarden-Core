<?php

namespace Spiggle\FilaWarden\Security;

/**
 * Compares an installed version with a published affected-version constraint.
 *
 * Supports the comparator forms used by GitHub Security Advisories and the
 * Packagist advisory feed, including comma-separated AND groups and
 * pipe-separated OR groups.
 */
class AdvisoryVersionMatcher
{
    public function affected(string $version, string $constraint): bool
    {
        $version = $this->normalize($version);
        $constraint = trim($constraint);

        if ($version === null || $constraint === '') {
            return false;
        }

        foreach (explode('|', $constraint) as $group) {
            $parts = array_values(array_filter(array_map('trim', explode(',', $group)), fn (string $part): bool => $part !== ''));

            if ($parts === []) {
                continue;
            }

            $matchesGroup = true;

            foreach ($parts as $part) {
                if (! $this->matchesAtomic($version, $part)) {
                    $matchesGroup = false;
                    break;
                }
            }

            if ($matchesGroup) {
                return true;
            }
        }

        return false;
    }

    public function sameBranch(string $installed, string $patched): bool
    {
        $installed = $this->normalize($installed);
        $patched = $this->normalize($patched);

        if ($installed === null || $patched === null) {
            return false;
        }

        $installedParts = explode('.', $installed);
        $patchedParts = explode('.', $patched);

        return ($installedParts[0] ?? null) === ($patchedParts[0] ?? null)
            && ($installedParts[1] ?? '0') === ($patchedParts[1] ?? '0');
    }

    private function matchesAtomic(string $version, string $constraint): bool
    {
        if (! preg_match('/^(<=|>=|<|>|!=|==|=)?\s*v?(\d+(?:\.\d+){1,3})$/i', $constraint, $matches)) {
            return false;
        }

        $operator = $matches[1] !== '' ? $matches[1] : '=';
        $bound = $this->normalize($matches[2]);

        if ($bound === null) {
            return false;
        }

        $operator = $operator === '==' ? '=' : $operator;

        return version_compare($version, $bound, $operator);
    }

    private function normalize(string $version): ?string
    {
        $version = ltrim(trim($version), 'vV');

        if (! preg_match('/^(\d+(?:\.\d+){1,3})/', $version, $matches)) {
            return null;
        }

        return $matches[1];
    }
}
