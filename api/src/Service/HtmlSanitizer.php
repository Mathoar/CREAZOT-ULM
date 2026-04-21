<?php

declare(strict_types=1);

namespace App\Service;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Nettoie les contenus HTML provenant du WYSIWYG (briefing client / circuit) avant persistence.
 * Bloque scripts, iframes et attributs dangereux. Autorise uniquement un subset éditorial.
 */
final class HtmlSanitizer
{
    private ?HTMLPurifier $purifier = null;

    public function sanitize(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        return $this->getPurifier()->purify($html);
    }

    private function getPurifier(): HTMLPurifier
    {
        if ($this->purifier !== null) {
            return $this->purifier;
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null);
        $config->set('HTML.Allowed', 'p,br,strong,em,u,ul,ol,li,h2,h3,h4,a[href|title|target|rel],blockquote,hr,span[style]');
        $config->set('CSS.AllowedProperties', 'color,background-color,text-align,font-weight,font-style,text-decoration');
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.TargetBlank', true);
        $config->set('HTML.Nofollow', true);
        $config->set('AutoFormat.AutoParagraph', true);
        $config->set('AutoFormat.RemoveEmpty', true);

        $this->purifier = new HTMLPurifier($config);

        return $this->purifier;
    }
}
