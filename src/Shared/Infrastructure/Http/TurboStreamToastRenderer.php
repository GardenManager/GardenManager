<?php

declare(strict_types=1);

namespace GardenManager\Shared\Infrastructure\Http;

use Symfony\UX\Turbo\TurboStreamResponse;
use Twig\Environment;

final readonly class TurboStreamToastRenderer
{
    public function __construct(private Environment $twig)
    {
    }

    public function createErrorResponse(string $message, int $statusCode = 422): TurboStreamResponse
    {
        $html = $this->renderToast('error', $message, autoDismiss: false);

        return new TurboStreamResponse(status: $statusCode)
            ->append('#toast-container', $html);
    }

    public function createSuccessResponse(string $message): TurboStreamResponse
    {
        $html = $this->renderToast('success', $message, autoDismiss: true);

        return new TurboStreamResponse()
            ->append('#toast-container', $html);
    }

    private function renderToast(string $type, string $message, bool $autoDismiss, int $timeout = 5000): string
    {
        return $this->twig->render('_toast_stream.html.twig', [
            'type' => $type,
            'message' => $message,
            'autoDismiss' => $autoDismiss,
            'timeout' => $timeout,
        ]);
    }
}
