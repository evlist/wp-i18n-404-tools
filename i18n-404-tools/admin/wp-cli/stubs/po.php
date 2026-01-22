<?php
/**
 * Classe stub Po pour la compatibilité WP-CLI i18n.
 *
 * @package i18n-404-tools
 * @author  L'équipe i18n-404-tools
 * SPDX-FileCopyrightText: 2026 L'équipe i18n-404-tools
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace Gettext\Generators;

/**
 * Générateur minimal de fichiers PO.
 * Fournit des méthodes pour convertir et écrire des traductions au format PO.
 */
class Po {
	/**
	 * Échappe une chaîne pour le format PO.
	 *
	 * @param string $string Chaîne à échapper.
	 * @return string Chaîne échappée.
	 */
	protected static function escape( $string ) {
		return addcslashes( $string, "\0\n\r\t\\\"" );
	}

	/**
	 * Convertit une chaîne pour le format PO.
	 *
	 * @param string $string Chaîne à convertir.
	 * @return string Chaîne convertie.
	 */
	public static function convert_string( $string ) {
		return '"' . self::escape( $string ) . '"';
	}

	/**
	 * Écrit la collection de traductions dans un fichier PO (stub).
	 *
	 * @param \Gettext\Translations $translations Collection de traductions.
	 * @param string                $file Chemin du fichier PO.
	 * @param array                 $options Options d'écriture.
	 * @return bool Vrai si l'écriture a réussi.
	 */
	public static function to_file( \Gettext\Translations $translations, $file, array $options = array() ) {
		if ( ! method_exists( $translations, 'get_headers' ) ) {
			return false;
		}
		$content = static::to_string( $translations, $options );
		return false !== file_put_contents( $file, $content );
	}

	/**
	 * Convertit la collection de traductions en chaîne PO (stub).
	 *
	 * @param \Gettext\Translations $translations Collection de traductions.
	 * @param array                 $options Options de conversion.
	 * @return string Chaîne PO générée.
	 */
	public static function to_string( \Gettext\Translations $translations, array $options = array() ) {
		// Placeholder minimal.
		return '';
	}
}
