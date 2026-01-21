<?php

// Minimal stubs for gettext/gettext classes to support bundled WP-CLI i18n commands
// when the full composer dependency is unavailable.

namespace Gettext;

class Translation {
    private $context;
    private $original;
    private $plural;
    private $translation = '';
    private $pluralTranslations = array();
    private $comments = array();
    private $extractedComments = array();
    private $references = array();
    private $flags = array();
    private $disabled = false;

    public function __construct($context = null, $original = '', $plural = null) {
        $this->context = $context;
        $this->original = $original;
        $this->plural = $plural;
    }

    public function addReference($file, $line = null) {
        $this->references[] = array($file, $line);
        return $this;
    }

    public function addExtractedComment($comment) {
        $this->extractedComments[] = $comment;
        return $this;
    }

    public function addFlag($flag) {
        $this->flags[] = $flag;
        return $this;
    }

    public function hasPluralTranslations($strict = false) {
        return !empty($this->pluralTranslations);
    }

    public function getPluralTranslations($count = null) {
        return $this->pluralTranslations;
    }

    public function setPluralTranslations(array $translations) {
        $this->pluralTranslations = $translations;
        return $this;
    }

    public function setTranslation($value) {
        $this->translation = $value;
        return $this;
    }

    public function getTranslation() {
        return $this->translation;
    }

    public function hasPlural() {
        return null !== $this->plural;
    }

    public function getPlural() {
        return $this->plural;
    }

    public function getOriginal() {
        return $this->original;
    }

    public function getContext() {
        return $this->context;
    }

    public function hasContext() {
        return null !== $this->context && '' !== $this->context;
    }

    public function hasComments() {
        return !empty($this->comments);
    }

    public function getComments() {
        return $this->comments;
    }

    public function addComment($comment) {
        $this->comments[] = $comment;
        return $this;
    }

    public function hasExtractedComments() {
        return !empty($this->extractedComments);
    }

    public function getExtractedComments() {
        return $this->extractedComments;
    }

    public function getReferences() {
        return $this->references;
    }

    public function hasFlags() {
        return !empty($this->flags);
    }

    public function getFlags() {
        return $this->flags;
    }

    public function isDisabled() {
        return (bool) $this->disabled;
    }

    public function setDisabled($disabled) {
        $this->disabled = (bool) $disabled;
        return $this;
    }

    public function getId() {
        return ($this->context ?: '') . "\004" . $this->original;
    }
}

class Translations implements \IteratorAggregate {
    const HEADER_LANGUAGE = 'Language';

    private $entries = array();
    private $headers = array();
    private $domain;
    private $language;

    public static function fromPoFile($file) {
        // Minimal parser: not implemented, return empty collection
        return new self();
    }

    public function insert($context, $original, $plural = null) {
        $id = ($context ?: '') . "\004" . $original;
        if (!isset($this->entries[$id])) {
            $this->entries[$id] = new Translation($context, $original, $plural);
        }
        return $this->entries[$id];
    }

    public function find(Translation $translation) {
        $id = $translation->getId();
        return isset($this->entries[$id]) ? $this->entries[$id] : null;
    }

    public function mergeWith(Translations $other, $flags = 0) {
        foreach ($other as $translation) {
            $existing = $this->find($translation);
            if (!$existing) {
                $this->entries[$translation->getId()] = $translation;
            }
        }
        return $this;
    }

    public function setHeader($name, $value) {
        $this->headers[$name] = $value;
        if (self::HEADER_LANGUAGE === $name) {
            $this->language = $value;
        }
    }

    public function getHeader($name) {
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }

    public function deleteHeader($name) {
        unset($this->headers[$name]);
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function setDomain($domain) {
        $this->domain = $domain;
    }

    public function getDomain() {
        return $this->domain;
    }

    public function setLanguage($language) {
        $this->language = $language;
    }

    public function getLanguage() {
        return $this->language ?: $this->getHeader(self::HEADER_LANGUAGE);
    }

    public function getPluralForms() {
        $header = $this->getHeader('Plural-Forms');
        if ($header && preg_match('/nplurals\s*=\s*(\d+)/i', $header, $m)) {
            return array((int) $m[1]);
        }
        return array(2); // sensible default
    }

    public function toPoFile($file) {
        return (bool) file_put_contents($file, '');
    }

    public function toMoFile($file) {
        return (bool) file_put_contents($file, '');
    }

    #[\ReturnTypeWillChange]
    public function getIterator(): \Traversable {
        return new \ArrayIterator(array_values($this->entries));
    }
}

class Merge {
    const ADD = 1;
    const REMOVE = 2;
    const COMMENTS_THEIRS = 4;
    const EXTRACTED_COMMENTS_THEIRS = 8;
    const REFERENCES_THEIRS = 16;
    const DOMAIN_OVERRIDE = 32;
}

namespace Gettext\Utils;

class ParsedComment {
    private $comment;
    public function __construct($comment) { $this->comment = $comment; }
    public function getComment() { return $this->comment; }
}

namespace Gettext\Generators;

class Po {
    protected static function escape($string) {
        return addcslashes($string, "\0\n\r\t\\\"");
    }

    public static function convertString($string) {
        return '"' . self::escape($string) . '"';
    }

    public static function toFile(\Gettext\Translations $translations, $file, array $options = array()) {
        if (!method_exists($translations, 'getHeaders')) {
            return false;
        }
        $content = static::toString($translations, $options);
        return false !== file_put_contents($file, $content);
    }

    public static function toString(\Gettext\Translations $translations, array $options = array()) {
        // Minimal placeholder; PotGenerator overrides behavior.
        return '';
    }
}
