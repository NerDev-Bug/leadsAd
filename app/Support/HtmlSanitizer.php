<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlSanitizer
{
    /**
     * Tags allowed for rich-text news content (Quill-compatible).
     *
     * @var list<string>
     */
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
        'ol', 'ul', 'li', 'h1', 'h2', 'h3', 'h4', 'blockquote',
        'span', 'div', 'a',
    ];

    /**
     * @var list<string>
     */
    private const ALLOWED_ATTRIBUTES = ['href', 'target', 'rel', 'class'];

    public static function clean(?string $html): string
    {
        if ($html === null) {
            return '';
        }

        $html = trim($html);
        if ($html === '') {
            return '';
        }

        // Plain text (no HTML tags): store as-is; views escape on render.
        if (! preg_match('/<[a-z][\s\S]*>/i', $html)) {
            return $html;
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $wrapped = '<?xml encoding="UTF-8"><div id="sanitize-root">'.$html.'</div>';
        $document->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $root = $document->getElementById('sanitize-root');
        if (! $root) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            return '';
        }

        self::sanitizeNode($root);

        $clean = '';
        foreach ($root->childNodes as $child) {
            $clean .= $document->saveHTML($child);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return trim($clean);
    }

    private static function sanitizeNode(DOMNode $node): void
    {
        if (! $node->hasChildNodes()) {
            return;
        }

        /** @var list<DOMNode> $children */
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child->nodeType === XML_TEXT_NODE || $child->nodeType === XML_CDATA_SECTION_NODE) {
                continue;
            }

            if ($child->nodeType !== XML_ELEMENT_NODE || ! $child instanceof DOMElement) {
                $child->parentNode?->removeChild($child);
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'link', 'meta', 'form', 'input', 'button'], true)) {
                $child->parentNode?->removeChild($child);
                continue;
            }

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                // Unwrap disallowed tags but keep children text/structure.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $child->parentNode?->removeChild($child);
                continue;
            }

            self::sanitizeAttributes($child);
            self::sanitizeNode($child);
        }
    }

    private static function sanitizeAttributes(DOMElement $element): void
    {
        /** @var list<string> $names */
        $names = [];
        foreach ($element->attributes ?? [] as $attribute) {
            $names[] = $attribute->name;
        }

        foreach ($names as $name) {
            $lower = strtolower($name);

            if (str_starts_with($lower, 'on') || ! in_array($lower, self::ALLOWED_ATTRIBUTES, true)) {
                $element->removeAttribute($name);
                continue;
            }

            if ($lower === 'href') {
                $href = trim($element->getAttribute('href'));
                if ($href === '' || preg_match('/^\s*javascript:/i', $href) || preg_match('/^\s*data:/i', $href)) {
                    $element->removeAttribute('href');
                    continue;
                }

                if (! preg_match('#^(https?:)?//#i', $href)
                    && ! str_starts_with($href, '/')
                    && ! str_starts_with($href, '#')
                    && ! preg_match('/^mailto:/i', $href)
                    && ! preg_match('/^tel:/i', $href)) {
                    $element->removeAttribute('href');
                }
            }

            if ($lower === 'target') {
                $element->setAttribute('rel', 'noopener noreferrer');
            }
        }
    }
}
