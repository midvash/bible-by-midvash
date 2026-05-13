<?php
/**
 * Bible Books Data
 * 
 * Centralized book definitions with multilingual support.
 * Based on api-publica/src/books.ts
 * 
 * @package Bible_by_Midvash
 */

if (!defined('ABSPATH')) {
    exit;
}

class BBM_Books
{
    /**
     * Supported locales
     */
    const LOCALES = array('en', 'pt-br', 'es');

    /**
     * Default versions per locale
     */
    const DEFAULT_VERSIONS = array(
        'en' => 'nlt',
        'pt-br' => 'nvt',
        'es' => 'ntv',
    );

    /**
     * Complete book definitions with multilingual support
     * Each book has: id, chapters, testament, slugs (by locale), names (by locale), abbrev (by locale)
     */
    private static $books = null;

    /**
     * Get all books
     */
    public static function get_books()
    {
        if (self::$books === null) {
            self::$books = self::init_books();
        }
        return self::$books;
    }

    /**
     * Get book by ID
     */
    public static function get_book_by_id($id)
    {
        $books = self::get_books();
        return isset($books[$id]) ? $books[$id] : null;
    }

    /**
     * Get book by slug (searches all locales)
     */
    public static function get_book_by_slug($slug)
    {
        $normalized = strtolower(trim($slug));
        $books = self::get_books();

        foreach ($books as $book) {
            foreach (self::LOCALES as $locale) {
                if (isset($book['slugs'][$locale]) && $book['slugs'][$locale] === $normalized) {
                    return $book;
                }
            }
        }

        return null;
    }

    /**
     * Get book by name or abbreviation (searches all locales)
     */
    public static function get_book_by_name($name)
    {
        $normalized = mb_strtolower(trim($name));
        $books = self::get_books();

        foreach ($books as $book) {
            // Check names
            foreach (self::LOCALES as $locale) {
                if (isset($book['names'][$locale]) && mb_strtolower($book['names'][$locale]) === $normalized) {
                    return $book;
                }
            }
            // Check abbreviations
            foreach (self::LOCALES as $locale) {
                if (isset($book['abbrev'][$locale]) && mb_strtolower($book['abbrev'][$locale]) === $normalized) {
                    return $book;
                }
            }
        }

        return null;
    }

    /**
     * Get slug for book in specific locale
     */
    public static function get_book_slug($book_id, $locale = 'en')
    {
        $book = self::get_book_by_id($book_id);
        if (!$book) {
            return null;
        }
        $locale = self::normalize_locale($locale);
        return isset($book['slugs'][$locale]) ? $book['slugs'][$locale] : $book['slugs']['en'];
    }

    /**
     * Get name for book in specific locale
     */
    public static function get_book_name($book_id, $locale = 'en')
    {
        $book = self::get_book_by_id($book_id);
        if (!$book) {
            return null;
        }
        $locale = self::normalize_locale($locale);
        return isset($book['names'][$locale]) ? $book['names'][$locale] : $book['names']['en'];
    }

    /**
     * Normalize locale string
     */
    public static function normalize_locale($locale)
    {
        $locale = strtolower(trim($locale));
        if ($locale === 'pt' || $locale === 'pt-br' || $locale === 'pt_br') {
            return 'pt-br';
        }
        if ($locale === 'es' || $locale === 'es-es' || $locale === 'es_es') {
            return 'es';
        }
        if (in_array($locale, self::LOCALES)) {
            return $locale;
        }
        return 'en';
    }

    /**
     * Get default version for locale
     */
    public static function get_default_version($locale)
    {
        $locale = self::normalize_locale($locale);
        return isset(self::DEFAULT_VERSIONS[$locale]) ? self::DEFAULT_VERSIONS[$locale] : 'nvt';
    }

    /**
     * Build pattern for regex matching (all names and abbreviations)
     */
    public static function get_matching_pattern($locale = null)
    {
        $books = self::get_books();
        $patterns = array();

        foreach ($books as $book) {
            $locales_to_check = $locale ? array(self::normalize_locale($locale)) : self::LOCALES;

            foreach ($locales_to_check as $loc) {
                if (isset($book['names'][$loc])) {
                    $patterns[] = preg_quote($book['names'][$loc], '/');
                }
                if (isset($book['abbrev'][$loc])) {
                    $patterns[] = preg_quote($book['abbrev'][$loc], '/');
                }
            }
        }

        // Sort by length descending to match longer names first
        usort($patterns, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        return implode('|', array_unique($patterns));
    }

    /**
     * Initialize book data
     * Based on api-publica/src/books.ts
     */
    private static function init_books()
    {
        return array(
            // Old Testament
            1 => array(
                'id' => 1,
                'chapters' => 50,
                'testament' => 'old',
                'slugs' => array('en' => 'genesis', 'pt-br' => 'genesis', 'es' => 'genesis'),
                'names' => array('en' => 'Genesis', 'pt-br' => 'Gênesis', 'es' => 'Génesis'),
                'abbrev' => array('en' => 'Gen', 'pt-br' => 'Gn', 'es' => 'Gén'),
            ),
            2 => array(
                'id' => 2,
                'chapters' => 40,
                'testament' => 'old',
                'slugs' => array('en' => 'exodus', 'pt-br' => 'exodo', 'es' => 'exodo'),
                'names' => array('en' => 'Exodus', 'pt-br' => 'Êxodo', 'es' => 'Éxodo'),
                'abbrev' => array('en' => 'Exo', 'pt-br' => 'Êx', 'es' => 'Éx'),
            ),
            3 => array(
                'id' => 3,
                'chapters' => 27,
                'testament' => 'old',
                'slugs' => array('en' => 'leviticus', 'pt-br' => 'levitico', 'es' => 'levitico'),
                'names' => array('en' => 'Leviticus', 'pt-br' => 'Levítico', 'es' => 'Levítico'),
                'abbrev' => array('en' => 'Lev', 'pt-br' => 'Lv', 'es' => 'Lv'),
            ),
            4 => array(
                'id' => 4,
                'chapters' => 36,
                'testament' => 'old',
                'slugs' => array('en' => 'numbers', 'pt-br' => 'numeros', 'es' => 'numeros'),
                'names' => array('en' => 'Numbers', 'pt-br' => 'Números', 'es' => 'Números'),
                'abbrev' => array('en' => 'Num', 'pt-br' => 'Nm', 'es' => 'Núm'),
            ),
            5 => array(
                'id' => 5,
                'chapters' => 34,
                'testament' => 'old',
                'slugs' => array('en' => 'deuteronomy', 'pt-br' => 'deuteronomio', 'es' => 'deuteronomio'),
                'names' => array('en' => 'Deuteronomy', 'pt-br' => 'Deuteronômio', 'es' => 'Deuteronomio'),
                'abbrev' => array('en' => 'Deu', 'pt-br' => 'Dt', 'es' => 'Dt'),
            ),
            6 => array(
                'id' => 6,
                'chapters' => 24,
                'testament' => 'old',
                'slugs' => array('en' => 'joshua', 'pt-br' => 'josue', 'es' => 'josue'),
                'names' => array('en' => 'Joshua', 'pt-br' => 'Josué', 'es' => 'Josué'),
                'abbrev' => array('en' => 'Jos', 'pt-br' => 'Js', 'es' => 'Jos'),
            ),
            7 => array(
                'id' => 7,
                'chapters' => 21,
                'testament' => 'old',
                'slugs' => array('en' => 'judges', 'pt-br' => 'juizes', 'es' => 'jueces'),
                'names' => array('en' => 'Judges', 'pt-br' => 'Juízes', 'es' => 'Jueces'),
                'abbrev' => array('en' => 'Jdg', 'pt-br' => 'Jz', 'es' => 'Jue'),
            ),
            8 => array(
                'id' => 8,
                'chapters' => 4,
                'testament' => 'old',
                'slugs' => array('en' => 'ruth', 'pt-br' => 'rute', 'es' => 'rut'),
                'names' => array('en' => 'Ruth', 'pt-br' => 'Rute', 'es' => 'Rut'),
                'abbrev' => array('en' => 'Rut', 'pt-br' => 'Rt', 'es' => 'Rut'),
            ),
            9 => array(
                'id' => 9,
                'chapters' => 31,
                'testament' => 'old',
                'slugs' => array('en' => '1-samuel', 'pt-br' => '1-samuel', 'es' => '1-samuel'),
                'names' => array('en' => '1 Samuel', 'pt-br' => '1 Samuel', 'es' => '1 Samuel'),
                'abbrev' => array('en' => '1Sa', 'pt-br' => '1Sm', 'es' => '1Sa'),
            ),
            10 => array(
                'id' => 10,
                'chapters' => 24,
                'testament' => 'old',
                'slugs' => array('en' => '2-samuel', 'pt-br' => '2-samuel', 'es' => '2-samuel'),
                'names' => array('en' => '2 Samuel', 'pt-br' => '2 Samuel', 'es' => '2 Samuel'),
                'abbrev' => array('en' => '2Sa', 'pt-br' => '2Sm', 'es' => '2Sa'),
            ),
            11 => array(
                'id' => 11,
                'chapters' => 22,
                'testament' => 'old',
                'slugs' => array('en' => '1-kings', 'pt-br' => '1-reis', 'es' => '1-reyes'),
                'names' => array('en' => '1 Kings', 'pt-br' => '1 Reis', 'es' => '1 Reyes'),
                'abbrev' => array('en' => '1Ki', 'pt-br' => '1Rs', 'es' => '1Re'),
            ),
            12 => array(
                'id' => 12,
                'chapters' => 25,
                'testament' => 'old',
                'slugs' => array('en' => '2-kings', 'pt-br' => '2-reis', 'es' => '2-reyes'),
                'names' => array('en' => '2 Kings', 'pt-br' => '2 Reis', 'es' => '2 Reyes'),
                'abbrev' => array('en' => '2Ki', 'pt-br' => '2Rs', 'es' => '2Re'),
            ),
            13 => array(
                'id' => 13,
                'chapters' => 29,
                'testament' => 'old',
                'slugs' => array('en' => '1-chronicles', 'pt-br' => '1-cronicas', 'es' => '1-cronicas'),
                'names' => array('en' => '1 Chronicles', 'pt-br' => '1 Crônicas', 'es' => '1 Crónicas'),
                'abbrev' => array('en' => '1Ch', 'pt-br' => '1Cr', 'es' => '1Cr'),
            ),
            14 => array(
                'id' => 14,
                'chapters' => 36,
                'testament' => 'old',
                'slugs' => array('en' => '2-chronicles', 'pt-br' => '2-cronicas', 'es' => '2-cronicas'),
                'names' => array('en' => '2 Chronicles', 'pt-br' => '2 Crônicas', 'es' => '2 Crónicas'),
                'abbrev' => array('en' => '2Ch', 'pt-br' => '2Cr', 'es' => '2Cr'),
            ),
            15 => array(
                'id' => 15,
                'chapters' => 10,
                'testament' => 'old',
                'slugs' => array('en' => 'ezra', 'pt-br' => 'esdras', 'es' => 'esdras'),
                'names' => array('en' => 'Ezra', 'pt-br' => 'Esdras', 'es' => 'Esdras'),
                'abbrev' => array('en' => 'Ezr', 'pt-br' => 'Ed', 'es' => 'Esd'),
            ),
            16 => array(
                'id' => 16,
                'chapters' => 13,
                'testament' => 'old',
                'slugs' => array('en' => 'nehemiah', 'pt-br' => 'neemias', 'es' => 'nehemias'),
                'names' => array('en' => 'Nehemiah', 'pt-br' => 'Neemias', 'es' => 'Nehemías'),
                'abbrev' => array('en' => 'Neh', 'pt-br' => 'Ne', 'es' => 'Neh'),
            ),
            17 => array(
                'id' => 17,
                'chapters' => 10,
                'testament' => 'old',
                'slugs' => array('en' => 'esther', 'pt-br' => 'ester', 'es' => 'ester'),
                'names' => array('en' => 'Esther', 'pt-br' => 'Ester', 'es' => 'Ester'),
                'abbrev' => array('en' => 'Est', 'pt-br' => 'Et', 'es' => 'Est'),
            ),
            18 => array(
                'id' => 18,
                'chapters' => 42,
                'testament' => 'old',
                'slugs' => array('en' => 'job', 'pt-br' => 'jo', 'es' => 'job'),
                'names' => array('en' => 'Job', 'pt-br' => 'Jó', 'es' => 'Job'),
                'abbrev' => array('en' => 'Job', 'pt-br' => 'Jó', 'es' => 'Job'),
            ),
            19 => array(
                'id' => 19,
                'chapters' => 150,
                'testament' => 'old',
                'slugs' => array('en' => 'psalms', 'pt-br' => 'salmos', 'es' => 'salmos'),
                'names' => array('en' => 'Psalms', 'pt-br' => 'Salmos', 'es' => 'Salmos'),
                'abbrev' => array('en' => 'Psa', 'pt-br' => 'Sl', 'es' => 'Sal'),
            ),
            20 => array(
                'id' => 20,
                'chapters' => 31,
                'testament' => 'old',
                'slugs' => array('en' => 'proverbs', 'pt-br' => 'proverbios', 'es' => 'proverbios'),
                'names' => array('en' => 'Proverbs', 'pt-br' => 'Provérbios', 'es' => 'Proverbios'),
                'abbrev' => array('en' => 'Pro', 'pt-br' => 'Pv', 'es' => 'Pr'),
            ),
            21 => array(
                'id' => 21,
                'chapters' => 12,
                'testament' => 'old',
                'slugs' => array('en' => 'ecclesiastes', 'pt-br' => 'eclesiastes', 'es' => 'eclesiastes'),
                'names' => array('en' => 'Ecclesiastes', 'pt-br' => 'Eclesiastes', 'es' => 'Eclesiastés'),
                'abbrev' => array('en' => 'Ecc', 'pt-br' => 'Ec', 'es' => 'Ecl'),
            ),
            22 => array(
                'id' => 22,
                'chapters' => 8,
                'testament' => 'old',
                'slugs' => array('en' => 'song-of-solomon', 'pt-br' => 'canticos', 'es' => 'cantares'),
                'names' => array('en' => 'Song of Solomon', 'pt-br' => 'Cânticos', 'es' => 'Cantares'),
                'abbrev' => array('en' => 'Sng', 'pt-br' => 'Ct', 'es' => 'Cnt'),
            ),
            23 => array(
                'id' => 23,
                'chapters' => 66,
                'testament' => 'old',
                'slugs' => array('en' => 'isaiah', 'pt-br' => 'isaias', 'es' => 'isaias'),
                'names' => array('en' => 'Isaiah', 'pt-br' => 'Isaías', 'es' => 'Isaías'),
                'abbrev' => array('en' => 'Isa', 'pt-br' => 'Is', 'es' => 'Is'),
            ),
            24 => array(
                'id' => 24,
                'chapters' => 52,
                'testament' => 'old',
                'slugs' => array('en' => 'jeremiah', 'pt-br' => 'jeremias', 'es' => 'jeremias'),
                'names' => array('en' => 'Jeremiah', 'pt-br' => 'Jeremias', 'es' => 'Jeremías'),
                'abbrev' => array('en' => 'Jer', 'pt-br' => 'Jr', 'es' => 'Jer'),
            ),
            25 => array(
                'id' => 25,
                'chapters' => 5,
                'testament' => 'old',
                'slugs' => array('en' => 'lamentations', 'pt-br' => 'lamentacoes', 'es' => 'lamentaciones'),
                'names' => array('en' => 'Lamentations', 'pt-br' => 'Lamentações', 'es' => 'Lamentaciones'),
                'abbrev' => array('en' => 'Lam', 'pt-br' => 'Lm', 'es' => 'Lam'),
            ),
            26 => array(
                'id' => 26,
                'chapters' => 48,
                'testament' => 'old',
                'slugs' => array('en' => 'ezekiel', 'pt-br' => 'ezequiel', 'es' => 'ezequiel'),
                'names' => array('en' => 'Ezekiel', 'pt-br' => 'Ezequiel', 'es' => 'Ezequiel'),
                'abbrev' => array('en' => 'Eze', 'pt-br' => 'Ez', 'es' => 'Ez'),
            ),
            27 => array(
                'id' => 27,
                'chapters' => 12,
                'testament' => 'old',
                'slugs' => array('en' => 'daniel', 'pt-br' => 'daniel', 'es' => 'daniel'),
                'names' => array('en' => 'Daniel', 'pt-br' => 'Daniel', 'es' => 'Daniel'),
                'abbrev' => array('en' => 'Dan', 'pt-br' => 'Dn', 'es' => 'Dn'),
            ),
            28 => array(
                'id' => 28,
                'chapters' => 14,
                'testament' => 'old',
                'slugs' => array('en' => 'hosea', 'pt-br' => 'oseias', 'es' => 'oseas'),
                'names' => array('en' => 'Hosea', 'pt-br' => 'Oseias', 'es' => 'Oseas'),
                'abbrev' => array('en' => 'Hos', 'pt-br' => 'Os', 'es' => 'Os'),
            ),
            29 => array(
                'id' => 29,
                'chapters' => 3,
                'testament' => 'old',
                'slugs' => array('en' => 'joel', 'pt-br' => 'joel', 'es' => 'joel'),
                'names' => array('en' => 'Joel', 'pt-br' => 'Joel', 'es' => 'Joel'),
                'abbrev' => array('en' => 'Joe', 'pt-br' => 'Jl', 'es' => 'Jl'),
            ),
            30 => array(
                'id' => 30,
                'chapters' => 9,
                'testament' => 'old',
                'slugs' => array('en' => 'amos', 'pt-br' => 'amos', 'es' => 'amos'),
                'names' => array('en' => 'Amos', 'pt-br' => 'Amós', 'es' => 'Amós'),
                'abbrev' => array('en' => 'Amo', 'pt-br' => 'Am', 'es' => 'Am'),
            ),
            31 => array(
                'id' => 31,
                'chapters' => 1,
                'testament' => 'old',
                'slugs' => array('en' => 'obadiah', 'pt-br' => 'obadias', 'es' => 'abdias'),
                'names' => array('en' => 'Obadiah', 'pt-br' => 'Obadias', 'es' => 'Abdías'),
                'abbrev' => array('en' => 'Oba', 'pt-br' => 'Ob', 'es' => 'Abd'),
            ),
            32 => array(
                'id' => 32,
                'chapters' => 4,
                'testament' => 'old',
                'slugs' => array('en' => 'jonah', 'pt-br' => 'jonas', 'es' => 'jonas'),
                'names' => array('en' => 'Jonah', 'pt-br' => 'Jonas', 'es' => 'Jonás'),
                'abbrev' => array('en' => 'Jon', 'pt-br' => 'Jn', 'es' => 'Jon'),
            ),
            33 => array(
                'id' => 33,
                'chapters' => 7,
                'testament' => 'old',
                'slugs' => array('en' => 'micah', 'pt-br' => 'miqueias', 'es' => 'miqueas'),
                'names' => array('en' => 'Micah', 'pt-br' => 'Miqueias', 'es' => 'Miqueas'),
                'abbrev' => array('en' => 'Mic', 'pt-br' => 'Mq', 'es' => 'Miq'),
            ),
            34 => array(
                'id' => 34,
                'chapters' => 3,
                'testament' => 'old',
                'slugs' => array('en' => 'nahum', 'pt-br' => 'naum', 'es' => 'nahum'),
                'names' => array('en' => 'Nahum', 'pt-br' => 'Naum', 'es' => 'Nahúm'),
                'abbrev' => array('en' => 'Nah', 'pt-br' => 'Na', 'es' => 'Nah'),
            ),
            35 => array(
                'id' => 35,
                'chapters' => 3,
                'testament' => 'old',
                'slugs' => array('en' => 'habakkuk', 'pt-br' => 'habacuque', 'es' => 'habacuc'),
                'names' => array('en' => 'Habakkuk', 'pt-br' => 'Habacuque', 'es' => 'Habacuc'),
                'abbrev' => array('en' => 'Hab', 'pt-br' => 'Hc', 'es' => 'Hab'),
            ),
            36 => array(
                'id' => 36,
                'chapters' => 3,
                'testament' => 'old',
                'slugs' => array('en' => 'zephaniah', 'pt-br' => 'sofonias', 'es' => 'sofonias'),
                'names' => array('en' => 'Zephaniah', 'pt-br' => 'Sofonias', 'es' => 'Sofonías'),
                'abbrev' => array('en' => 'Zep', 'pt-br' => 'Sf', 'es' => 'Sof'),
            ),
            37 => array(
                'id' => 37,
                'chapters' => 2,
                'testament' => 'old',
                'slugs' => array('en' => 'haggai', 'pt-br' => 'ageu', 'es' => 'hageo'),
                'names' => array('en' => 'Haggai', 'pt-br' => 'Ageu', 'es' => 'Hageo'),
                'abbrev' => array('en' => 'Hag', 'pt-br' => 'Ag', 'es' => 'Hag'),
            ),
            38 => array(
                'id' => 38,
                'chapters' => 14,
                'testament' => 'old',
                'slugs' => array('en' => 'zechariah', 'pt-br' => 'zacarias', 'es' => 'zacarias'),
                'names' => array('en' => 'Zechariah', 'pt-br' => 'Zacarias', 'es' => 'Zacarías'),
                'abbrev' => array('en' => 'Zec', 'pt-br' => 'Zc', 'es' => 'Zac'),
            ),
            39 => array(
                'id' => 39,
                'chapters' => 4,
                'testament' => 'old',
                'slugs' => array('en' => 'malachi', 'pt-br' => 'malaquias', 'es' => 'malaquias'),
                'names' => array('en' => 'Malachi', 'pt-br' => 'Malaquias', 'es' => 'Malaquías'),
                'abbrev' => array('en' => 'Mal', 'pt-br' => 'Ml', 'es' => 'Mal'),
            ),

            // New Testament
            40 => array(
                'id' => 40,
                'chapters' => 28,
                'testament' => 'new',
                'slugs' => array('en' => 'matthew', 'pt-br' => 'mateus', 'es' => 'mateo'),
                'names' => array('en' => 'Matthew', 'pt-br' => 'Mateus', 'es' => 'Mateo'),
                'abbrev' => array('en' => 'Mat', 'pt-br' => 'Mt', 'es' => 'Mt'),
            ),
            41 => array(
                'id' => 41,
                'chapters' => 16,
                'testament' => 'new',
                'slugs' => array('en' => 'mark', 'pt-br' => 'marcos', 'es' => 'marcos'),
                'names' => array('en' => 'Mark', 'pt-br' => 'Marcos', 'es' => 'Marcos'),
                'abbrev' => array('en' => 'Mar', 'pt-br' => 'Mc', 'es' => 'Mr'),
            ),
            42 => array(
                'id' => 42,
                'chapters' => 24,
                'testament' => 'new',
                'slugs' => array('en' => 'luke', 'pt-br' => 'lucas', 'es' => 'lucas'),
                'names' => array('en' => 'Luke', 'pt-br' => 'Lucas', 'es' => 'Lucas'),
                'abbrev' => array('en' => 'Luk', 'pt-br' => 'Lc', 'es' => 'Lc'),
            ),
            43 => array(
                'id' => 43,
                'chapters' => 21,
                'testament' => 'new',
                'slugs' => array('en' => 'john', 'pt-br' => 'joao', 'es' => 'juan'),
                'names' => array('en' => 'John', 'pt-br' => 'João', 'es' => 'Juan'),
                'abbrev' => array('en' => 'Joh', 'pt-br' => 'Jo', 'es' => 'Jn'),
            ),
            44 => array(
                'id' => 44,
                'chapters' => 28,
                'testament' => 'new',
                'slugs' => array('en' => 'acts', 'pt-br' => 'atos', 'es' => 'hechos'),
                'names' => array('en' => 'Acts', 'pt-br' => 'Atos', 'es' => 'Hechos'),
                'abbrev' => array('en' => 'Act', 'pt-br' => 'At', 'es' => 'Hch'),
            ),
            45 => array(
                'id' => 45,
                'chapters' => 16,
                'testament' => 'new',
                'slugs' => array('en' => 'romans', 'pt-br' => 'romanos', 'es' => 'romanos'),
                'names' => array('en' => 'Romans', 'pt-br' => 'Romanos', 'es' => 'Romanos'),
                'abbrev' => array('en' => 'Rom', 'pt-br' => 'Rm', 'es' => 'Ro'),
            ),
            46 => array(
                'id' => 46,
                'chapters' => 16,
                'testament' => 'new',
                'slugs' => array('en' => '1-corinthians', 'pt-br' => '1-corintios', 'es' => '1-corintios'),
                'names' => array('en' => '1 Corinthians', 'pt-br' => '1 Coríntios', 'es' => '1 Corintios'),
                'abbrev' => array('en' => '1Co', 'pt-br' => '1Co', 'es' => '1Co'),
            ),
            47 => array(
                'id' => 47,
                'chapters' => 13,
                'testament' => 'new',
                'slugs' => array('en' => '2-corinthians', 'pt-br' => '2-corintios', 'es' => '2-corintios'),
                'names' => array('en' => '2 Corinthians', 'pt-br' => '2 Coríntios', 'es' => '2 Corintios'),
                'abbrev' => array('en' => '2Co', 'pt-br' => '2Co', 'es' => '2Co'),
            ),
            48 => array(
                'id' => 48,
                'chapters' => 6,
                'testament' => 'new',
                'slugs' => array('en' => 'galatians', 'pt-br' => 'galatas', 'es' => 'galatas'),
                'names' => array('en' => 'Galatians', 'pt-br' => 'Gálatas', 'es' => 'Gálatas'),
                'abbrev' => array('en' => 'Gal', 'pt-br' => 'Gl', 'es' => 'Gá'),
            ),
            49 => array(
                'id' => 49,
                'chapters' => 6,
                'testament' => 'new',
                'slugs' => array('en' => 'ephesians', 'pt-br' => 'efesios', 'es' => 'efesios'),
                'names' => array('en' => 'Ephesians', 'pt-br' => 'Efésios', 'es' => 'Efesios'),
                'abbrev' => array('en' => 'Eph', 'pt-br' => 'Ef', 'es' => 'Ef'),
            ),
            50 => array(
                'id' => 50,
                'chapters' => 4,
                'testament' => 'new',
                'slugs' => array('en' => 'philippians', 'pt-br' => 'filipenses', 'es' => 'filipenses'),
                'names' => array('en' => 'Philippians', 'pt-br' => 'Filipenses', 'es' => 'Filipenses'),
                'abbrev' => array('en' => 'Php', 'pt-br' => 'Fp', 'es' => 'Fil'),
            ),
            51 => array(
                'id' => 51,
                'chapters' => 4,
                'testament' => 'new',
                'slugs' => array('en' => 'colossians', 'pt-br' => 'colossenses', 'es' => 'colosenses'),
                'names' => array('en' => 'Colossians', 'pt-br' => 'Colossenses', 'es' => 'Colosenses'),
                'abbrev' => array('en' => 'Col', 'pt-br' => 'Cl', 'es' => 'Col'),
            ),
            52 => array(
                'id' => 52,
                'chapters' => 5,
                'testament' => 'new',
                'slugs' => array('en' => '1-thessalonians', 'pt-br' => '1-tessalonicenses', 'es' => '1-tesalonicenses'),
                'names' => array('en' => '1 Thessalonians', 'pt-br' => '1 Tessalonicenses', 'es' => '1 Tesalonicenses'),
                'abbrev' => array('en' => '1Th', 'pt-br' => '1Ts', 'es' => '1Ts'),
            ),
            53 => array(
                'id' => 53,
                'chapters' => 3,
                'testament' => 'new',
                'slugs' => array('en' => '2-thessalonians', 'pt-br' => '2-tessalonicenses', 'es' => '2-tesalonicenses'),
                'names' => array('en' => '2 Thessalonians', 'pt-br' => '2 Tessalonicenses', 'es' => '2 Tesalonicenses'),
                'abbrev' => array('en' => '2Th', 'pt-br' => '2Ts', 'es' => '2Ts'),
            ),
            54 => array(
                'id' => 54,
                'chapters' => 6,
                'testament' => 'new',
                'slugs' => array('en' => '1-timothy', 'pt-br' => '1-timoteo', 'es' => '1-timoteo'),
                'names' => array('en' => '1 Timothy', 'pt-br' => '1 Timóteo', 'es' => '1 Timoteo'),
                'abbrev' => array('en' => '1Ti', 'pt-br' => '1Tm', 'es' => '1Ti'),
            ),
            55 => array(
                'id' => 55,
                'chapters' => 4,
                'testament' => 'new',
                'slugs' => array('en' => '2-timothy', 'pt-br' => '2-timoteo', 'es' => '2-timoteo'),
                'names' => array('en' => '2 Timothy', 'pt-br' => '2 Timóteo', 'es' => '2 Timoteo'),
                'abbrev' => array('en' => '2Ti', 'pt-br' => '2Tm', 'es' => '2Ti'),
            ),
            56 => array(
                'id' => 56,
                'chapters' => 3,
                'testament' => 'new',
                'slugs' => array('en' => 'titus', 'pt-br' => 'tito', 'es' => 'tito'),
                'names' => array('en' => 'Titus', 'pt-br' => 'Tito', 'es' => 'Tito'),
                'abbrev' => array('en' => 'Tit', 'pt-br' => 'Tt', 'es' => 'Tit'),
            ),
            57 => array(
                'id' => 57,
                'chapters' => 1,
                'testament' => 'new',
                'slugs' => array('en' => 'philemon', 'pt-br' => 'filemom', 'es' => 'filemon'),
                'names' => array('en' => 'Philemon', 'pt-br' => 'Filemom', 'es' => 'Filemón'),
                'abbrev' => array('en' => 'Phm', 'pt-br' => 'Fm', 'es' => 'Flm'),
            ),
            58 => array(
                'id' => 58,
                'chapters' => 13,
                'testament' => 'new',
                'slugs' => array('en' => 'hebrews', 'pt-br' => 'hebreus', 'es' => 'hebreos'),
                'names' => array('en' => 'Hebrews', 'pt-br' => 'Hebreus', 'es' => 'Hebreos'),
                'abbrev' => array('en' => 'Heb', 'pt-br' => 'Hb', 'es' => 'He'),
            ),
            59 => array(
                'id' => 59,
                'chapters' => 5,
                'testament' => 'new',
                'slugs' => array('en' => 'james', 'pt-br' => 'tiago', 'es' => 'santiago'),
                'names' => array('en' => 'James', 'pt-br' => 'Tiago', 'es' => 'Santiago'),
                'abbrev' => array('en' => 'Jam', 'pt-br' => 'Tg', 'es' => 'Stg'),
            ),
            60 => array(
                'id' => 60,
                'chapters' => 5,
                'testament' => 'new',
                'slugs' => array('en' => '1-peter', 'pt-br' => '1-pedro', 'es' => '1-pedro'),
                'names' => array('en' => '1 Peter', 'pt-br' => '1 Pedro', 'es' => '1 Pedro'),
                'abbrev' => array('en' => '1Pe', 'pt-br' => '1Pe', 'es' => '1Pe'),
            ),
            61 => array(
                'id' => 61,
                'chapters' => 3,
                'testament' => 'new',
                'slugs' => array('en' => '2-peter', 'pt-br' => '2-pedro', 'es' => '2-pedro'),
                'names' => array('en' => '2 Peter', 'pt-br' => '2 Pedro', 'es' => '2 Pedro'),
                'abbrev' => array('en' => '2Pe', 'pt-br' => '2Pe', 'es' => '2Pe'),
            ),
            62 => array(
                'id' => 62,
                'chapters' => 5,
                'testament' => 'new',
                'slugs' => array('en' => '1-john', 'pt-br' => '1-joao', 'es' => '1-juan'),
                'names' => array('en' => '1 John', 'pt-br' => '1 João', 'es' => '1 Juan'),
                'abbrev' => array('en' => '1Jo', 'pt-br' => '1Jo', 'es' => '1Jn'),
            ),
            63 => array(
                'id' => 63,
                'chapters' => 1,
                'testament' => 'new',
                'slugs' => array('en' => '2-john', 'pt-br' => '2-joao', 'es' => '2-juan'),
                'names' => array('en' => '2 John', 'pt-br' => '2 João', 'es' => '2 Juan'),
                'abbrev' => array('en' => '2Jo', 'pt-br' => '2Jo', 'es' => '2Jn'),
            ),
            64 => array(
                'id' => 64,
                'chapters' => 1,
                'testament' => 'new',
                'slugs' => array('en' => '3-john', 'pt-br' => '3-joao', 'es' => '3-juan'),
                'names' => array('en' => '3 John', 'pt-br' => '3 João', 'es' => '3 Juan'),
                'abbrev' => array('en' => '3Jo', 'pt-br' => '3Jo', 'es' => '3Jn'),
            ),
            65 => array(
                'id' => 65,
                'chapters' => 1,
                'testament' => 'new',
                'slugs' => array('en' => 'jude', 'pt-br' => 'judas', 'es' => 'judas'),
                'names' => array('en' => 'Jude', 'pt-br' => 'Judas', 'es' => 'Judas'),
                'abbrev' => array('en' => 'Jud', 'pt-br' => 'Jd', 'es' => 'Jud'),
            ),
            66 => array(
                'id' => 66,
                'chapters' => 22,
                'testament' => 'new',
                'slugs' => array('en' => 'revelation', 'pt-br' => 'apocalipse', 'es' => 'apocalipsis'),
                'names' => array('en' => 'Revelation', 'pt-br' => 'Apocalipse', 'es' => 'Apocalipsis'),
                'abbrev' => array('en' => 'Rev', 'pt-br' => 'Ap', 'es' => 'Ap'),
            ),
        );
    }

    /**
     * Build lookup table for fast name/abbreviation to book mapping
     * Returns array of pattern => book_id
     */
    public static function get_lookup_table($locale = null)
    {
        $books = self::get_books();
        $lookup = array();
        $locales_to_check = $locale ? array(self::normalize_locale($locale)) : self::LOCALES;

        foreach ($books as $book) {
            foreach ($locales_to_check as $loc) {
                // Add name (lowercase)
                if (isset($book['names'][$loc])) {
                    $lookup[mb_strtolower($book['names'][$loc])] = $book['id'];
                }
                // Add abbreviation (lowercase)
                if (isset($book['abbrev'][$loc])) {
                    $lookup[mb_strtolower($book['abbrev'][$loc])] = $book['id'];
                }
                // Add slug
                if (isset($book['slugs'][$loc])) {
                    $lookup[$book['slugs'][$loc]] = $book['id'];
                }
            }
        }

        return $lookup;
    }
}
