<?php
/**
 * Classe stub Translations pour la compatibilité WP-CLI i18n.
 *
 * @package i18n-404-tools
 * @author  L'équipe i18n-404-tools
 * SPDX-FileCopyrightText: 2026 L'équipe i18n-404-tools
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace Gettext;

use IteratorAggregate;

/**
 * Représente une collection de traductions.
 */
class Translations implements IteratorAggregate {
	const HEADER_LANGUAGE = 'Language';

	/**
	 * Tableau des entrées de traduction.
	 *
	 * @var array
	 */
	private $entries = array();

	/**
	 * Tableau des en-têtes PO.
	 *
	 * @var array
	 */
	private $headers = array();

	/**
	 * Domaine de la collection.
	 *
	 * @var string|null
	 */
	private $domain;

	/**
	 * Langue de la collection.
	 *
	 * @var string|null
	 */
	private $language;

	/**
	 * Crée une collection à partir d'un fichier PO (stub).
	 * Cette méthode retourne une collection vide (non implémentée).
	 *
	 * @param string $file Chemin du fichier PO.
	 * @return self Nouvelle instance vide.
	 */
	public static function from_po_file( $file ) {
		// Minimal parser: non implémenté, retourne une collection vide.
		return new self();
	}

	/**
	 * Insère une traduction dans la collection.
	 *
	 * @param string|null $context Contexte de la chaîne.
	 * @param string      $original Chaîne originale.
	 * @param string|null $plural Chaîne plurielle.
	 * @return Translation Instance de Translation.
	 */
	public function insert( $context, $original, $plural = null ) {
		// Remplacement du ternaire court par une expression complète.
		$id = $context;
		if ( empty( $id ) ) {
			$id = '';
		}
		$id .= "\004" . $original;
		if ( ! isset( $this->entries[ $id ] ) ) {
			$this->entries[ $id ] = new Translation( $context, $original, $plural );
		}
		return $this->entries[ $id ];
	}

	/**
	 * Trouve une traduction dans la collection.
	 *
	 * @param Translation $translation Instance à rechercher.
	 * @return Translation|null Traduction trouvée ou null.
	 */
	public function find( Translation $translation ) {
		$id = $translation->get_id();
		return isset( $this->entries[ $id ] ) ? $this->entries[ $id ] : null;
	}

	/**
	 * Fusionne la collection avec une autre.
	 *
	 * @param Translations $other Collection à fusionner.
	 * @param int          $flags Options de fusion.
	 * @return $this
	 */
	public function merge_with( Translations $other, $flags = 0 ) {
		foreach ( $other as $translation ) {
			$existing = $this->find( $translation );
			if ( ! $existing ) {
				$this->entries[ $translation->get_id() ] = $translation;
			}
		}
		return $this;
	}

	/**
	 * Définit un en-tête dans la collection.
	 *
	 * @param string $name Nom de l'en-tête.
	 * @param string $value Valeur de l'en-tête.
	 */
	/**
	 * Définit un en-tête dans la collection.
	 *
	 * @param string $name Nom de l'en-tête.
	 * @param string $value Valeur de l'en-tête.
	 */
	public function set_header( $name, $value ) {
		$this->headers[ $name ] = $value;
		if ( self::HEADER_LANGUAGE === $name ) {
			$this->language = $value;
		}
	}

	/**
	 * Retourne la valeur d'un en-tête.
	 *
	 * @param string $name Nom de l'en-tête.
	 * @return string|null Valeur de l'en-tête ou null.
	 */
	/**
	 * Retourne la valeur d'un en-tête.
	 *
	 * @param string $name Nom de l'en-tête.
	 * @return string|null Valeur de l'en-tête ou null.
	 */
	public function get_header( $name ) {
		return isset( $this->headers[ $name ] ) ? $this->headers[ $name ] : null;
	}

	/**
	 * Supprime un en-tête de la collection.
	 *
	 * @param string $name Nom de l'en-tête à supprimer.
	 */
	/**
	 * Supprime un en-tête de la collection.
	 *
	 * @param string $name Nom de l'en-tête à supprimer.
	 */
	public function delete_header( $name ) {
		unset( $this->headers[ $name ] );
	}

	/**
	 * Retourne tous les en-têtes de la collection.
	 *
	 * @return array Tableau des en-têtes.
	 */
	/**
	 * Retourne tous les en-têtes de la collection.
	 *
	 * @return array Tableau des en-têtes.
	 */
	public function get_headers() {
		return $this->headers;
	}

	/**
	 * Définit le domaine de la collection.
	 *
	 * @param string $domain Domaine à définir.
	 */
	/**
	 * Définit le domaine de la collection.
	 *
	 * @param string $domain Domaine à définir.
	 */
	public function set_domain( $domain ) {
		$this->domain = $domain;
	}

	/**
	 * Retourne le domaine de la collection.
	 *
	 * @return string|null Domaine ou null.
	 */
	/**
	 * Retourne le domaine de la collection.
	 *
	 * @return string|null Domaine ou null.
	 */
	public function get_domain() {
		return $this->domain;
	}

	/**
	 * Définit la langue de la collection.
	 *
	 * @param string $language Langue à définir.
	 */
	/**
	 * Définit la langue de la collection.
	 *
	 * @param string $language Langue à définir.
	 */
	public function set_language( $language ) {
		$this->language = $language;
	}

	/**
	 * Retourne la langue de la collection.
	 *
	 * @return string|null Langue ou null.
	 */
	/**
	 * Retourne la langue de la collection.
	 *
	 * @return string|null Langue ou null.
	 */
	public function get_language() {
		if ( ! empty( $this->language ) ) {
			return $this->language;
		}
		return $this->get_header( self::HEADER_LANGUAGE );
	}

	/**
	 * Retourne le nombre de formes plurielles.
	 *
	 * @return array Tableau contenant le nombre de formes plurielles.
	 */
	/**
	 * Retourne le nombre de formes plurielles.
	 *
	 * @return array Tableau contenant le nombre de formes plurielles.
	 */
	public function get_plural_forms() {
		$header = $this->get_header( 'Plural-Forms' );
		if ( $header && preg_match( '/nplurals\s*=\s*(\d+)/i', $header, $m ) ) {
			return array( (int) $m[1] );
		}
		return array( 2 ); // valeur par défaut.
	}

	/**
	 * Écrit la collection dans un fichier PO (stub).
	 *
	 * @param string $file Chemin du fichier PO.
	 * @return bool Vrai si l'écriture a réussi.
	 */
	/**
	 * Écrit la collection dans un fichier PO (stub).
	 *
	 * @param string $file Chemin du fichier PO.
	 * @return bool Vrai si l'écriture a réussi.
	 */
	public function to_po_file( $file ) {
		return (bool) file_put_contents( $file, '' );
	}

	/**
	 * Écrit la collection dans un fichier MO (stub).
	 *
	 * @param string $file Chemin du fichier MO.
	 * @return bool Vrai si l'écriture a réussi.
	 */
	/**
	 * Écrit la collection dans un fichier MO (stub).
	 *
	 * @param string $file Chemin du fichier MO.
	 * @return bool Vrai si l'écriture a réussi.
	 */
	public function to_mo_file( $file ) {
		return (bool) file_put_contents( $file, '' );
	}

	/**
	 * Retourne un itérateur sur les traductions de la collection.
	 *
	 * @return \Traversable Itérateur sur les traductions.
	 */
	/**
	 * Retourne un itérateur sur les traductions de la collection.
	 *
	 * @return \Traversable Itérateur sur les traductions.
	 */
	/**
	 * Retourne un itérateur sur les traductions de la collection.
	 *
	 * @return \Traversable Itérateur sur les traductions.
	 */
	public function get_iterator(): \Traversable {
		return new \ArrayIterator( array_values( $this->entries ) );
	}
}
