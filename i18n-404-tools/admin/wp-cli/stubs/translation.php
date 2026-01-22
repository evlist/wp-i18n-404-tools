<?php
/**
 * Classe stub Translation pour la compatibilité WP-CLI i18n.
 *
 * @package i18n-404-tools
 * @author  L'équipe i18n-404-tools
 * SPDX-FileCopyrightText: 2026 L'équipe i18n-404-tools
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace Gettext;

/**
 * Représente une traduction unique (singulier/pluriel, contexte, etc.).
 */
class Translation {
	/**
	 * Contexte de la chaîne (ex : domaine ou contexte PO).
	 *
	 * @var string|null
	 */
	private $context;

	/**
	 * Chaîne originale à traduire.
	 *
	 * @var string
	 */
	private $original;

	/**
	 * Chaîne plurielle (si applicable).
	 *
	 * @var string|null
	 */
	private $plural;

	/**
	 * Traduction principale de la chaîne.
	 *
	 * @var string
	 */
	private $translation = '';

	/**
	 * Tableau des traductions plurielles.
	 *
	 * @var array
	 */
	private $plural_translations = array();

	/**
	 * Tableau des commentaires associés à la chaîne.
	 *
	 * @var array
	 */
	private $comments = array();

	/**
	 * Tableau des commentaires extraits (PO #.).
	 *
	 * @var array
	 */
	private $extracted_comments = array();

	/**
	 * Tableau des références de fichiers (PO #:).
	 *
	 * @var array
	 */
	private $references = array();

	/**
	 * Tableau des drapeaux (flags PO).
	 *
	 * @var array
	 */
	private $flags = array();

	/**
	 * Indique si la traduction est désactivée.
	 *
	 * @var bool
	 */
	private $disabled = false;

	/**
	 * Constructeur de la classe Translation.
	 * Initialise le contexte, l'original et le pluriel.
	 *
	 * @param string|null $context Contexte de la chaîne.
	 * @param string      $original Chaîne originale.
	 * @param string|null $plural Chaîne plurielle.
	 */
	public function __construct( $context = null, $original = '', $plural = null ) {
		$this->context = $context;
		$this->original = $original;
		$this->plural = $plural;
	}

	/**
	 * Ajoute une référence de fichier à la traduction.
	 *
	 * @param string   $file Chemin du fichier.
	 * @param int|null $line Ligne dans le fichier.
	 * @return $this
	 */
	public function add_reference( $file, $line = null ) {
		$this->references[] = array( $file, $line );
		return $this;
	}

	/**
	 * Ajoute un commentaire extrait à la traduction.
	 *
	 * @param string $comment Commentaire extrait.
	 * @return $this
	 */
	public function add_extracted_comment( $comment ) {
		$this->extracted_comments[] = $comment;
		return $this;
	}

	/**
	 * Ajoute un drapeau (flag) à la traduction.
	 *
	 * @param string $flag Drapeau à ajouter.
	 * @return $this
	 */
	public function add_flag( $flag ) {
		$this->flags[] = $flag;
		return $this;
	}

	/**
	 * Vérifie si des traductions plurielles existent.
	 *
	 * @param bool $strict Mode strict (non utilisé ici).
	 * @return bool Vrai si des traductions plurielles existent.
	 */
	public function has_plural_translations( $strict = false ) {
		return ! empty( $this->plural_translations );
	}

	/**
	 * Retourne les traductions plurielles.
	 *
	 * @param int|null $count Nombre de traductions (non utilisé ici).
	 * @return array Tableau des traductions plurielles.
	 */
	public function get_plural_translations( $count = null ) {
		return $this->plural_translations;
	}

	/**
	 * Définit les traductions plurielles.
	 *
	 * @param array $translations Tableau des traductions plurielles.
	 * @return $this
	 */
	public function set_plural_translations( array $translations ) {
		$this->plural_translations = $translations;
		return $this;
	}

	/**
	 * Définit la traduction principale.
	 *
	 * @param string $value Traduction principale.
	 * @return $this
	 */
	public function set_translation( $value ) {
		$this->translation = $value;
		return $this;
	}

	/**
	 * Retourne la traduction principale.
	 *
	 * @return string Traduction principale.
	 */
	public function get_translation() {
		return $this->translation;
	}

	/**
	 * Vérifie si une forme plurielle existe.
	 *
	 * @return bool Vrai si une forme plurielle existe.
	 */
	public function has_plural() {
		return null !== $this->plural;
	}

	/**
	 * Retourne la forme plurielle.
	 *
	 * @return string|null Forme plurielle.
	 */
	public function get_plural() {
		return $this->plural;
	}

	/**
	 * Retourne la chaîne originale.
	 *
	 * @return string Chaîne originale.
	 */
	public function get_original() {
		return $this->original;
	}

	/**
	 * Retourne le contexte de la traduction.
	 *
	 * @return string|null Contexte.
	 */
	public function get_context() {
		return $this->context;
	}

	/**
	 * Vérifie si un contexte existe.
	 *
	 * @return bool Vrai si un contexte existe.
	 */
	public function has_context() {
		return null !== $this->context && '' !== $this->context;
	}

	/**
	 * Vérifie si des commentaires existent.
	 *
	 * @return bool Vrai si des commentaires existent.
	 */
	public function has_comments() {
		return ! empty( $this->comments );
	}

	/**
	 * Retourne les commentaires associés à la traduction.
	 *
	 * @return array Tableau des commentaires.
	 */
	public function get_comments() {
		return $this->comments;
	}

	/**
	 * Ajoute un commentaire à la traduction.
	 *
	 * @param string $comment Commentaire à ajouter.
	 * @return $this
	 */
	public function add_comment( $comment ) {
		$this->comments[] = $comment;
		return $this;
	}

	/**
	 * Vérifie si des commentaires extraits existent.
	 *
	 * @return bool Vrai si des commentaires extraits existent.
	 */
	public function has_extracted_comments() {
		return ! empty( $this->extracted_comments );
	}

	/**
	 * Retourne les commentaires extraits associés à la traduction.
	 *
	 * @return array Tableau des commentaires extraits.
	 */
	public function get_extracted_comments() {
		return $this->extracted_comments;
	}

	/**
	 * Retourne les références de fichiers associées à la traduction.
	 *
	 * @return array Tableau des références de fichiers.
	 */
	public function get_references() {
		return $this->references;
	}

	/**
	 * Vérifie si des drapeaux existent.
	 *
	 * @return bool Vrai si des drapeaux existent.
	 */
	public function has_flags() {
		return ! empty( $this->flags );
	}

	/**
	 * Retourne les drapeaux associés à la traduction.
	 *
	 * @return array Tableau des drapeaux.
	 */
	public function get_flags() {
		return $this->flags;
	}

	/**
	 * Vérifie si la traduction est désactivée.
	 *
	 * @return bool Vrai si la traduction est désactivée.
	 */
	public function is_disabled() {
		return (bool) $this->disabled;
	}

	/**
	 * Définit le statut désactivé de la traduction.
	 *
	 * @param bool $disabled Vrai pour désactiver, faux sinon.
	 * @return $this
	 */
	public function set_disabled( $disabled ) {
		$this->disabled = (bool) $disabled;
		return $this;
	}

	/**
	 * Retourne l'identifiant unique (contexte + original).
	 *
	 * @return string Identifiant unique.
	 */
	public function get_id() {
		// Remplacement du ternaire court par une expression complète.
		$context = $this->context;
		if ( empty( $context ) ) {
			$context = '';
		}
		return $context . "\004" . $this->original;
	}
}
