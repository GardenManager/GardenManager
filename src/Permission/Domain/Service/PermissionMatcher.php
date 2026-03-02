<?php

declare(strict_types=1);

namespace GardenManager\Permission\Domain\Service;

final class PermissionMatcher
{
    /**
     * @param array<string, bool> $resolved
     */
    public function evaluate(array $resolved, string $permission): bool
    {
        $bestMatch = null;
        $bestSpecificity = -1;

        foreach ($resolved as $pattern => $granted) {
            if ($this->matches($pattern, $permission)) {
                $specificity = $this->specificity($pattern);
                if ($specificity > $bestSpecificity) {
                    $bestSpecificity = $specificity;
                    $bestMatch = $granted;
                }
            }
        }

        return $bestMatch ?? false;
    }

    public function matches(string $pattern, string $requested): bool
    {
        if ($pattern === $requested) {
            return true;
        }

        if ($pattern === '**') {
            return true;
        }

        $patternSegments = explode('.', $pattern);
        $requestedSegments = explode('.', $requested);

        // Trailing ** matches any remaining segments
        if (end($patternSegments) === '**') {
            $concreteSegments = \array_slice($patternSegments, 0, -1);

            if (\count($requestedSegments) <= \count($concreteSegments)) {
                return false;
            }

            foreach ($concreteSegments as $i => $patternSegment) {
                if ($patternSegment === '*') {
                    continue;
                }

                if ($patternSegment !== $requestedSegments[$i]) {
                    return false;
                }
            }

            return true;
        }

        if (\count($patternSegments) !== \count($requestedSegments)) {
            return false;
        }

        foreach ($patternSegments as $i => $patternSegment) {
            if ($patternSegment === '*') {
                continue;
            }

            if ($patternSegment !== $requestedSegments[$i]) {
                return false;
            }
        }

        return true;
    }

    public function specificity(string $pattern): int
    {
        if ($pattern === '**') {
            return 0;
        }

        $segments = explode('.', $pattern);
        $score = count($segments) * 10;

        foreach ($segments as $segment) {
            if ($segment !== '*' && $segment !== '**') {
                $score += 10;
            }
        }

        return $score;
    }
}
