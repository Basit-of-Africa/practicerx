<?php
namespace PracticeRx\Services\EmailTemplates;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Engine {

    /**
     * Render a template string with context. If Twig is available, use it.
     * Otherwise perform simple {{key}} replacements.
     *
     * @param string $template
     * @param array  $context
     * @return string
     */
    public static function render( $template, $context = array() ) {
        // Use Twig if installed
        if ( class_exists( '\\Twig\\Environment' ) ) {
            static $twig = null;
            if ( null === $twig ) {
                $loader = new \Twig\Loader\ArrayLoader( array( 'tpl' => $template ) );
                $twig = new \Twig\Environment( $loader );
            } else {
                $twig->getLoader()->setTemplate( 'tpl', $template );
            }
            try {
                return $twig->render( 'tpl', $context );
            } catch ( \Exception $e ) {
                // fallback
            }
        }

        // Simple replacement: {{key}} will be replaced by context['key']
        $replacements = array();
        foreach ( $context as $k => $v ) {
            $replacements['{{' . $k . '}}'] = is_scalar( $v ) ? $v : wp_json_encode( $v );
        }

        return strtr( $template, $replacements );
    }
}
